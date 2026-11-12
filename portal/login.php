<?php

    session_start();

    if (isset($_SESSION['nombre'])) {
        header('Location: principal.php');
        exit;
    }

    require __DIR__ . '/db.php';

    $mail = "";
    $errores = [];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $mail = trim((string) filter_input(INPUT_POST, 'mail'));
        $pass = (string) filter_input(INPUT_POST, 'pass');

        // Email
        if ($mail === '') {
            $errores['mail'] = 'El email es obligatorio.';
        } elseif (filter_var($mail, FILTER_VALIDATE_EMAIL) === false) {
            $errores['mail'] = 'El formato del email no es válido.';
        }

        // Contraseña
        if ($pass === '') {
            $errores['pass'] = 'La contraseña es obligatoria.';
        }

        // Comprobar credenciales contra la base de datos
        if (empty($errores)) {
            $clave = mb_strtolower($mail, 'UTF-8');

            try {
                // Consulta preparada: el email nunca se mete dentro del SQL
                $stmt = conectar()->prepare('SELECT nombre, pass_hash, rol FROM usuarios WHERE email = ?');
                $stmt->execute([$clave]);
                $usuario = $stmt->fetch();   // array con los datos, o false si no existe
            } catch (PDOException $e) {
                // El detalle va al log del servidor, no a la pantalla (puede contener datos de conexión)
                error_log('Error de base de datos en login: ' . $e->getMessage());
                $usuario = false;
                $errores['bd'] = 'Error al conectar con la base de datos. Inténtalo más tarde.';
            }

            if ($usuario && password_verify($pass, $usuario['pass_hash'])) {
                session_regenerate_id(true);
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['email']  = $clave;
                $_SESSION['rol']    = $usuario['rol'];

                header('Location: ' . ($usuario['rol'] === 'admin' ? 'admin.php' : 'principal.php'));
                exit;
            }

            // Mismo mensaje si falla el email o la contraseña
            if (!isset($errores['bd'])) {
                $errores['login'] = 'Email o contraseña incorrectos.';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main>
    <h1>Iniciar sesión</h1>
    <p class="subtitulo">Introduce tu email y contraseña.</p>

    <?php foreach (['bd', 'login'] as $k): ?>
        <?php if (isset($errores[$k])): ?>
            <p class="mensaje mensaje--error"><?= htmlspecialchars($errores[$k]) ?></p>
        <?php endif; ?>
    <?php endforeach; ?>

    <form action="" method="post" novalidate>
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
        <button type="submit">Entrar</button>
    </form>
</main>
</body>
</html>
