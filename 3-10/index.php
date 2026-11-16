<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';

$errores = [];
$datos   = ['tecnico' => '', 'equipo' => '', 'prioridad' => 'media', 'titulo' => '', 'descripcion' => ''];
$aviso   = (string) ($_GET['aviso'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');

    if ($accion === 'crear') {
        foreach ($datos as $campo => $_) {
            $datos[$campo] = trim((string) ($_POST[$campo] ?? ''));
        }
        if ($datos['tecnico'] === '')     $errores[] = 'Indica el técnico.';
        if ($datos['equipo'] === '')      $errores[] = 'Indica el equipo averiado.';
        if ($datos['titulo'] === '')      $errores[] = 'Indica un título.';
        if ($datos['descripcion'] === '') $errores[] = 'Describe la avería.';
        if (!in_array($datos['prioridad'], PRIORIDADES, true)) $errores[] = 'Prioridad no válida.';

        if (!$errores) {
            $id = crearParte($datos);
            header('Location: ?ver=' . urlencode($id) . '&aviso=creado');
            exit;
        }
    } elseif ($accion === 'estado') {
        $id    = (string) ($_POST['id'] ?? '');
        $nuevo = (string) ($_POST['nuevo'] ?? '');
        $ok    = cambiarEstado($id, $nuevo, (string) ($_POST['nota'] ?? ''));
        header('Location: ?ver=' . urlencode($id) . '&aviso=' . ($ok ? 'movido' : 'error'));
        exit;
    }
}

$ver   = (string) ($_GET['ver'] ?? '');
$parte = $ver !== '' ? leerParte($ver) : null;

$mensajes = [
    'creado' => 'Parte creado correctamente.',
    'movido' => 'Estado del parte actualizado.',
    'error'  => 'No se pudo cambiar el estado.',
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad 3.10 - Partes de averías</title>
    <style>
        body { font-family: sans-serif; max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 1.5rem; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .alta { color: #b00; font-weight: bold; }
        .aviso { background: #e8f5e9; padding: .5rem; }
        .error { color: #b00; }
        pre { background: #f4f4f4; padding: 1rem; white-space: pre-wrap; }
        label { display: block; margin-top: .5rem; }
    </style>
</head>

<body>
    <h1>Actividad 3.10: Gestión de partes de averías</h1>

    <?php if (isset($mensajes[$aviso])): ?>
        <p class="aviso"><?= e($mensajes[$aviso]) ?></p>
    <?php endif; ?>

    <?php if ($ver !== ''): ?>
        <p><a href="?">← Volver al listado</a></p>
        <?php if ($parte === null): ?>
            <p class="error">El parte no existe.</p>
        <?php else: ?>
            <h2>Parte <?= e($parte['cabecera']['ID'] ?? '') ?> (<?= e($parte['estado']) ?>)</h2>
            <table>
                <?php foreach ($parte['cabecera'] as $clave => $valor): ?>
                    <tr><th><?= e($clave) ?></th><td><?= e($valor) ?></td></tr>
                <?php endforeach; ?>
            </table>
            <h3>Descripción e historial</h3>
            <pre><?= e($parte['descripcion']) ?></pre>

            <form method="post">
                <input type="hidden" name="accion" value="estado">
                <input type="hidden" name="id" value="<?= e($ver) ?>">
                <?php if ($parte['estado'] === 'pendientes'): ?>
                    <input type="hidden" name="nuevo" value="resueltas">
                    <label>Nota de resolución: <input type="text" name="nota" size="50"></label>
                    <button type="submit">Marcar como resuelto</button>
                <?php else: ?>
                    <input type="hidden" name="nuevo" value="pendientes">
                    <label>Motivo de reapertura: <input type="text" name="nota" size="50"></label>
                    <button type="submit">Reabrir parte</button>
                <?php endif; ?>
            </form>

            <h3>Contenido bruto del fichero</h3>
            <pre><?= e($parte['bruto']) ?></pre>
        <?php endif; ?>

    <?php else: ?>
        <?php foreach (ESTADOS as $estado): $partes = listarPartes($estado); ?>
            <h2><?= ucfirst($estado) ?> (<?= count($partes) ?>)</h2>
            <?php if ($partes): ?>
                <table>
                    <tr><th>ID</th><th>Fecha</th><th>Equipo</th><th>Título</th><th>Prioridad</th><th>Técnico</th><th></th></tr>
                    <?php foreach ($partes as $p): ?>
                        <tr>
                            <td><?= e($p['ID'] ?? '') ?></td>
                            <td><?= e($p['Fecha'] ?? '') ?></td>
                            <td><?= e($p['Equipo'] ?? '') ?></td>
                            <td><?= e($p['Título'] ?? '') ?></td>
                            <td class="<?= e($p['Prioridad'] ?? '') ?>"><?= e($p['Prioridad'] ?? '') ?></td>
                            <td><?= e($p['Técnico'] ?? '') ?></td>
                            <td><a href="?ver=<?= urlencode($p['ID'] ?? '') ?>">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No hay partes <?= e($estado) ?>.</p>
            <?php endif; ?>
        <?php endforeach; ?>

        <h2>Nuevo parte</h2>
        <?php if ($errores): ?>
            <ul class="error"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="accion" value="crear">
            <label>Técnico: <input type="text" name="tecnico" value="<?= e($datos['tecnico']) ?>" required></label>
            <label>Equipo: <input type="text" name="equipo" value="<?= e($datos['equipo']) ?>" required></label>
            <label>Título: <input type="text" name="titulo" value="<?= e($datos['titulo']) ?>" required size="50"></label>
            <label>Prioridad:
                <select name="prioridad">
                    <?php foreach (PRIORIDADES as $p): ?>
                        <option value="<?= $p ?>" <?= $datos['prioridad'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Descripción:<br>
                <textarea name="descripcion" rows="5" cols="60" required><?= e($datos['descripcion']) ?></textarea>
            </label>
            <p><button type="submit">Registrar parte</button></p>
        </form>
    <?php endif; ?>
</body>

</html>
