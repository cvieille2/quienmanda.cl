<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Términos y Condiciones
    |--------------------------------------------------------------------------
    |
    | Versión canónica vigente de los Términos y Condiciones de uso.
    | Se almacena en cada transacción para trazabilidad y auditoría.
    |
    */
    'terms' => [
        'version' => env('LEGAL_TERMS_VERSION', '1.0'),
        'effective_date' => env('LEGAL_TERMS_EFFECTIVE_DATE', '2026-09-01'),
        'url' => '/terminos',
    ],

    /*
    |--------------------------------------------------------------------------
    | Política de Privacidad
    |--------------------------------------------------------------------------
    */
    'privacy' => [
        'url' => '/privacidad',
    ],

    /*
    |--------------------------------------------------------------------------
    | Contacto legal
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'email' => env('LEGAL_CONTACT_EMAIL', 'contacto@quienmanda.cl'),
    ],
];
