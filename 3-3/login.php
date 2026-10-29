<?php
require_once __DIR__ . '/includes/sesion.php';

// Hash de una contraseña aleatoria que nadie conoce. Se usa cuando el usuario
// no existe, para que password_verify() tarde lo mismo y no se pueda adivinar
// qué usuarios existen midiendo el tiempo de respuesta.
const HASH_FALSO = '$2y$10$QNsHvdYYXnzuoY.3VrJnGOvbiIvJRSqntadMI4TDgecgQuC4zjj9K';

// Si ya hay sesión, no tiene sentido volver a pedir login
$actual = usuarioActual();
if ($actual !== null) {
    redirigir(DESTINOS[$actual['rol']]);
}

$error  = '';
$nombre = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string) filter_input(INPUT_POST, 'usuario'));
    $pass   = (string) filter_input(INPUT_POST, 'pass');

    if ($nombre === '' || $pass === '') {
        $error = 'Introduce usuario y contraseña.';
    } else {
        $usuarios = require 'includes/usuarios.php';
        $datos    = $usuarios[$nombre] ?? null;
        $hash     = $datos['hash'] ?? HASH_FALSO;

        if (password_verify($pass, $hash) && $datos !== null) {
            // Nuevo ID de sesión y borra el anterior: evita la fijación de sesión
            session_regenerate_id(true);

            $_SESSION['usuario'] = $nombre;
            $_SESSION['rol']     = $datos['rol'];

            redirigir(DESTINOS[$datos['rol']]);
        }

        // El mismo mensaje tanto si falla el usuario como la contraseña
        $error = 'Usuario o contraseña incorrectos.';
    }
}

$haSalido = filter_input(INPUT_GET, 'salida') === '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
<main class="card">
    <h1>Iniciar sesión</h1>
    <p class="subtitulo">Accede con tu cuenta para continuar.</p>

    <?php if ($error !== ''): ?>
        <p class="mensaje mensaje--error"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($haSalido): ?>
        <p class="mensaje mensaje--ok">Has cerrado sesión correctamente.</p>
    <?php endif; ?>

    <form action="" method="post">
        <div class="input--camp">
            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" id="usuario" autocomplete="username"
                   value="<?= htmlspecialchars($nombre) ?>" required>
        </div>
        <div class="input--camp">
            <label for="pass">Contraseña</label>
            <input type="password" name="pass" id="pass" autocomplete="current-password" required>
        </div>
        <button type="submit">Entrar</button>
    </form>
</main>
</body>
</html>
