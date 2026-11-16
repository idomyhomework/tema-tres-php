<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php

    // inicializamos user y error
    $error = "";
    $user = "";

    // validar input con isset y empty
    function validateInput($var): bool
    {
        if (!isset($var) || empty(trim($var))) {
            return false;
        }
        return true;
    }
    // verificamos el metodo post
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // inicializamos los campos del formulario
        $nombre = $_POST['nombre'] ?? '';
        $mail = $_POST['mail'] ?? '';
        $pass = $_POST['pass'] ?? '';
        // validamos cada campo con validateInput() 
        if (!validateInput($nombre)) {
            $error = "Introduce el nombre de usuario...";
            // para el email necesitamos filter_var ya que los emails tienen un formato especifico 
        } elseif (!validateInput($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $error = "Introduce un email de usuario válido...";
        } elseif (!validateInput($pass)) {
            $error = "Introduce la password de usuario...";
        } else {
            $user = $nombre;
        }
    }

    ?>

    <!-- mostramos el error si hay -->
    <?php if (!empty($error)): ?>
        <h3 style="color: red;"><?php echo $error; ?></h3>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
        <!-- bienvenida al usuario  -->
        <p>Bienvenido <?php echo htmlspecialchars($user); ?>!</p>
    <?php endif; ?>

</body>

</html>