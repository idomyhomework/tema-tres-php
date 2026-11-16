<?php
declare(strict_types=1);
require_once __DIR__ . '/sesion.php';

// Hash de una contraseña que nadie conoce: igualamos el tiempo de respuesta
// cuando el usuario no existe (evita enumerar usuarios midiendo tiempos).
const HASH_FALSO = '$2y$10$QNsHvdYYXnzuoY.3VrJnGOvbiIvJRSqntadMI4TDgecgQuC4zjj9K';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

iniciarSesion();

$usuario = trim((string) ($_POST['usuario'] ?? ''));
$pass    = (string) ($_POST['pass'] ?? '');

if ($usuario === '' || $pass === '') {
    header('Location: index.php?error=vacio');
    exit;
}

$usuarios = require __DIR__ . '/usuarios.php';
$datos    = $usuarios[$usuario] ?? null;
$valido   = password_verify($pass, $datos['hash'] ?? HASH_FALSO) && $datos !== null;

registrarAcceso($usuario, $valido);

if (!$valido) {
    header('Location: index.php?error=credenciales');
    exit;
}

session_regenerate_id(true); // evita la fijación de sesión
$_SESSION['usuario'] = $usuario;
$_SESSION['nombre']  = $datos['nombre'];
$_SESSION['desde']   = date('Y-m-d H:i:s');

header('Location: privado/panel.php');
exit;
