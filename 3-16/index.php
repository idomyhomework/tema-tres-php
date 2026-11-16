<?php
declare(strict_types=1);
require_once __DIR__ . '/sesion.php';

iniciarSesion();
if (!empty($_SESSION['usuario'])) {
    header('Location: perfil.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf(); // también el login va protegido (evita "login CSRF")

    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $pass    = (string) ($_POST['pass'] ?? '');
    $datos   = USUARIOS[$usuario] ?? null;

    if ($datos !== null && password_verify($pass, $datos['hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
        $_SESSION['email']   = $datos['email']; // simulamos la BD en la sesión
        header('Location: perfil.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.16 - Login</title>
</head>

<body>
    <h1>Actividad 3.16: Protección CSRF</h1>
    <?php if ($error): ?><p style="color:#b00"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <?= campoCsrf() ?>
        <p><label>Usuario: <input type="text" name="usuario" required></label></p>
        <p><label>Contraseña: <input type="password" name="pass" required></label></p>
        <button type="submit">Entrar</button>
    </form>
    <p><small>Prueba: usuario / usuario123</small></p>
</body>

</html>
