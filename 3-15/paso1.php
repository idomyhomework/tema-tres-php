<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

registrarVisita('paso1');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.15 - Paso 1</title>
</head>

<body>
    <h1>Actividad 3.15 · Paso 1</h1>
    <p>Sesión mantenida <strong>sin cookies</strong>, propagando el SID por la URL.</p>

    <table border="1" cellpadding="5">
        <tr><th>session_id()</th><td><code><?= e(session_id()) ?></code></td></tr>
        <tr><th>Constante SID</th><td><code><?= e(SID) ?></code></td></tr>
        <tr><th>Visitas totales</th><td><?= (int) $_SESSION['visitas_total'] ?></td></tr>
        <tr><th>Visitas a esta página</th><td><?= (int) $_SESSION['visitas']['paso1'] ?></td></tr>
        <tr><th>Cookies recibidas</th><td><?= $_COOKIE ? e(implode(', ', array_keys($_COOKIE))) : '(ninguna)' ?></td></tr>
    </table>

    <h2>Navegación por enlace (GET)</h2>
    <p><a href="paso2.php?<?= e(SID) ?>">Ir al paso 2 conservando la sesión</a></p>
    <p><a href="paso2.php">Ir al paso 2 SIN el SID</a> (se pierde la sesión: se crea una nueva)</p>
    <p><a href="paso1.php?<?= e(SID) ?>">Recargar paso 1 con SID</a> (el contador sube)</p>

    <h2>Formulario (POST con campo oculto)</h2>
    <form action="paso2.php" method="post">
        <?= campoSesion() ?>
        <p><label>Nombre: <input type="text" name="nombre" value="<?= e($_SESSION['form']['nombre'] ?? '') ?>" required></label></p>
        <p><label>Ciudad: <input type="text" name="ciudad" value="<?= e($_SESSION['form']['ciudad'] ?? '') ?>"></label></p>
        <button type="submit">Enviar al paso 2</button>
    </form>
</body>

</html>
