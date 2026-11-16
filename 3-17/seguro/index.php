<?php
declare(strict_types=1);

/*
 * Se copia a /var/www/html/seguro/index.php.
 * Este código SOLO se ejecuta si Apache/NGINX ya ha validado las credenciales:
 * la autenticación la hace el servidor, no PHP.
 */
function e(string $t): string
{
    return htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
}

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$tipo  = $_SERVER['AUTH_TYPE'] ?? '(desconocido)';
$user  = $_SERVER['REMOTE_USER'] ?? $_SERVER['PHP_AUTH_USER'] ?? '(desconocido)';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.17 - Zona segura</title>
</head>

<body>
    <h1>Directorio protegido /seguro/</h1>
    <table border="1" cellpadding="6">
        <tr><th>Usuario autenticado por el servidor</th><td><?= e($user) ?></td></tr>
        <tr><th>Tipo de autenticación</th><td><?= e($tipo) ?></td></tr>
        <tr><th>Conexión cifrada (HTTPS)</th><td style="color:<?= $https ? 'green' : 'red' ?>"><?= $https ? 'SÍ' : 'NO' ?></td></tr>
        <tr><th>Protocolo TLS</th><td><?= e($_SERVER['SSL_PROTOCOL'] ?? '—') ?></td></tr>
        <tr><th>Cifrado</th><td><?= e($_SERVER['SSL_CIPHER'] ?? '—') ?></td></tr>
        <tr><th>Puerto</th><td><?= e((string) ($_SERVER['SERVER_PORT'] ?? '')) ?></td></tr>
        <tr><th>Software del servidor</th><td><?= e($_SERVER['SERVER_SOFTWARE'] ?? '') ?></td></tr>
    </table>
</body>

</html>
