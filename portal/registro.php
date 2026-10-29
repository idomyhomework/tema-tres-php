<?php

    const NOMBRE_MIN = 2;
    const NOMBRE_MAX = 50;
    const PASS_MIN = 8;

    $nombre = "";
    $mail = "";
    $errores = [];
    $registrado = false;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nombre     = trim((string) filter_input(INPUT_POST, 'nombre'));
        $mail       = trim((string) filter_input(INPUT_POST, 'mail'));
        $pass       = (string) filter_input(INPUT_POST, 'pass');
        $passRepeat = (string) filter_input(INPUT_POST, 'pass_repeat');

        // Nombre
        if ($nombre === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombre, 'UTF-8') < NOMBRE_MIN) {
            $errores['nombre'] = 'El nombre debe tener al menos ' . NOMBRE_MIN . ' caracteres.';
        } elseif (mb_strlen($nombre, 'UTF-8') > NOMBRE_MAX) {
            $errores['nombre'] = 'El nombre no puede tener más de ' . NOMBRE_MAX . ' caracteres.';
        }

        // Email
        if ($mail === '') {
            $errores['mail'] = 'El email es obligatorio.';
        } elseif (filter_var($mail, FILTER_VALIDATE_EMAIL) === false) {
            $errores['mail'] = 'El formato del email no es válido.';
        }

        // Contraseña 
        if (trim($pass) === '') {
            $errores['pass'] = 'La contraseña es obligatoria.';
        } elseif (mb_strlen($pass, 'UTF-8') < PASS_MIN
            || !preg_match('/[A-Za-z]/', $pass)
            || !preg_match('/\d/', $pass)) {
            $errores['pass'] = 'La contraseña debe tener al menos ' . PASS_MIN . ' caracteres, una letra y un número.';
        }

        // Repetir contraseña: solo se compara si la primera es válida
        if (!isset($errores['pass']) && $pass !== $passRepeat) {
            $errores['pass_repeat'] = 'Las contraseñas no coinciden.';
        }

        $registrado = empty($errores);
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main>
    <?php if (!$registrado): ?>
    <h1>Registro</h1>
    <p class="subtitulo">Rellena tus datos para continuar.</p>

    <form action="" method="post" novalidate>
        <div class="input--camp">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($nombre) ?>">
            <?php if (isset($errores['nombre'])): ?>
                <p class="mensaje mensaje--error"><?= htmlspecialchars($errores['nombre']) ?></p>
            <?php endif; ?>
        </div>
        <div class="input--camp">
            <label for="mail">Email</label>
            <input type="email" name="mail" id="mail" value="<?= htmlspecialchars($mail) ?>">
            <?php if (isset($errores['mail'])): ?>
                <p class="mensaje mensaje--error"><?= htmlspecialchars($errores['mail']) ?></p>
            <?php endif; ?>
        </div>
        <div class="input--camp">
            <label for="pass">Contraseña</label>
            <input type="password" name="pass" id="pass" value="">
            <?php if (isset($errores['pass'])): ?>
                <p class="mensaje mensaje--error"><?= htmlspecialchars($errores['pass']) ?></p>
            <?php endif; ?>
        </div>
        <div class="input--camp">
            <label for="pass_repeat">Repite la contraseña</label>
            <input type="password" name="pass_repeat" id="pass_repeat" value="">
            <?php if (isset($errores['pass_repeat'])): ?>
                <p class="mensaje mensaje--error"><?= htmlspecialchars($errores['pass_repeat']) ?></p>
            <?php endif; ?>
        </div>
        <button type="submit">Registrarse</button>
    </form>
    <?php else: ?>
        <h1>¡Bienvenido, <?= htmlspecialchars($nombre) ?>!</h1>
        <p>Email: <?= htmlspecialchars($mail) ?></p>
    <?php endif; ?>
</main>
</body>
</html>
