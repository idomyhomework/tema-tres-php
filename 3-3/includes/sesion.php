<?php
// Funciones comunes de sesión y control de acceso

const PAGINA_LOGIN = 'login.php';

// Página de destino de cada rol tras iniciar sesión
const DESTINOS = [
    'admin'   => 'admin.php',
    'usuario' => 'usuario.php',
];

// Arranca la sesión con la cookie configurada de forma segura
function iniciarSesion(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,      // dura hasta cerrar el navegador
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,   // JavaScript no puede leer el ID de sesión
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1'); // rechaza IDs de sesión inventados

    session_start();
}

function redirigir(string $url): never {
    header('Location: ' . $url);
    exit;
}

// Devuelve el usuario logueado o null si no hay sesión iniciada
function usuarioActual(): ?array {
    iniciarSesion();

    if (!isset($_SESSION['usuario'], $_SESSION['rol'])) {
        return null;
    }
    return ['nombre' => $_SESSION['usuario'], 'rol' => $_SESSION['rol']];
}

// Protege una página: sin sesión → login; con un rol no permitido → su propia zona
function exigirRol(string ...$rolesPermitidos): array {
    $usuario = usuarioActual();

    if ($usuario === null) {
        redirigir(PAGINA_LOGIN);
    }
    if (!in_array($usuario['rol'], $rolesPermitidos, true)) {
        $_SESSION['aviso'] = 'No tienes permiso para acceder a esa página.';
        redirigir(DESTINOS[$usuario['rol']]);
    }
    return $usuario;
}

// Mensaje "flash": se lee una vez y se borra de la sesión
function tomarAviso(): ?string {
    $aviso = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $aviso;
}
