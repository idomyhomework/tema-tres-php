<?php
// Usuarios del portal. Las contraseñas se guardan como hash (password_hash),
// nunca en texto plano. Si se abre este archivo en el navegador no muestra nada.
//
// Para añadir un usuario, genera su hash desde la terminal:
//   php -r 'echo password_hash("LaContraseña1", PASSWORD_DEFAULT), "\n";'

return [
    'admin@portal.com' => [
        'nombre' => 'Admin',
        'pass'   => '$2y$10$TbaYp2B/o.Als2liuNJzCOMP/nMcQ.Ky0VhpRZsVMwRLvfw7PjicC',
        'rol'    => 'admin',
    ],
    'usuario@portal.com' => [
        'nombre' => 'Usuario',
        'pass'   => '$2y$10$4WuQVbQwm0JXkdEPzexX8.QjYw3aV6MKEJCmYS4FmLAaNelekoi2O',
        'rol'    => 'usuario',
    ],
];
