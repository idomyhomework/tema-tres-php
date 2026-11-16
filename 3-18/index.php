<?php

require_once 'funciones.php';   // funciones.php ya incluye settings.php

$errors = [];
$mensaje = '';

$data = DATOS_DEFAULT;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = validarFormulario($_POST);

    if (array_key_exists('nombre', $resultado)) {
        $resultado['fecha'] = date('Y-m-d H:i:s');
        guardarRespuesta($resultado);
        $mensaje = 'La valoracion fue guardada con exito!';
    } else {
        $errors = $resultado;
        $data = $_POST + DATOS_DEFAULT;   // para volver a rellenar el formulario
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Valoración</title>
</head>

<body>
    <h1>Deja tu valoración</h1>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($error)): ?>
        <p style="color: green;"><?= $mensaje ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <p><?= e($errors[0]) ?></p>
        </ul>
    <?php endif; ?>

    <form method="post">
        <p>
            <label for="nombre">Nombre:</label><br>
            <input type="text" id="nombre" name="nombre" minlength="<?= MIN_NOMBRE ?>" maxlength="<?= MAX_NOMBRE ?>"
                value="<?= e($data['nombre']) ?>">
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="<?= e($data['email']) ?>">
        </p>

        <p>
            <label for="valoracion">Valoración:</label><br>
            <select id="valoracion" name="valoracion">
                <?php for ($i = MIN_VALORACION; $i <= MAX_VALORACION; $i++): ?>
                    <option value="<?= $i ?>" <?= (string) $data['valoracion'] === (string) $i ? 'selected' : '' ?>>
                        <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
        </p>

        <p>
            <label for="comentario">Comentario:</label><br>
            <textarea id="comentario" name="comentario" rows="4" cols="40"><?= e($data['comentario']) ?></textarea>
        </p>

        <button type="submit">Enviar</button>
    </form>
</body>

</html>