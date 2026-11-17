<?php

// Mensajes de validación en español (solo las reglas que usa la API)
return [
    'required' => ':Attribute es obligatorio.',
    'string' => ':Attribute debe ser texto.',
    'email' => ':Attribute debe ser un correo electrónico válido.',
    'unique' => ':Attribute ya está registrado.',
    'integer' => ':Attribute debe ser un número entero.',
    'numeric' => ':Attribute debe ser un número.',
    'exists' => ':Attribute no existe.',
    'min' => [
        'string' => ':Attribute debe tener al menos :min caracteres.',
        'numeric' => ':Attribute debe ser al menos :min.',
    ],
    'max' => [
        'string' => ':Attribute no debe tener más de :max caracteres.',
        'numeric' => ':Attribute no debe ser mayor que :max.',
    ],
    'size' => [
        'string' => ':Attribute debe tener exactamente :size letras.',
    ],

    // Nombres de los campos como se leen en español
    'attributes' => [
        'name' => 'el nombre',
        'email' => 'el correo',
        'password' => 'la contraseña',
        'nombre' => 'el nombre',
        'telefono' => 'el teléfono',
        'user_id' => 'el usuario',
        'monto' => 'el monto',
        'de' => 'la moneda de origen',
        'a' => 'la moneda de destino',
    ],
];
