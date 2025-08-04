<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebContactController extends Controller
{
    public function handleContactForm(Request $request)
    {
        $response = Http::withToken(config('services.internal_api.token'))
            ->acceptJson()
            ->post(url('/api/contact'), $request->all());

        return $response->toPsrResponse();
    }
}
