<?php
declare(strict_types=1);
require_once __DIR__ . '/sesion.php';

$usuario = exigirSesion();
$error   = '';
$ok      = isset($_GET['ok']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf(); // si falla, aquí termina con 403

    $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
    if ($email === false) {
        $error = 'Introduce un correo electrónico válido.';
    } else {
        $_SESSION['email'] = $email;
        header('Location: perfil.php?ok=1');
        exit;
    }
}

$token = tokenCsrf(); // nuevo token si el anterior se consumió
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.16 - Cambiar correo</title>
</head>

<body>
    <h1>Hola, <?= e($usuario) ?></h1>
    <p>Correo actual: <strong><?= e($_SESSION['email'] ?? '') ?></strong></p>

    <?php if ($ok): ?><p style="color:green">Correo actualizado. El token usado se ha destruido.</p><?php endif; ?>
    <?php if ($error): ?><p style="color:#b00"><?= e($error) ?></p><?php endif; ?>

    <h2>Cambiar correo electrónico</h2>
    <form method="post">
        <?= campoCsrf() ?>
        <p><label>Nuevo correo: <input type="email" name="email" required></label></p>
        <button type="submit">Guardar</button>
    </form>

    <p><small>Token CSRF actual: <code><?= e($token) ?></code></small></p>

    <h2>Pruebas</h2>
    <ul>
        <li><a href="ataque.html" target="_blank">Simular ataque CSRF</a> (formulario sin token → 403).</li>
        <li>Envía el formulario, vuelve atrás con el navegador y reenvíalo: el token ya se consumió → 403.</li>
    </ul>

    <form method="post" action="logout.php">
        <?= campoCsrf() ?>
        <button type="submit">Cerrar sesión</button>
    </form>
</body>

</html>
