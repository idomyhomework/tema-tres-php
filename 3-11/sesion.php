<?php
declare(strict_types=1);

const FICHERO_LOG = __DIR__ . '/accesos.txt';

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

/** Se incluye al inicio de cada página privada: sin sesión → al login. */
function exigirSesion(): string
{
    iniciarSesion();
    if (empty($_SESSION['usuario'])) {
        header('Location: index.php');
        exit;
    }
    return $_SESSION['usuario'];
}

/** Añade una línea al final de accesos.txt: fecha | IP | usuario | resultado. */
function registrarAcceso(string $usuario, bool $exito): void
{
    $linea = sprintf(
        "%s | %s | %s | %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
        str_replace(["\r", "\n", '|'], ' ', $usuario), // evita inyectar líneas falsas en el log
        $exito ? 'ÉXITO' : 'FALLO'
    );
    file_put_contents(FICHERO_LOG, $linea, FILE_APPEND | LOCK_EX);
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
