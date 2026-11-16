<?php
declare(strict_types=1);
require_once __DIR__ . '/sesion.php';

iniciarSesion();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfil.php');
    exit;
}
verificarCsrf(); // un logout forzado desde otra web también es CSRF

$_SESSION = [];
$p = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires'  => time() - 42000,
    'path'     => $p['path'],
    'domain'   => $p['domain'],
    'secure'   => $p['secure'],
    'httponly' => $p['httponly'],
    'samesite' => $p['samesite'],
]);
session_destroy();

header('Location: index.php');
exit;
