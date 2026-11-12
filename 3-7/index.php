<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';

$datos = ['texto' => '', 'accion' => 'cifrar', 'clave' => 3];
$errores = [];
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$datos, $errores] = validarFormulario($_POST);

    if (!$errores) {
        $resultado = cesar($datos['texto'], $datos['clave'], $datos['accion']);
        $inversa = $datos['accion'] === 'cifrar' ? 'descifrar' : 'cifrar';
        $comprobado = cesar($resultado, $datos['clave'], $inversa);
        $pasos = detalle($datos['texto'], $datos['clave'], $datos['accion']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.7 - Cifrado César</title>
</head>

<body>
    <h1>Actividad 3.7: Cifrado y Descifrado César</h1>

    <h2>Alfabeto (<?= TAM_ABECEDARIO ?> letras)</h2>
    <table border="1" cellpadding="4">
        <tr>
            <th>Posición</th><?php foreach (alfabeto() as $pos => $l): ?>
                <td><?= $pos ?></td><?php endforeach; ?>
        </tr>
        <tr>
            <th>Letra</th><?php foreach (alfabeto() as $l): ?>
                <td><?= e($l) ?></td><?php endforeach; ?>
        </tr>
    </table>
    <p>Las vocales con tilde o diéresis se convierten antes de cifrar (á→a, ü→u). La Ñ sí se cifra.</p>

    <form method="post">
        <p>
            <label for="texto">Texto:</label><br>
            <textarea id="texto" name="texto" rows="4" cols="60"><?= e($datos['texto']) ?></textarea>
        </p>
        <p>
            Acción:
            <label><input type="radio" name="accion" value="cifrar" <?= $datos['accion'] === 'cifrar' ? 'checked' : '' ?>>
                Cifrar</label>
            <label><input type="radio" name="accion" value="descifrar" <?= $datos['accion'] === 'descifrar' ? 'checked' : '' ?>> Descifrar</label>
        </p>
        <p>
            <label for="clave">Clave (posiciones):</label>
            <input type="number" id="clave" name="clave" value="<?= e((string) $datos['clave']) ?>" required>
        </p>
        <button type="submit">Procesar</button>
    </form>

    <?php if ($errores): ?>
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($resultado !== null): ?>
        <h2>Resultado</h2>
        <table border="1" cellpadding="5">
            <tr>
                <th>Texto original</th>
                <td><?= e($datos['texto']) ?></td>
            </tr>
            <tr>
                <th>Sin tildes</th>
                <td><?= e(quitarTildes($datos['texto'])) ?></td>
            </tr>
            <tr>
                <th><?= $datos['accion'] === 'cifrar' ? 'Cifrado' : 'Descifrado' ?> (clave <?= $datos['clave'] ?>)</th>
                <td><strong><?= e($resultado) ?></strong></td>
            </tr>
            <tr>
                <th>Comprobación (<?= e($inversa) ?>)</th>
                <td><?= e($comprobado) ?></td>
            </tr>
        </table>

        <h2>Paso a paso con ord() y chr()</h2>
        <p>Solo letras (máx. 20). Espacios y signos se copian sin cambios. Desplazamiento aplicado:
            <?= $datos['accion'] === 'cifrar' ? '+' : '-' ?>     <?= abs($datos['clave']) ?> (mod <?= TAM_ABECEDARIO ?>).
        </p>
        <table border="1" cellpadding="5">
            <tr>
                <th>Letra</th>
                <th>ord()</th>
                <th>Posición (0-26)</th>
                <th>Nueva posición</th>
                <th>chr()</th>
                <th>Resultado</th>
            </tr>
            <?php foreach ($pasos as $fila): ?>
                <tr><?php foreach ($fila as $celda): ?>
                        <td><?= e((string) $celda) ?></td><?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>

</html>