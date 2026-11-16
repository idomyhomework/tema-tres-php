<?php
declare(strict_types=1);
require_once __DIR__ . '/../sesion.php';

$usuario = exigirSesion();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.11 - Perfil</title>
</head>

<body>
    <h1>Mi perfil</h1>
    <ul>
        <li>Usuario: <?= e($usuario) ?></li>
        <li>Nombre: <?= e($_SESSION['nombre'] ?? '') ?></li>
        <li>Sesión desde: <?= e($_SESSION['desde'] ?? '') ?></li>
        <li>IP: <?= e($_SERVER['REMOTE_ADDR'] ?? '') ?></li>
    </ul>
    <p><a href="panel.php">← Panel</a> · <a href="../logout.php">Cerrar sesión</a></p>
</body>

</html>
