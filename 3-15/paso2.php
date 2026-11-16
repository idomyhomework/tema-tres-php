<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['form'] = [
        'nombre' => trim((string) ($_POST['nombre'] ?? '')),
        'ciudad' => trim((string) ($_POST['ciudad'] ?? '')),
    ];
}

registrarVisita('paso2');

$idPaso1 = $_SESSION['ids']['paso1'] ?? null;
$mismoId = $idPaso1 !== null && $idPaso1 === session_id();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.15 - Paso 2</title>
</head>

<body>
    <h1>Actividad 3.15 · Paso 2</h1>

    <table border="1" cellpadding="5">
        <tr><th>session_id() en paso 2</th><td><code><?= e(session_id()) ?></code></td></tr>
        <tr><th>session_id() visto en paso 1</th><td><code><?= e($idPaso1 ?? '(no se ha pasado por el paso 1 con esta sesión)') ?></code></td></tr>
        <tr>
            <th>¿Es la misma sesión?</th>
            <td style="color: <?= $mismoId ? 'green' : 'red' ?>"><strong><?= $mismoId ? 'SÍ' : 'NO' ?></strong></td>
        </tr>
        <tr><th>Visitas totales</th><td><?= (int) $_SESSION['visitas_total'] ?></td></tr>
        <tr><th>Visitas paso 1 / paso 2</th><td><?= (int) ($_SESSION['visitas']['paso1'] ?? 0) ?> / <?= (int) $_SESSION['visitas']['paso2'] ?></td></tr>
        <tr><th>Método de la petición</th><td><?= e($_SERVER['REQUEST_METHOD']) ?></td></tr>
    </table>

    <h2>Datos del formulario guardados en la sesión</h2>
    <?php if (!empty($_SESSION['form'])): ?>
        <ul>
            <li>Nombre: <?= e($_SESSION['form']['nombre']) ?></li>
            <li>Ciudad: <?= e($_SESSION['form']['ciudad']) ?></li>
        </ul>
    <?php else: ?>
        <p>Aún no se ha enviado el formulario.</p>
    <?php endif; ?>

    <p><a href="paso1.php?<?= e(SID) ?>">← Volver al paso 1 (con SID)</a></p>
    <p><a href="paso2.php?<?= e(SID) ?>">Recargar paso 2 (con SID)</a></p>

    <form method="post" action="paso1.php?<?= e(SID) ?>">
        <button type="submit" name="nada" value="1">Volver al paso 1 por POST</button>
    </form>

    <p><small>Riesgo: el ID en la URL puede filtrarse por la cabecera Referer, el historial o al
        compartir el enlace. Por eso en producción se usa <code>session.use_only_cookies = 1</code>.</small></p>
</body>

</html>
