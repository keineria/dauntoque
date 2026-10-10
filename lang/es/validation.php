<?php

// Mensajes de validación en español para las reglas que usa el portal (las demás caen al inglés).
// :Attribute (con mayúscula) va al inicio de la frase; los nombres de campo llevan su artículo.
return [
    'accepted' => 'Debes aceptar :attribute.',
    'array' => ':Attribute no es válido.',
    'boolean' => ':Attribute no es válido.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña no es correcta.',
    'email' => 'Escribe un correo electrónico válido.',
    'exists' => ':Attribute no es válido.',
    'image' => ':Attribute debe ser una imagen.',
    'json' => ':Attribute no es válido.',
    'lowercase' => ':Attribute debe estar en minúsculas.',
    'max' => [
        'string' => ':Attribute no puede tener más de :max caracteres.',
        'file' => ':Attribute no puede pesar más de :max kilobytes.',
        'numeric' => ':Attribute no puede ser mayor que :max.',
        'array' => ':Attribute no puede tener más de :max elementos.',
    ],
    'min' => [
        'string' => ':Attribute debe tener al menos :min caracteres.',
        'file' => ':Attribute debe pesar al menos :min kilobytes.',
        'numeric' => ':Attribute debe ser al menos :min.',
        'array' => ':Attribute debe tener al menos :min elementos.',
    ],
    'numeric' => ':Attribute debe ser un número.',
    'password' => [
        'letters' => ':Attribute debe contener al menos una letra.',
        'mixed' => ':Attribute debe contener mayúsculas y minúsculas.',
        'numbers' => ':Attribute debe contener al menos un número.',
        'symbols' => ':Attribute debe contener al menos un símbolo.',
        'uncompromised' => 'Esa contraseña apareció en una filtración de datos. Elige otra.',
    ],
    'required' => 'Falta :attribute.',
    'string' => ':Attribute no es válido.',
    'unique' => 'Ya existe una cuenta con :attribute.',
    'url' => ':Attribute no es un enlace válido.',

    'attributes' => [
        'name' => 'el nombre',
        'email' => 'el correo',
        'password' => 'la contraseña',
        'password_confirmation' => 'la confirmación de la contraseña',
        'current_password' => 'la contraseña actual',
    ],
];
