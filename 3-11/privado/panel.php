<?php
declare(strict_types=1);
require_once __DIR__ . '/../sesion.php';

$usuario = exigirSesion(); // primera línea útil de toda página privada

// Últimos 20 accesos del log, el más reciente primero
$lineas = is_file(FICHERO_LOG) ? file(FICHERO_LOG, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$lineas = array_reverse(array_slice($lineas, -20));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad 3.11 - Panel privado</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 4px 8px; }
        .FALLO { color: #b00; } .ÉXITO { color: #070; }
    </style>
</head>

<body>
    <h1>Panel privado</h1>
    <p>Hola, <strong><?= e($_SESSION['nombre'] ?? $usuario) ?></strong>. Sesión iniciada el <?= e($_SESSION['desde'] ?? '') ?>.</p>
    <p><a href="perfil.php">Mi perfil</a> · <a href="../logout.php">Cerrar sesión</a></p>

    <h2>Registro de auditoría (accesos.txt)</h2>
    <?php if ($lineas): ?>
        <table>
            <tr><th>Fecha y hora</th><th>IP</th><th>Usuario</th><th>Resultado</th></tr>
            <?php foreach ($lineas as $linea): $c = array_pad(explode(' | ', $linea), 4, ''); ?>
                <tr>
                    <td><?= e($c[0]) ?></td><td><?= e($c[1]) ?></td><td><?= e($c[2]) ?></td>
                    <td class="<?= e($c[3]) ?>"><?= e($c[3]) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Sin registros.</p>
    <?php endif; ?>
</body>

</html>
