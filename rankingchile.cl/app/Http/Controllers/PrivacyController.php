<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PrivacyController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('legal.privacy', [
            'title' => 'Política de Privacidad — ¿Quién Manda?',
            'contactEmail' => config('legal.contact.email'),
        ])->header('Cache-Control', 'public, max-age=3600')
          ->header('X-Robots-Tag', 'index, follow');
    }
}
