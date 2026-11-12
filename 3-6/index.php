<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';

/* ---------- Texto del formulario ---------- */
$textoUsuario = isset($_POST['texto']) ? (string) $_POST['texto'] : '';

/* ---------- Casos de ejemplo ---------- */
$ejemplos = [
    "  maría JOSÉ   de la ROSA  ",
    "ÁNGEL\tMuñoz-Pérez!!",
    "<b>Pingüino</b> en el Ñandú",
    "  CAFÉ con leche\n y AZÚCAR ",
    "José Ignacio Ibáñez",
];

/* ---------- Comparativa strtoupper vs mb_strtoupper ---------- */
$comparativa = ['ñandú', 'canción', 'pingüino', 'ÁRBOL'];

/* ---------- Búsqueda tolerante ---------- */
$clientes = ['José Muñoz', 'Jose Martínez', 'María Ibáñez', 'ÁNGEL MUÑOZ', 'Lucía Pérez'];
$consultas = ['munoz', 'JOSE', 'ibanez maria', 'angel  muñoz!'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.6 - Normalización y sanitización UTF-8</title>
</head>

<body>
    <h1>Actividad 3.6: Normalización y Sanitización de Cadenas UTF-8</h1>

    <h2>1. Prueba tu texto</h2>
    <form method="post">
        <input type="text" name="texto" size="50" value="<?= e($textoUsuario) ?>" placeholder="Ej: ÁNGEL  muñoz-pérez">
        <button type="submit">Procesar</button>
    </form>

    <?php if (trim($textoUsuario) !== ''): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Transformación</th>
                <th>Resultado</th>
            </tr>
            <tr>
                <td>Original</td>
                <td><?= visible($textoUsuario) ?></td>
            </tr>
                <?php foreach (procesar($textoUsuario) as $nombre => $valor): ?>
                <tr>
                    <td><?= e($nombre) ?></td>
                    <td><?= visible($valor) ?></td>
                </tr>
                <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <h2>2. Casos de ejemplo</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Original</th>
            <?php foreach (array_keys(procesar('')) as $nombre): ?>
                <th><?= e($nombre) ?></th><?php endforeach; ?>
        </tr>
        <?php foreach ($ejemplos as $texto): ?>
            <tr>
                <td><?= visible($texto) ?></td>
                    <?php foreach (procesar($texto) as $valor): ?>
                    <td><?= e($valor) ?></td><?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>3. ¿Por qué funciones multibyte?</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Texto</th>
            <th>strtoupper()</th>
            <th>mb_strtoupper()</th>
            <th>strlen()</th>
            <th>mb_strlen()</th>
        </tr>
        <?php foreach ($comparativa as $texto): ?>
            <tr>
                <td><?= e($texto) ?></td>
                <td><?= e(strtoupper($texto)) ?></td>
                <td><?= e(mb_strtoupper($texto)) ?></td>
                <td><?= strlen($texto) ?></td>
                <td><?= mb_strlen($texto) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p>strtoupper() y strlen() trabajan por bytes: las letras con tilde ocupan 2 bytes en UTF-8 y no se convierten ni se
        cuentan bien.</p>

    <h2>4. Búsqueda sin tildes ni mayúsculas</h2>
    <p>Clientes: <?= e(implode(' | ', $clientes)) ?></p>
    <table border="1" cellpadding="5">
        <tr>
            <th>Consulta</th>
            <th>Clave normalizada</th>
            <th>Nº coincidencias</th>
            <th>Resultados (índice: valor)</th>
        </tr>
        <?php foreach ($consultas as $consulta): ?>
                <?php $encontrados = buscar($consulta, $clientes); ?>
            <tr>
                <td><?= visible($consulta) ?></td>
                <td><?= e(claveBusqueda($consulta)) ?></td>
                <td><?= count($encontrados) ?></td>
                <td>
                        <?php foreach ($encontrados as $i => $valor): ?>
                                <?= $i ?>: <?= e($valor) ?><br>
                        <?php endforeach; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>