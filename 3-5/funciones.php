<?php
declare(strict_types=1);

/* =========================================================
 * 0. NORMALIZACIÓN (para comparar "Leche " con "leche")
 * ========================================================= */

function clave(string $texto): string
{
    return strtolower(trim($texto));
}

/** Normaliza todos los valores de una lista. Conserva las claves. */
function normalizar(array $lista): array
{
    return array_map(fn($v) => clave((string) $v), $lista);
}

/* =========================================================
 * 1. FUNCIONES AUXILIARES (operaciones sobre catálogos)
 * ========================================================= */

/**
 * Añade un producto al inicio de la lista solo si no existe ya (sin distinguir mayúsculas/espacios).
 * Nativas: in_array() + array_unshift()
 */
function anadirNovedad(array &$lista, string $producto): bool
{
    if (in_array(clave($producto), normalizar($lista), true)) {
        return false;
    }
    array_unshift($lista, trim($producto));
    return true;
}

/**
 * Elimina un producto y reindexa la lista. Devuelve el índice que tenía, o false si no estaba.
 * Nativas: array_search() + unset + array_values()
 */
function quitarProducto(array &$lista, string $producto): int|false
{
    $indice = array_search(clave($producto), normalizar($lista), true);
    if ($indice === false) {
        return false;
    }
    unset($lista[$indice]);
    $lista = array_values($lista);
    return $indice;
}

/**
 * Mueve un producto existente a la primera posición (p. ej. para destacarlo).
 * Nativas: array_search() + array_unshift()
 */
function moverAlPrincipio(array &$lista, string $producto): bool
{
    $indice = array_search(clave($producto), normalizar($lista), true);
    if ($indice === false) {
        return false;
    }
    $valor = $lista[$indice];
    unset($lista[$indice]);
    array_unshift($lista, $valor);   // array_unshift también reindexa
    return true;
}

/**
 * Rotación de destacados: el primero pasa al final. Devuelve el producto rotado o null si está vacía.
 * Nativas: array_shift()
 */
function rotar(array &$lista): ?string
{
    if (!$lista) {
        return null;
    }
    $primero = array_shift($lista);
    $lista[] = $primero;
    return $primero;
}

/**
 * Los N últimos añadidos, del más reciente al más antiguo.
 * Nativas: array_reverse() + array_slice()
 */
function ultimosN(array $lista, int $n): array
{
    return array_slice(array_reverse($lista), 0, max(0, $n));
}

/**
 * Cambia el precio solo si el producto ya existe y el precio es válido
 * (evita crear claves nuevas por una errata como "kefir" en vez de "kéfir").
 * Nativas: array_key_exists()
 */
function actualizarPrecio(array &$precios, string $producto, float $precio): bool
{
    $k = clave($producto);
    if (!array_key_exists($k, $precios) || $precio < 0) {
        return false;
    }
    $precios[$k] = $precio;
    return true;
}

/* =========================================================
 * 2. ALGORITMO DE DUPLICADOS / ELEMENTOS COMUNES
 * ========================================================= */

/**
 * Recibe 2 o más listas con nombre: ['Tienda A' => [...], 'Tienda B' => [...], ...]
 * Devuelve, por cada elemento común a TODAS las listas:
 *   - nombre:        el valor original (tal cual aparece en la primera lista)
 *   - coincidencias: nº total de apariciones sumando todas las listas
 *   - indices:       ['Tienda A' => [0, 4], 'Tienda B' => [2], ...]
 */
function buscarComunes(array $listas): array
{
    if (count($listas) < 2) {
        throw new InvalidArgumentException('Se necesitan al menos dos listas.');
    }

    $normalizadas = array_map('normalizar', $listas);
    $primera = array_key_first($listas);

    // array_intersect conserva las claves de la 1ª lista -> nos sirve para recuperar el nombre original.
    // array_values evita que las claves de texto se interpreten como argumentos con nombre.
    $comunes = array_unique(array_intersect(...array_values($normalizadas)));

    $resultado = [];
    foreach ($comunes as $claveOriginal => $valor) {
        $indices = [];
        $total = 0;
        foreach ($normalizadas as $nombreLista => $lista) {
            $indices[$nombreLista] = array_keys($lista, $valor, true);
            $total += count($indices[$nombreLista]);
        }
        $resultado[] = [
            'nombre' => trim((string) $listas[$primera][$claveOriginal]),
            'coincidencias' => $total,
            'indices' => $indices,
        ];
    }
    return $resultado;
}

/**
 * Duplicados dentro de UNA sola lista.
 * array_unique se queda con la 1ª aparición; lo que sobra (array_diff_key) son las repeticiones.
 */
function duplicadosInternos(array $lista): array
{
    $norm = normalizar($lista);
    $repetidos = array_diff_key($norm, array_unique($norm));

    $resultado = [];
    foreach (array_unique($repetidos) as $valor) {
        $indices = array_keys($norm, $valor, true);
        $resultado[] = [
            'nombre' => trim((string) $lista[$indices[0]]),
            'veces' => count($indices),
            'indices' => $indices,
        ];
    }
    return $resultado;
}

/* =========================================================
 * 3. UTILIDADES DE PRESENTACIÓN
 * ========================================================= */

/** Escapa texto para HTML. */
function e(mixed $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Representa cualquier valor de forma legible (arrays, booleanos, false...). */
function mostrar(mixed $valor): string
{
    return e(json_encode($valor, JSON_UNESCAPED_UNICODE));
}