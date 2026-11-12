<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';

/* ---------- Datos para probar las funciones auxiliares ---------- */
$novedades = ['Leche', 'Pan de centeno', 'Café'];               // indexado
$precios = ['leche' => 1.2, 'café' => 3.5, 'kéfir' => 2.1];    // asociativo
$inicial = ['novedades' => $novedades, 'precios' => $precios];

// Cada prueba: [nativas usadas, llamada, resultado, estado del array después]
$pruebas = [];

$r = anadirNovedad($novedades, 'Miel');
$pruebas[] = ['in_array + array_unshift', 'anadirNovedad($novedades, "Miel")', $r, $novedades];

$r = anadirNovedad($novedades, ' leche');
$pruebas[] = ['in_array + array_unshift', 'anadirNovedad($novedades, " leche")', $r, $novedades];

$r = quitarProducto($novedades, 'café');
$pruebas[] = ['array_search', 'quitarProducto($novedades, "café")', $r, $novedades];

$r = moverAlPrincipio($novedades, 'Pan de centeno');
$pruebas[] = ['array_search + array_unshift', 'moverAlPrincipio($novedades, "Pan de centeno")', $r, $novedades];

$r = rotar($novedades);
$pruebas[] = ['array_shift', 'rotar($novedades)', $r, $novedades];

$r = ultimosN($novedades, 2);
$pruebas[] = ['array_reverse', 'ultimosN($novedades, 2)', $r, $novedades];

$r = actualizarPrecio($precios, 'Kéfir', 2.4);
$pruebas[] = ['array_key_exists', 'actualizarPrecio($precios, "Kéfir", 2.4)', $r, $precios];

$r = actualizarPrecio($precios, 'kefir', 9);
$pruebas[] = ['array_key_exists', 'actualizarPrecio($precios, "kefir", 9)', $r, $precios];

$r = actualizarPrecio($precios, 'leche', -1);
$pruebas[] = ['array_key_exists', 'actualizarPrecio($precios, "leche", -1)', $r, $precios];

/* ---------- Catálogos de productos (2 o más listas) ---------- */
$catalogos = [
    'Tienda Centro' => ['Leche', 'Pan de centeno', 'Café', 'Arroz', 'Kéfir', 'Leche'],
    'Tienda Norte' => ['arroz', 'Té verde', 'leche ', 'Kéfir', 'Azúcar', 'Café'],
    'Tienda Sur' => ['Kéfir', 'Miel', 'Leche', 'Café', 'Pasta', 'kéfir'],
];

$comunes = buscarComunes($catalogos);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Actividad 3.5 - Arrays y duplicados</title>
</head>

<body>
    <h1>Actividad 3.5: Manipulación avanzada de Arrays y Búsqueda de Duplicados</h1>

    <h2>1. Funciones auxiliares</h2>
    <p>
        $novedades (inicial) = <?= mostrar($inicial['novedades']) ?><br>
        $precios (inicial) = <?= mostrar($inicial['precios']) ?>
    </p>
    <table border="1" cellpadding="5">
        <tr>
            <th>Nativas usadas</th>
            <th>Llamada</th>
            <th>Resultado</th>
            <th>Array después</th>
        </tr>
        <?php foreach ($pruebas as [$nativas, $llamada, $resultado, $despues]): ?>
            <tr>
                <td><?= e($nativas) ?></td>
                <td><code><?= e($llamada) ?></code></td>
                <td><?= mostrar($resultado) ?></td>
                <td><?= mostrar($despues) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>2. Catálogos de entrada</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Índice</th>
            <?php foreach (array_keys($catalogos) as $nombre): ?>
                <th><?= e($nombre) ?></th><?php endforeach; ?>
        </tr>
        <?php for ($i = 0, $max = max(array_map('count', $catalogos)); $i < $max; $i++): ?>
            <tr>
                <td><?= $i ?></td>
                <?php foreach ($catalogos as $lista): ?>
                    <td><?= e($lista[$i] ?? '') ?></td><?php endforeach; ?>
            </tr>
        <?php endfor; ?>
    </table>

    <h2>3. Productos comunes a todos los catálogos</h2>
    <p>Productos comunes encontrados: <strong><?= count($comunes) ?></strong></p>
    <table border="1" cellpadding="5">
        <tr>
            <th>#</th>
            <th>Producto</th>
            <th>Nº coincidencias</th>
            <?php foreach (array_keys($catalogos) as $nombre): ?>
                <th>Índices en <?= e($nombre) ?></th><?php endforeach; ?>
        </tr>
        <?php foreach ($comunes as $n => $fila): ?>
            <tr>
                <td><?= $n + 1 ?></td>
                <td><?= e($fila['nombre']) ?></td>
                <td><?= $fila['coincidencias'] ?></td>
                <?php foreach ($fila['indices'] as $indices): ?>
                    <td><?= e(implode(', ', $indices)) ?></td><?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>4. Duplicados dentro de cada catálogo</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Catálogo</th>
            <th>Producto</th>
            <th>Nº coincidencias</th>
            <th>Índices originales</th>
        </tr>
        <?php foreach ($catalogos as $nombre => $lista): ?>
            <?php $dups = duplicadosInternos($lista); ?>
            <?php if (!$dups): ?>
                <tr>
                    <td><?= e($nombre) ?></td>
                    <td colspan="3">Sin duplicados</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($dups as $d): ?>
                <tr>
                    <td><?= e($nombre) ?></td>
                    <td><?= e($d['nombre']) ?></td>
                    <td><?= $d['veces'] ?></td>
                    <td><?= e(implode(', ', $d['indices'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>
</body>

</html>