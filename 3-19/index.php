<?php

require_once 'funciones.php';

createXML();

$data = DATOS_DEFAULT;
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = validateInput($_POST);

    if (array_key_exists('autor', $data)) {
        saveInput($data);
        $mensaje = 'El libro fue guardado con exito!';
    } else {
        $error = $data[0];
        $data = $_POST + DATOS_DEFAULT;
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Biblioteca</title>
</head>

<body>
    <h1>Apunta el libro a la biblioteca aqui.</h1>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($error)): ?>
        <p style="color: green;"><?= $mensaje ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <ul style="color: red;">
            <p><?= e($error) ?></p>
        </ul>
    <?php endif; ?>

    <form method="post">
        <p>
            <label for="isbn">Isbn:</label><br>
            <input type="text" id="isbn" name="isbn" value="<?= e($data['isbn']) ?>">
        </p>

        <p>
            <label for="autor">Autor:</label><br>
            <input type="text" id="autor" name="autor" value="<?= e($data['autor']) ?>">
        </p>

        <p>
            <label for="title">Titulo</label><br>
            <input type="text" id="title" name="title" value="<?= e($data['title']) ?>">

        </p>

        <p>
            <label for="year">Year:</label><br>
            <input type="number" id="year" name="year" value="<?= e($data['year']) ?>">
        </p>

        <button type="submit">Enviar</button>
    </form>
</body>

</html>