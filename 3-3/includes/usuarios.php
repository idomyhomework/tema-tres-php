<?php
// Usuarios del sistema. Contraseñas hasheadas previamente con password_hash().
// Comillas SIMPLES: con dobles, PHP intentaría interpretar "$2y" como variable.
//
// Usuarios de prueba:  admin / admin123   ·   usuario / usuario123

return [
    'admin' => [
        'hash' => '$2y$10$nhzK5HQ8lBwVxgh08yugmOSSkG0TFNGSEI.BHt./KHKgsx2rF8oX.',
        'rol'  => 'admin',
    ],
    'usuario' => [
        'hash' => '$2y$10$SzdDljnvafiVgAFYlP.9yO8xC2Wq7LgRtVN/g66nO4FDIvcjagtEW',
        'rol'  => 'usuario',
    ],
];
