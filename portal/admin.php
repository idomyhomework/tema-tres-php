<?php

    session_start();

    // Sin sesión: al login
    if (!isset($_SESSION['nombre'])) {
        header('Location: login.php');
        exit;
    }

    // Con sesión pero sin ser admin: 403 Forbidden
    if (($_SESSION['rol'] ?? '') !== 'admin') {
        http_response_code(403);
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso denegado</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main>
    <h1>403</h1>
    <p class="mensaje mensaje--error">No tienes permiso para acceder a esta página.</p>
    <a class="enlace" href="principal.php">Volver a la página principal</a>
</main>
</body>
</html>
        <?php
        exit;
    }

    $usuarios = require __DIR__ . '/usuarios.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main>
    <div class="barra">
        <div class="barra__usuario">
            <span class="barra__nombre"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <span class="chip chip--admin">admin</span>
        </div>
        <form action="logout.php" method="post">
            <button type="submit" class="boton-secundario">Cerrar sesión</button>
        </form>
    </div>

    <h1>Administración</h1>
    <p class="subtitulo">Usuarios registrados en el portal.</p>

    <ul class="lista">
        <?php foreach ($usuarios as $email => $datos): ?>
            <li class="lista__fila">
                <span><?= htmlspecialchars($datos['nombre']) ?> · <?= htmlspecialchars($email) ?></span>
                <span class="chip chip--<?= $datos['rol'] === 'admin' ? 'admin' : 'usuario' ?>"><?= htmlspecialchars($datos['rol']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <a class="enlace" href="principal.php">Volver a la página principal</a>
</main>
</body>
</html>
