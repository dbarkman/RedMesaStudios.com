@component('mail::message')
# New Contact Form Submission

A new message has been submitted through the contact form.

**Name:** {{ $name }}
**Email:** {{ $email }}
**Phone:** {{ $phone }}

**Message:**
{{ $message }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
