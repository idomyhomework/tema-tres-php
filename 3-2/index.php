<?php

require_once "config.php";

// Directivas de seguridad comunes a todas las cookies
function opcionesCookie(int $expira): array {
    return [
        'expires'  => $expira,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // solo HTTPS si lo hay
        'httponly' => true,   // JavaScript no puede leerla
        'samesite' => 'Lax',  // no se envía en peticiones cruzadas tipo POST
    ];
}

// Devuelve el valor solo si está en la lista blanca (el cliente puede manipular cookies y POST)
function validar(mixed $valor, array $validos, ?string $defecto = null): ?string {
    return is_string($valor) && in_array($valor, $validos, true) ? $valor : $defecto;
}

// ---------- Guardar / borrar (antes de cualquier salida HTML) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) filter_input(INPUT_POST, 'accion');
        
    if ($accion === 'borrar') {
        $pasado = time() - 3600;
        setcookie('tema',   '', opcionesCookie($pasado));
        setcookie('idioma', '', opcionesCookie($pasado));
    } elseif($accion === 'guardar'){
        $expira = time() + COOKIE_DIAS * 24 * 60 * 60;
        $tema   = validar(filter_input(INPUT_POST, 'tema'), TEMAS);
        $idioma = validar(filter_input(INPUT_POST, 'idioma'), array_keys(IDIOMAS));

        if ($tema !== null)   setcookie('tema',   $tema,   opcionesCookie($expira));
        if ($idioma !== null) setcookie('idioma', $idioma, opcionesCookie($expira));
    }

    // PRG: $_COOKIE no se actualiza hasta la siguiente petición, así que recargamos
    header('Location: ' . basename(__FILE__));
    exit;
}

// ---------- Leer cookies existentes ----------
$hayPreferencias = isset($_COOKIE['tema']) || isset($_COOKIE['idioma']);
$tema   = validar($_COOKIE['tema']   ?? null, TEMAS, 'claro');
$idioma = validar($_COOKIE['idioma'] ?? null, array_keys(IDIOMAS), 'es');
$t      = TEXTOS[$idioma];
?>
<!DOCTYPE html>
<html lang="<?= $idioma ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['titulo']) ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="tema-<?= $tema ?>">
<main>
    <h1><?= htmlspecialchars($t['titulo']) ?></h1>
    <p><?= htmlspecialchars($hayPreferencias ? $t['vuelta'] : $t['nuevo']) ?></p>

    <form action="" method="post">
        <div class="input--camp">
            <label for="tema"><?= htmlspecialchars($t['tema']) ?></label>
            <select name="tema" id="tema">
                <?php foreach (TEMAS as $opcion): ?>
                    <option value="<?= $opcion ?>" <?= $opcion === $tema ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t[$opcion]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input--camp">
            <label for="idioma"><?= htmlspecialchars($t['idioma']) ?></label>
            <select name="idioma" id="idioma">
                <?php foreach (IDIOMAS as $codigo => $nombre): ?>
                    <option value="<?= $codigo ?>" <?= $codigo === $idioma ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nombre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="botones">
            <button type="submit" name="accion" value="guardar"><?= htmlspecialchars($t['guardar']) ?></button>
            <?php if ($hayPreferencias): ?>
                <button type="submit" name="accion" value="borrar"><?= htmlspecialchars($t['borrar']) ?></button>
            <?php endif; ?>
        </div>
    </form>
</main>
</body>
</html>