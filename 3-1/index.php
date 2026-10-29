<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php

    $error = "";
    $user = "";

    function validateInput($var): bool {
        if (!isset($var) || empty(trim($var))) {
            return false;   
        } 
        return true;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $nombre = $_POST['nombre'] ?? '';
        $mail   = $_POST['mail'] ?? '';
        $pass   = $_POST['pass'] ?? '';

        if (!validateInput($nombre)) {
            $error = "Introduce el nombre de usuario...";
        } elseif (!validateInput($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $error = "Introduce un email de usuario válido...";
        } elseif (!validateInput($pass)) {
            $error = "Introduce la password de usuario...";
        } else {
            $user = $nombre;
        }
    }

?>

<?php if (!empty($error)): ?>
    <h3 style="color: red;"><?php echo $error; ?></h3>
<?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <p>Bienvenido <?php echo htmlspecialchars($user); ?>!</p>
<?php endif; ?>

</body>
</html>