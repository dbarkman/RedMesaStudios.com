<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormNotification;

class ContactController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email:rfc|max:255',
            'phone' => 'nullable|string|max:255',
            'message' => 'required|string',
        ], [
            'message.required' => 'Message is required.'
        ]);

        // Lightweight sanitization
        $sanitized = [
            'name' => isset($validated['name']) ? strip_tags(trim($validated['name'])) : null,
            'email' => isset($validated['email']) ? strtolower(trim($validated['email'])) : null,
            'phone' => isset($validated['phone']) ? preg_replace('/[^\d+\-\s\(\)]/', '', $validated['phone']) : null,
            'message' => strip_tags(trim($validated['message'])),
        ];

        $contact = Contact::create($sanitized);

        // Send email notification
        $recipient = config('custom.contact_form_recipient');
        if (!empty($recipient)) {
            Mail::to($recipient)->send(new ContactFormNotification($contact));
        }

        return response()->json([
            'status' => 'queued',
            'message' => 'Thank you for your message! We will be in touch soon.',
            'contact_id' => $contact->id,
        ], 202);
    }
}
