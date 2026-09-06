<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RulesController extends Controller
{
    public function __invoke(): Response
    {
        return response()->view('legal.rules', [
            'title' => 'Reglas del Ranking | Quién Manda',
            'metaDescription' => 'Conoce cómo funciona el ranking de Quién Manda, cómo se calculan las posiciones, qué pasa al pagar y cómo se define quién manda al cierre de cada período.',
        ])->header('Cache-Control', 'public, max-age=3600')
          ->header('X-Robots-Tag', 'index, follow');
    }
}
