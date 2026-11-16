<?php
// Usuarios autorizados: array asociativo usuario => datos.
// Contraseñas hasheadas con password_hash(). Comillas SIMPLES por el "$2y".
//
// Usuarios de prueba:  admin / admin123   ·   usuario / usuario123

return [
    'admin' => [
        'hash'   => '$2y$10$nhzK5HQ8lBwVxgh08yugmOSSkG0TFNGSEI.BHt./KHKgsx2rF8oX.',
        'nombre' => 'Administrador',
    ],
    'usuario' => [
        'hash'   => '$2y$10$SzdDljnvafiVgAFYlP.9yO8xC2Wq7LgRtVN/g66nO4FDIvcjagtEW',
        'nombre' => 'Usuario de pruebas',
    ],
];
