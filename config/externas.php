<?php

// Configuración de las APIs externas que consume AgendaAPI.
// Las claves se leen del archivo .env (nunca se escriben en el código).
return [

    // Open-Meteo: clima, gratuita y sin registro
    'clima' => [
        'url' => env('OPENMETEO_URL', 'https://api.open-meteo.com/v1/forecast'),
    ],

    // ExchangeRate-API: tipo de cambio, gratuita pero pide registrarse y usar una clave
    'tipo_cambio' => [
        'url' => env('EXCHANGERATE_URL', 'https://v6.exchangerate-api.com/v6'),
        'key' => env('EXCHANGERATE_KEY'),
    ],
];
