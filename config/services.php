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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'almacen' => [
        'api_url' => env('ALMACEN_API_URL', 'http://localhost:8002/api'),
    ],

    'plantacruds' => [
        'api_url' => env('PLANTACRUDS_API_URL', 'http://localhost:8001/api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Base URL para App Móvil
    |--------------------------------------------------------------------------
    |
    | Esta es la URL base que la app móvil usará para conectarse a la API.
    | IMPORTANTE: Debe ser la IP de la red local (ej: http://192.168.0.129:8001)
    | NO usar localhost ya que la app móvil no puede acceder a localhost.
    | 
    | Para encontrar tu IP local, ejecuta: ipconfig (Windows) o ifconfig (Linux/Mac)
    | Busca la IP en la red local (generalmente 192.168.x.x o 10.x.x.x)
    |
    */
    'app_mobile' => [
        'api_base_url' => env('APP_MOBILE_API_URL', env('APP_URL', 'http://localhost') . '/api'),
    ],

];
