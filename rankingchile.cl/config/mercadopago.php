<?php

// config/mercadopago.php — Única pasarela del MVP (D-30 + D-36 / DECISION_REQUIRED-003)
return [
    'driver' => env('MP_DRIVER', 'mercadopago'), // mercadopago | local
    'access_token' => env('MP_ACCESS_TOKEN', ''),
    'public_key' => env('MP_PUBLIC_KEY', ''),
    'env' => env('MP_ENV', 'sandbox'), // sandbox | production
    'webhook_url' => env('MP_NOTIFICATION_URL', 'https://www.quienmanda.cl/api/webhooks/mercadopago'),
    'webhook_secret' => env('MP_WEBHOOK_SECRET', ''), // BLOCKER-005/TASK-PROD-003: firma x-signature del webhook
    // Topics manejados por el handler (TASK-001 confirma cuál aplica en sandbox):
    //   payment -> data.id = payment id (GET /v1/payments/{id})
    //   merchant_order -> data.id = merchant_order id (GET /merchant_orders/{id}, payment_ids[])
    //   orders -> variante de órdenes del producto MP configurado
    'webhook_topics' => array_filter(array_map('trim', explode(',', env('MP_WEBHOOK_TOPICS', 'payment,merchant_order')))),
    // https://api.mercadopago.com (sandbox y producción comparten base; el access token define el entorno)
    'base_url' => 'https://api.mercadopago.com',
    'local_cache_ttl_hours' => (int) env('MP_LOCAL_CACHE_TTL_HOURS', 24),
];
