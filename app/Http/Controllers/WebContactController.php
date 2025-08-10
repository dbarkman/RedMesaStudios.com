<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormNotification;

class WebContactController extends Controller
{
    public function handleContactForm(Request $request)
    {
        // Validate with same rules as API, but handled locally to avoid HTTP proxy
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email:rfc|max:255',
            'phone' => 'nullable|string|max:255',
            'message' => 'required|string',
        ], [
            'message.required' => 'Message is required.'
        ]);

        $sanitized = [
            'name' => isset($validated['name']) ? strip_tags(trim($validated['name'])) : null,
            'email' => isset($validated['email']) ? strtolower(trim($validated['email'])) : null,
            'phone' => isset($validated['phone']) ? preg_replace('/[^\d+\-\s\(\)]/', '', $validated['phone']) : null,
            'message' => strip_tags(trim($validated['message'])),
        ];

        $contact = Contact::create($sanitized);

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
