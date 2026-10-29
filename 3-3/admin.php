<?php
require_once __DIR__ . '/includes/sesion.php';

// Solo el rol admin puede entrar aquí
$usuario  = exigirRol('admin');
$aviso    = tomarAviso();
$usuarios = require __DIR__ . '/includes/usuarios.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
<main class="card card--ancha">
    <?php require __DIR__ . '/includes/cabecera.php'; ?>

    <h1>Panel de administración</h1>
    <p class="subtitulo">Acceso total: gestión de usuarios y acceso a todas las zonas.</p>

    <?php if ($aviso !== null): ?>
        <p class="mensaje mensaje--error"><?= htmlspecialchars($aviso) ?></p>
    <?php endif; ?>

    <h2>Usuarios registrados</h2>
    <ul class="lista">
        <?php foreach ($usuarios as $nombre => $datos): ?>
            <li class="lista__fila">
                <span><?= htmlspecialchars($nombre) ?></span>
                <span class="chip chip--<?= htmlspecialchars($datos['rol']) ?>"><?= htmlspecialchars($datos['rol']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <a class="enlace" href="usuario.php">Ver la zona de usuario →</a>

    <details class="debug">
        <summary>Datos de la sesión</summary>
        <ul>
            <li>ID de sesión: <code><?= htmlspecialchars(session_id()) ?></code></li>
            <li><code>$_SESSION['usuario']</code> = <code><?= htmlspecialchars($usuario['nombre']) ?></code></li>
            <li><code>$_SESSION['rol']</code> = <code><?= htmlspecialchars($usuario['rol']) ?></code></li>
        </ul>
    </details>
</main>
</body>
</html>
