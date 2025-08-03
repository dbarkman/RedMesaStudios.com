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
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $contact = Contact::create($validatedData);

        // Send email notification
        $recipient = config('custom.contact_form_recipient');
        if ($recipient) {
            Mail::to($recipient)->send(new ContactFormNotification($contact));
        }

        return response()->json([
            'message' => 'Your message has been received and is being processed.'
        ], 202);
    }
}
