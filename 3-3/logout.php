<?php
require_once __DIR__ . '/includes/sesion.php';

// Solo se cierra sesión por POST (el botón). Un enlace o una imagen de otra web
// no puede cerrarte la sesión con una simple petición GET.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(PAGINA_LOGIN);
}

// iniciarSesion();

// 1. Vaciar todas las variables de sesión
$_SESSION = [];

// 2. Borrar la cookie de sesión del navegador (mismas opciones con las que se creó)
$params = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires'  => time() - 42000,
    'path'     => $params['path'],
    'domain'   => $params['domain'],
    'secure'   => $params['secure'],
    'httponly' => $params['httponly'],
    'samesite' => $params['samesite'],
]);

// 3. Destruir los datos de la sesión en el servidor
session_destroy();

redirigir(PAGINA_LOGIN . '?salida=1');
