<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'primetrack' => [
        'base_url' => env('PRIMETRACK_BASE_URL', ''),
        'api_key' => env('PRIMETRACK_API_KEY', ''),
        'status_path' => env('PRIMETRACK_STATUS_PATH', '/api/tracking/{code}'),
        'timeout' => (int) env('PRIMETRACK_TIMEOUT', 15),
        // Estados remotos (minúsculas, sin acentos) => estado local en inventario
        'estado_map' => [
            'pendiente' => 'pendiente',
            'recibido' => 'recibido',
            'en_bodega' => 'recibido',
            'en_transito' => 'en_transito',
            'en transito' => 'en_transito',
            'en_camino' => 'en_transito',
            'en_aduana' => 'en_aduana',
            'listo_para_entrega' => 'listo_entrega',
            'entregado' => 'entregado',
            'devuelto' => 'devuelto',
            'cancelado' => 'cancelado',
        ],
    ],

    /*
    | Provider de tracking externo (swappable).
    | TRACKING_PROVIDER=cargotrack|none
    */
    'tracking' => [
        'provider' => env('TRACKING_PROVIDER', 'cargotrack'),
    ],

    'cargotrack' => [
        'base_url' => env('CARGOTRACK_BASE_URL', 'https://gc.cargotrack.net'),
        'track_path' => env('CARGOTRACK_TRACK_PATH', '/m/track.asp'),
        'timeout' => (int) env('CARGOTRACK_TIMEOUT', 20),
        'user_agent' => env(
            'CARGOTRACK_USER_AGENT',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ),
        'estado_map' => [
            'in transit' => 'en_transito',
            'in_transit' => 'en_transito',
            'shipped' => 'en_transito',
            'received' => 'recibido',
            'warehouse' => 'recibido',
            'in warehouse' => 'recibido',
            'in destination country' => 'en_aduana',
            'in_destination_country' => 'en_aduana',
            'arrived' => 'en_aduana',
            'customs' => 'en_aduana',
            // Listo (listo_entrega) solo se marca al ingresar el paquete en el CRM.
            'ready for pickup' => 'en_aduana',
            'ready for delivery' => 'en_aduana',
            'out for delivery' => 'en_aduana',
            'delivered' => 'entregado',
            'returned' => 'devuelto',
            'cancelled' => 'cancelado',
            'canceled' => 'cancelado',
            'pending' => 'pendiente',
        ],
    ],

];
