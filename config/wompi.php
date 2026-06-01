<?php
return [
    'env'              => env('WOMPI_ENV', 'sandbox'),
    'public_key'       => env('WOMPI_PUBLIC_KEY', ''),
    'private_key'      => env('WOMPI_PRIVATE_KEY', ''),
    'integrity_secret' => env('WOMPI_INTEGRITY_SECRET', ''),
    'events_secret'    => env('WOMPI_EVENTS_SECRET', ''),
];
