<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';

iniciarSesion();

$_SESSION['calc'] ??= estadoInicial();

const TECLAS_VALIDAS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', '±',
    '+', '-', '*', '/', '=', 'C', 'CE', 'M+', 'M-', 'MR', 'MC'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tecla = (string) ($_POST['tecla'] ?? '');
    if (in_array($tecla, TECLAS_VALIDAS, true)) {
        $_SESSION['calc'] = pulsar($_SESSION['calc'], $tecla);
    }
    // PRG: al recargar no se repite la última tecla
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$s = $_SESSION['calc'];

// Distribución del teclado: [valor enviado, texto del botón, clase]
$filas = [
    [['MC', 'MC', 'mem'], ['MR', 'MR', 'mem'], ['M-', 'M−', 'mem'], ['M+', 'M+', 'mem']],
    [['C', 'C', 'fn'], ['CE', 'CE', 'fn'], ['±', '±', 'fn'], ['/', '÷', 'op']],
    [['7', '7', ''], ['8', '8', ''], ['9', '9', ''], ['*', '×', 'op']],
    [['4', '4', ''], ['5', '5', ''], ['6', '6', ''], ['-', '−', 'op']],
    [['1', '1', ''], ['2', '2', ''], ['3', '3', ''], ['+', '+', 'op']],
    [['0', '0', 'doble'], ['.', ',', ''], ['=', '=', 'igual']],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad 3.9 - Calculadora con sesión</title>
    <style>
        body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; padding: 1rem; }
        .calc { background: #222; padding: 1rem; border-radius: 12px; width: 300px; }
        .display { background: #dfe8d0; border-radius: 6px; padding: .5rem; text-align: right; font-family: monospace; margin-bottom: .75rem; }
        .display .expr { min-height: 1.2em; color: #555; }
        .display .num { font-size: 2rem; overflow-x: auto; }
        .display .m { float: left; font-weight: bold; }
        .teclado { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
        button { padding: .8rem 0; font-size: 1.1rem; border: 0; border-radius: 6px; cursor: pointer; background: #555; color: #fff; }
        button.op { background: #f39c12; }
        button.fn { background: #888; }
        button.mem { background: #2c3e50; font-size: .9rem; }
        button.igual { background: #27ae60; }
        button.doble { grid-column: span 2; }
        table { margin-top: 1rem; border-collapse: collapse; }
        td, th { border: 1px solid #ccc; padding: 4px 8px; }
    </style>
</head>

<body>
    <h1>Actividad 3.9: Calculadora con estado en sesión</h1>

    <div class="calc">
        <div class="display">
            <div class="expr">
                <?php if ($s['memoria'] != 0.0): ?><span class="m">M</span><?php endif; ?>
                <?= e(expresion($s)) ?>
            </div>
            <div class="num"><?= e($s['pantalla']) ?></div>
        </div>

        <form method="post" class="teclado">
            <?php foreach ($filas as $fila): ?>
                <?php foreach ($fila as [$valor, $texto, $clase]): ?>
                    <button type="submit" name="tecla" value="<?= e($valor) ?>" class="<?= $clase ?>"><?= e($texto) ?></button>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </form>
    </div>

    <h2>Estado en $_SESSION['calc']</h2>
    <table>
        <tr><th>Pantalla</th><td><?= e($s['pantalla']) ?></td></tr>
        <tr><th>Acumulador</th><td><?= $s['acumulador'] === null ? '—' : e(formatear($s['acumulador'])) ?></td></tr>
        <tr><th>Operador pendiente</th><td><?= $s['operador'] === null ? '—' : e(SIMBOLOS[$s['operador']]) ?></td></tr>
        <tr><th>Memoria (M)</th><td><?= e(formatear($s['memoria'])) ?></td></tr>
        <tr><th>ID de sesión</th><td><code><?= e(session_id()) ?></code></td></tr>
    </table>
</body>

</html>
