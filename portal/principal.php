<?php

    session_start();

    // Solo para usuarios con sesión iniciada
    if (!isset($_SESSION['nombre'])) {
        header('Location: login.php');
        exit;
    }

    $esAdmin = ($_SESSION['rol'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main>
    <div class="barra">
        <div class="barra__usuario">
            <span class="barra__nombre"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <span class="chip chip--<?= $esAdmin ? 'admin' : 'usuario' ?>"><?= htmlspecialchars($_SESSION['rol']) ?></span>
        </div>
        <form action="logout.php" method="post">
            <button type="submit" class="boton-secundario">Cerrar sesión</button>
        </form>
    </div>

    <h1>¡Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>!</h1>
    <p>Email: <?= htmlspecialchars($_SESSION['email']) ?></p>

    <?php if ($esAdmin): ?>
        <a class="enlace" href="admin.php">Ir al panel de administración</a>
    <?php endif; ?>
</main>
</body>
</html>
