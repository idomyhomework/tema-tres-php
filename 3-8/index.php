<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';

$historial = leerHistorial();
$datos = ['a' => '', 'b' => '', 'op' => 'sumar'];
$resultado = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['borrar'])) {
        borrarHistorial();
        // Patrón PRG: redirigir evita reenviar el formulario al recargar
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    $datos = [
        'a' => trim((string) ($_POST['a'] ?? '')),
        'b' => trim((string) ($_POST['b'] ?? '')),
        'op' => (string) ($_POST['op'] ?? ''),
    ];
    $a = filter_var($datos['a'], FILTER_VALIDATE_FLOAT);
    $b = filter_var($datos['b'], FILTER_VALIDATE_FLOAT);

    if ($a === false || $b === false) {
        $error = 'Introduce dos números válidos.';
    } elseif (!isset(OPERACIONES[$datos['op']])) {
        $error = 'Selecciona una operación válida.';
    } else {
        try {
            $resultado = calcular($a, $b, $datos['op']);
            $texto = formatear($a) . ' ' . OPERACIONES[$datos['op']] . ' ' . formatear($b) . ' = ' . formatear($resultado);
            $historial = guardarEnHistorial($historial, $texto);
        } catch (DivisionByZeroError $ex) {
            $error = $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad 3.8 - Calculadora con historial en cookies</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .contenedor {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .calculadora {
            flex: 2;
            min-width: 260px;
        }

        aside {
            flex: 1;
            min-width: 220px;
            border-left: 3px solid #ccc;
            padding-left: 1rem;
        }

        .error {
            color: #b00;
        }

        .resultado {
            font-size: 1.5rem;
            font-weight: bold;
        }

        input[type=text] {
            width: 8rem;
        }
    </style>
</head>

<body>
    <h1>Actividad 3.8: Calculadora con historial en cookies</h1>

    <div class="contenedor">
        <section class="calculadora">
            <form method="post">
                <input type="text" name="a" value="<?= e($datos['a']) ?>" placeholder="Número A" inputmode="decimal"
                    required>
                <select name="op">
                    <?php foreach (OPERACIONES as $clave => $simbolo): ?>
                        <option value="<?= $clave ?>" <?= $datos['op'] === $clave ? 'selected' : '' ?>><?= $simbolo ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="b" value="<?= e($datos['b']) ?>" placeholder="Número B" inputmode="decimal"
                    required>
                <button type="submit">=</button>
            </form>

            <?php if ($error !== ''): ?>
                <p class="error"><?= e($error) ?></p>
            <?php elseif ($resultado !== null): ?>
                <p class="resultado">Resultado: <?= e(formatear($resultado)) ?></p>
            <?php endif; ?>
        </section>

        <aside>
            <h2>Historial (últimas <?= MAX_HISTORIAL ?>)</h2>
            <?php if ($historial): ?>
                <ol>
                    <?php foreach ($historial as $linea): ?>
                        <li><?= e($linea) ?></li>
                    <?php endforeach; ?>
                </ol>
                <form method="post">
                    <button type="submit" name="borrar" value="1">Borrar historial</button>
                </form>
            <?php else: ?>
                <p>Todavía no hay operaciones.</p>
            <?php endif; ?>
            <p><small>Guardado en la cookie <code><?= COOKIE_HISTORIAL ?></code> como JSON.</small></p>
        </aside>
    </div>
</body>

</html>