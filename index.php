<?php
$error = "";
$user = "";
$imagenUrl = "";
$isLoggedIn = false;

const UPLOAD_DIR = __DIR__ . '/uploads/';
const UPLOAD_URL = 'uploads/';
const MAX_SIZE   = 1024 * 1024; // 1 MB
const ALLOWED    = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
];

function mensajeErrorSubida(int $code): string {
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "La imagen es demasiado grande.",
        UPLOAD_ERR_PARTIAL   => "La imagen se subió de forma incompleta.",
        UPLOAD_ERR_NO_FILE   => "Selecciona una imagen.",
        UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION
                             => "Error del servidor al subir la imagen.",
        default              => "Error desconocido al subir la imagen.",
    };
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim((string) filter_input(INPUT_POST, 'nombre'));
    $mail   = (string) filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
    $pass   = (string) filter_input(INPUT_POST, 'pass');

    // Si el POST supera post_max_size, PHP vacía $_POST y $_FILES
    if (empty($_POST) && empty($_FILES) && ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $error = "El envío supera el tamaño máximo permitido.";
    } elseif ($nombre === '') {
        $error = "Introduce el nombre de usuario...";
    } elseif ($mail === '' || $mail === false) {
        $error = "Introduce un email válido...";
    } elseif ($pass === '') {
        $error = "Introduce la contraseña...";
    } else {
        $file = $_FILES['imagen'] ?? null;

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $error = mensajeErrorSubida($file['error'] ?? UPLOAD_ERR_NO_FILE);
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $error = "Subida no válida.";
        } elseif ($file['size'] > MAX_SIZE) {
            $error = "La imagen no puede superar 1 MB.";
        } else {
            // MIME real, leyendo el contenido del archivo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);

            if (!isset(ALLOWED[$mime])) {
                $error = "Formato no permitido (solo JPG o PNG).";
            } elseif (getimagesize($file['tmp_name']) === false) {
                $error = "El archivo no es una imagen válida.";
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                $nombreArchivo = bin2hex(random_bytes(16)) . '.' . ALLOWED[$mime];

                if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nombreArchivo)) {
                    $imagenUrl  = UPLOAD_URL . $nombreArchivo;
                    $user       = $nombre;
                    $isLoggedIn = true;
                } else {
                    $error = "No se pudo guardar la imagen.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario en la misma página</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main>
<?php if ($error !== ""): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php elseif ($isLoggedIn): ?>
    <p style="text-align: center;">¡Bienvenido <?= htmlspecialchars($user) ?>!</p>
    <img src="<?= htmlspecialchars($imagenUrl) ?>" alt="<?= htmlspecialchars($user) ?>" width="200">
<?php endif; ?>

<?php if (!$isLoggedIn): ?>
<form action="" method="post" enctype="multipart/form-data">
    <div class="input--camp">
        <label for="nombre">Nombre: </label>
        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
    </div>
    <div class="input--camp">
        <label for="mail">Mail: </label>
        <input type="email" name="mail" id="mail" value="<?= htmlspecialchars($_POST['mail'] ?? '') ?>">
    </div>
    <div class="input--camp">
        <label for="pass">Password: </label>
        <input type="password" name="pass" id="pass">
    </div>
    <div class="input--camp">
        <label for="imagen" class="file-btn">Navega...</label>
        <input type="file" name="imagen" id="imagen"
                accept="<?= htmlspecialchars(implode(',', array_keys(ALLOWED))) ?>" class="file-hidden">
    </div>
    <button type="submit">Enviar</button>
</form>
<?php endif; ?>
</main>
</body>
</html>