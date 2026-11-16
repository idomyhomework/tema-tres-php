<?php
declare(strict_types=1);

const FICHERO_JSON = __DIR__ . '/productos.json';
const CATEGORIAS   = ['Informática', 'Electrónica', 'Hogar', 'Papelería', 'Otros'];
const COLUMNAS     = ['id' => 'ID', 'nombre' => 'Nombre', 'categoria' => 'Categoría', 'precio' => 'Precio', 'stock' => 'Stock'];

/** Lee productos.json. Si no existe o está corrupto, devuelve un array vacío. */
function leerProductos(): array
{
    if (!is_file(FICHERO_JSON)) {
        return [];
    }
    $json  = file_get_contents(FICHERO_JSON);
    $datos = json_decode((string) $json, true);
    return is_array($datos) ? $datos : [];
}

function guardarProductos(array $productos): void
{
    $json = json_encode($productos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(FICHERO_JSON, $json, LOCK_EX);
}

/** Siguiente ID autoincremental: máximo actual + 1. */
function siguienteId(array $productos): int
{
    return $productos ? max(array_column($productos, 'id')) + 1 : 1;
}

/** Valida el POST. Devuelve [datos limpios, errores]. */
function validarProducto(array $post): array
{
    $d = [
        'nombre'    => trim((string) ($post['nombre'] ?? '')),
        'categoria' => (string) ($post['categoria'] ?? ''),
        'precio'    => trim((string) ($post['precio'] ?? '')),
        'stock'     => trim((string) ($post['stock'] ?? '')),
    ];
    $errores = [];

    if ($d['nombre'] === '' || mb_strlen($d['nombre']) > 100) {
        $errores[] = 'El nombre es obligatorio (máx. 100 caracteres).';
    }
    if (!in_array($d['categoria'], CATEGORIAS, true)) {
        $errores[] = 'Selecciona una categoría válida.';
    }
    $precio = filter_var(str_replace(',', '.', $d['precio']), FILTER_VALIDATE_FLOAT);
    if ($precio === false || $precio < 0) {
        $errores[] = 'El precio debe ser un número positivo.';
    }
    $stock = filter_var($d['stock'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($stock === false) {
        $errores[] = 'El stock debe ser un entero mayor o igual que 0.';
    }

    return [$d + ['precio_num' => $precio, 'stock_num' => $stock], $errores];
}

/** Ordena por cualquier columna permitida, asc o desc. */
function ordenar(array $productos, string $campo, string $dir): array
{
    usort($productos, function ($a, $b) use ($campo, $dir) {
        $va = $a[$campo] ?? '';
        $vb = $b[$campo] ?? '';
        $cmp = is_string($va) ? strcasecmp($va, (string) $vb) : $va <=> $vb;
        return $dir === 'desc' ? -$cmp : $cmp;
    });
    return $productos;
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
