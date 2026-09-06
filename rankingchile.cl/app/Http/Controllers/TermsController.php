<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class TermsController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('legal.terms', [
            'title' => 'Términos y Condiciones — ¿Quién Manda?',
            'effectiveDate' => config('legal.terms.effective_date'),
            'termsVersion' => config('legal.terms.version'),
            'contactEmail' => config('legal.contact.email'),
        ])->header('Cache-Control', 'public, max-age=3600')
          ->header('X-Robots-Tag', 'index, follow');
    }
}
