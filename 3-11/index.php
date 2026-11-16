<?php
declare(strict_types=1);
require_once __DIR__ . '/sesion.php';

iniciarSesion();
if (!empty($_SESSION['usuario'])) {
    header('Location: privado/panel.php');
    exit;
}

$error = match ($_GET['error'] ?? '') {
    'credenciales' => 'Usuario o contraseña incorrectos.',
    'vacio'        => 'Introduce usuario y contraseña.',
    default        => '',
};
$salida = isset($_GET['salida']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad 3.11 - Acceso</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 3rem auto; padding: 0 1rem; }
        label { display: block; margin: .5rem 0; }
        input { width: 100%; padding: .4rem; }
        .error { color: #b00; } .ok { color: #070; }
    </style>
</head>

<body>
    <h1>Actividad 3.11: Iniciar sesión</h1>

    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <?php if ($salida): ?><p class="ok">Has cerrado la sesión.</p><?php endif; ?>

    <form action="verificar.php" method="post">
        <label>Usuario: <input type="text" name="usuario" autocomplete="username" required></label>
        <label>Contraseña: <input type="password" name="pass" autocomplete="current-password" required></label>
        <button type="submit">Entrar</button>
    </form>
    <p><small>Prueba: admin / admin123 · usuario / usuario123</small></p>
</body>

</html>
