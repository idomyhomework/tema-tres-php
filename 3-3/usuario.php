<?php
require_once __DIR__ . '/includes/sesion.php';

// Zona restringida: entran usuarios y también el admin (que tiene acceso total)
$usuario = exigirRol('usuario', 'admin');
$aviso   = tomarAviso();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zona de usuario</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
<main class="card card--ancha">
    <?php require __DIR__ . '/includes/cabecera.php'; ?>

    <h1>Hola, <?= htmlspecialchars($usuario['nombre']) ?></h1>
    <p class="subtitulo">Esta es tu zona personal. Tu acceso está limitado a esta página.</p>

    <?php if ($aviso !== null): ?>
        <p class="mensaje mensaje--error"><?= htmlspecialchars($aviso) ?></p>
    <?php endif; ?>

    <?php if ($usuario['rol'] === 'admin'): ?>
        <a class="enlace" href="admin.php">← Volver al panel de administración</a>
    <?php endif; ?>
</main>
</body>
</html>
