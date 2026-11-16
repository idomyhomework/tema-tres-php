<?php
declare(strict_types=1);

/*
 * Actividad 3.12: endpoint JSON protegido con Autenticación Básica HTTP.
 * Exige HTTPS: con Basic Auth la contraseña viaja solo en Base64 (no cifrada),
 * así que sin TLS cualquiera en la red podría leerla.
 *
 * Pruebas (ver README.md):
 *   curl -k https://localhost/Tema3/3-12/api.php                     → 401
 *   curl -k -u api:secreto123 https://localhost/Tema3/3-12/api.php   → 200
 *   curl    -u api:secreto123 http://localhost/Tema3/3-12/api.php    → 403 (sin HTTPS)
 */

const REALM = 'API Tema 3';
// Contraseñas hasheadas con password_hash(). Prueba: api / secreto123
const CLIENTES = [
    'api' => '$2y$10$mUMuJoWtvLe1za1l63JAXO4Yxv5.hD1od8zjP8eP8avzxBqUjWMUa',
];

header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $cuerpo): never
{
    http_response_code($codigo);
    echo json_encode($cuerpo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// No se confía en X-Forwarded-Proto: cualquier cliente puede enviar esa cabecera
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

if (!$https) {
    responder(403, ['error' => 'Este endpoint solo acepta conexiones HTTPS.']);
}

$usuario = $_SERVER['PHP_AUTH_USER'] ?? null;
$pass    = $_SERVER['PHP_AUTH_PW'] ?? null;

$valido = $usuario !== null
    && isset(CLIENTES[$usuario])
    && password_verify((string) $pass, CLIENTES[$usuario]);

if (!$valido) {
    header('WWW-Authenticate: Basic realm="' . REALM . '", charset="UTF-8"');
    responder(401, ['error' => 'Credenciales requeridas o incorrectas.']);
}

responder(200, [
    'usuario'   => $usuario,
    'protocolo' => $_SERVER['SERVER_PROTOCOL'] ?? '',
    'https'     => true,
    'hora'      => date(DATE_ATOM),
    'datos'     => ['mensaje' => 'Acceso autorizado al recurso protegido.'],
]);
