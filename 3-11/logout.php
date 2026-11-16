<?php
declare(strict_types=1);
require_once __DIR__ . '/sesion.php';

iniciarSesion();

$_SESSION = [];

// Borra también la cookie de sesión del navegador
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $p['samesite'],
    ]);
}
session_destroy();

header('Location: index.php?salida=1');
exit;
