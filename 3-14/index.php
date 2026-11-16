<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';

$errores = [];
$datos   = ['nombre' => '', 'categoria' => '', 'precio' => '', 'stock' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$datos, $errores] = validarProducto($_POST);

    if (!$errores) {
        $productos   = leerProductos();
        $productos[] = [
            'id'        => siguienteId($productos),
            'nombre'    => $datos['nombre'],
            'categoria' => $datos['categoria'],
            'precio'    => round($datos['precio_num'], 2),
            'stock'     => $datos['stock_num'],
        ];
        guardarProductos($productos);

        header('Location: ?ok=1');   // PRG: evita duplicar el alta al recargar
        exit;
    }
}

// Orden dinámico por la columna pulsada en la cabecera
$campo = array_key_exists($_GET['orden'] ?? '', COLUMNAS) ? $_GET['orden'] : 'id';
$dir   = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$productos = ordenar(leerProductos(), $campo, $dir);

$valorTotal = array_sum(array_map(fn($p) => $p['precio'] * $p['stock'], $productos));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad 3.14 - Productos en JSON</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        label { display: inline-block; margin: .3rem 1rem .3rem 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th a { text-decoration: none; color: inherit; }
        td.num { text-align: right; }
        .error { color: #b00; } .ok { color: #070; }
        .sin-stock { color: #b00; font-weight: bold; }
    </style>
</head>

<body>
    <h1>Actividad 3.14: Almacén de productos en JSON</h1>

    <h2>Alta rápida</h2>
    <?php if (isset($_GET['ok'])): ?><p class="ok">Producto añadido.</p><?php endif; ?>
    <?php if ($errores): ?>
        <ul class="error"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <form method="post">
        <label>Nombre <input type="text" name="nombre" value="<?= e($datos['nombre']) ?>" maxlength="100" required></label>
        <label>Categoría
            <select name="categoria" required>
                <option value="">-- elige --</option>
                <?php foreach (CATEGORIAS as $c): ?>
                    <option value="<?= e($c) ?>" <?= $datos['categoria'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Precio (€) <input type="number" name="precio" value="<?= e($datos['precio']) ?>" step="0.01" min="0" required></label>
        <label>Stock <input type="number" name="stock" value="<?= e($datos['stock']) ?>" step="1" min="0" required></label>
        <button type="submit">Añadir</button>
    </form>

    <h2>Productos (<?= count($productos) ?>)</h2>
    <?php if ($productos): ?>
        <table>
            <tr>
                <?php foreach (COLUMNAS as $clave => $titulo):
                    $nuevaDir = ($campo === $clave && $dir === 'asc') ? 'desc' : 'asc';
                    $flecha   = $campo === $clave ? ($dir === 'asc' ? ' ▲' : ' ▼') : ''; ?>
                    <th><a href="?orden=<?= $clave ?>&amp;dir=<?= $nuevaDir ?>"><?= e($titulo . $flecha) ?></a></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td class="num"><?= (int) $p['id'] ?></td>
                    <td><?= e((string) $p['nombre']) ?></td>
                    <td><?= e((string) $p['categoria']) ?></td>
                    <td class="num"><?= e(number_format((float) $p['precio'], 2, ',', '.')) ?> €</td>
                    <td class="num <?= (int) $p['stock'] === 0 ? 'sin-stock' : '' ?>"><?= (int) $p['stock'] ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th colspan="3">Valor total del inventario</th>
                <td class="num" colspan="2"><strong><?= e(number_format($valorTotal, 2, ',', '.')) ?> €</strong></td>
            </tr>
        </table>
    <?php else: ?>
        <p>Todavía no hay productos. El fichero <code>productos.json</code> se creará con el primer alta.</p>
    <?php endif; ?>
</body>

</html>
