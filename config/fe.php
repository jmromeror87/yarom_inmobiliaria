<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Driver activo de Facturación Electrónica DIAN
    |--------------------------------------------------------------------------
    | Opciones: factus | dataico | facturatech
    | Cambiar FE_DRIVER en .env para cambiar de operador sin tocar código.
    */
    'driver' => env('FE_DRIVER', 'factus'),

    /*
    |--------------------------------------------------------------------------
    | Ambiente DIAN
    |--------------------------------------------------------------------------
    | habilitacion = pruebas (sandbox DIAN)
    | produccion   = ambiente real
    */
    'ambiente' => env('FE_AMBIENTE', 'habilitacion'),

    /*
    |--------------------------------------------------------------------------
    | Factus (factus.com.co)  —  OAuth2 + REST
    |--------------------------------------------------------------------------
    */
    'factus' => [
        'base_url'       => env('FACTUS_BASE_URL', 'https://api-sandbox.factus.com.co'),
        'client_id'      => env('FACTUS_CLIENT_ID', ''),
        'client_secret'  => env('FACTUS_CLIENT_SECRET', ''),
        'username'       => env('FACTUS_USERNAME', ''),
        'password'       => env('FACTUS_PASSWORD', ''),
        'timeout'        => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dataico (dataico.com)  —  API Key + REST
    |--------------------------------------------------------------------------
    */
    'dataico' => [
        'base_url'   => env('DATAICO_BASE_URL', 'https://app.dataico.com'),
        'api_key'    => env('DATAICO_API_KEY', ''),
        'account_id' => env('DATAICO_ACCOUNT_ID', ''),
        'timeout'    => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Facturatech  —  API Key + REST
    |--------------------------------------------------------------------------
    */
    'facturatech' => [
        'base_url' => env('FACTURATECH_BASE_URL', 'https://api.facturatech.co'),
        'api_key'  => env('FACTURATECH_API_KEY', ''),
        'nit'      => env('FACTURATECH_NIT', ''),
        'timeout'  => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reintentos automáticos en caso de fallo
    |--------------------------------------------------------------------------
    */
    'reintentos' => [
        'max'            => 3,
        'delay_minutos'  => [5, 30, 120], // delay por intento
    ],
];
