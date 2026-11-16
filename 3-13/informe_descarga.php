<?php
declare(strict_types=1);

/*
 * Actividad 3.13: descarga de un informe confidencial protegida con
 * Autenticación Básica HTTP (sin formulario HTML).
 *
 * Usuarios de prueba:  direccion / informe2026   ·   auditor / lector123
 * Descargar el fichero en vez de verlo: informe_descarga.php?descargar=1
 *
 * Si usas PHP-FPM y PHP_AUTH_USER llega vacío, añade "CGIPassAuth On" (ver .htaccess).
 */

const REALM = 'Zona Segura de Descargas';

// Lista de usuarios autorizados (contraseñas hasheadas con password_hash()).
const AUTORIZADOS = [
    'direccion' => '$2y$10$0fsa1KgM9cDzOP4o3cXcUu4BgWxnj7GD/TIOCI1glQioPdGZdCBIm',
    'auditor' => '$2y$10$h9.AAXwPAYhKUjLNtnvaeuIlqyEznYAYaDEm1BHcFyG9AqcdiclOG',
];

const HASH_FALSO = '$2y$10$QNsHvdYYXnzuoY.3VrJnGOvbiIvJRSqntadMI4TDgecgQuC4zjj9K';

/** Envía el 401 + WWW-Authenticate (el navegador abre el cuadro modal) y termina. */
function pedirCredenciales(string $mensaje): never
{
    header('HTTP/1.0 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="' . REALM . '"');
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>401</title></head><body>';
    echo '<h1>401 - Acceso denegado</h1><p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
    exit;
}

// 1) El cliente no ha enviado credenciales → forzamos el diálogo del navegador
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    pedirCredenciales('Esta zona requiere autenticación.');
}

// 2) Validamos lo recibido
$usuario = (string) $_SERVER['PHP_AUTH_USER'];
$pass = (string) ($_SERVER['PHP_AUTH_PW'] ?? '');

$hash = AUTORIZADOS[$usuario] ?? HASH_FALSO;
$valido = password_verify($pass, $hash) && isset(AUTORIZADOS[$usuario]);

if (!$valido) {
    pedirCredenciales('Usuario o contraseña incorrectos. Acceso denegado.');
}

// 3) Credenciales correctas → servimos el contenido protegido
$informe = "INFORME CONFIDENCIAL - " . date('Y') . "\n"
    . "Generado: " . date('Y-m-d H:i:s') . "\n"
    . "Solicitado por: $usuario\n\n"
    . "Ventas T1: 125.400 €\nVentas T2: 131.900 €\nVentas T3: 128.300 €\n"
    . "Previsión T4: 140.000 €\n";

if (isset($_GET['descargar'])) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="informe_confidencial.txt"');
    header('Content-Length: ' . strlen($informe));
    header('Cache-Control: no-store');
    echo $informe;
    exit;
}

header('Cache-Control: no-store');
$u = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.13 - Zona segura</title>
</head>

<body>
    <h1>Bienvenido/a, <?= $u ?></h1>
    <p>Has accedido a la <strong><?= REALM ?></strong>.</p>

    <h2>Informe confidencial</h2>
    <pre><?= htmlspecialchars($informe, ENT_QUOTES, 'UTF-8') ?></pre>

    <p><a href="?descargar=1">Descargar informe (.txt)</a></p>
    <p><small>Para "cerrar sesión" con Basic Auth hay que cerrar el navegador:
            este guarda las credenciales y las reenvía en cada petición.</small></p>
</body>

</html>