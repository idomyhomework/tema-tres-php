<?php
declare(strict_types=1);

// Usuario de prueba: usuario / usuario123 (hash generado con password_hash())
const USUARIOS = [
    'usuario' => [
        'hash'  => '$2y$10$SzdDljnvafiVgAFYlP.9yO8xC2Wq7LgRtVN/g66nO4FDIvcjagtEW',
        'email' => 'usuario@ejemplo.com',
    ],
];

function iniciarSesion(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
}

function exigirSesion(): string
{
    iniciarSesion();
    if (empty($_SESSION['usuario'])) {
        header('Location: index.php');
        exit;
    }
    return $_SESSION['usuario'];
}

/* ===================== CSRF ===================== */

/** Devuelve el token actual o genera uno nuevo con 32 bytes aleatorios (64 hex). */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(tokenCsrf()) . '">';
}

/**
 * Comprueba el token recibido por POST. Si falta o no coincide → 403 y fin.
 * hash_equals() compara en tiempo constante (no filtra información por tiempo).
 * Si es válido, se destruye: es de un solo uso.
 */
function verificarCsrf(): void
{
    $recibido = $_POST['csrf_token'] ?? '';
    $guardado = $_SESSION['csrf_token'] ?? '';

    if (!is_string($recibido) || $guardado === '' || !hash_equals($guardado, $recibido)) {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>403 Forbidden</title></head><body>';
        echo '<h1>403 Forbidden</h1><p>Petición ilegítima: el token CSRF falta o no es válido.</p>';
        echo '<p><a href="perfil.php">Volver</a></p></body></html>';
        exit;
    }

    unset($_SESSION['csrf_token']); // consumido → el siguiente formulario tendrá uno nuevo
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
