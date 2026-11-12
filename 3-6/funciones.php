<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');

/* =========================================================
 * 1. SUSTITUCIÓN DE TILDES, DIÉRESIS Y Ñ
 * ========================================================= */

const MAPA_TILDES = [
    'á' => 'a',
    'é' => 'e',
    'í' => 'i',
    'ó' => 'o',
    'ú' => 'u',
    'Á' => 'A',
    'É' => 'E',
    'Í' => 'I',
    'Ó' => 'O',
    'Ú' => 'U',
    'ä' => 'a',
    'ë' => 'e',
    'ï' => 'i',
    'ö' => 'o',
    'ü' => 'u',
    'Ä' => 'A',
    'Ë' => 'E',
    'Ï' => 'I',
    'Ö' => 'O',
    'Ü' => 'U',
    'ñ' => 'n',
    'Ñ' => 'N',
];

/** "Pingüino Ñandú" -> "Pinguino Nandu". str_replace con arrays sustituye todo el mapa de una pasada. */
function quitarTildes(string $texto): string
{
    return str_replace(array_keys(MAPA_TILDES), array_values(MAPA_TILDES), $texto);
}

/* =========================================================
 * 2. SANITIZACIÓN (antes de guardar o mostrar)
 * ========================================================= */

/**
 * - Garantiza UTF-8 válido (si llega en ISO-8859-1 lo convierte).
 * - Quita etiquetas HTML.
 * - Convierte tabuladores/saltos/espacios múltiples en un solo espacio.
 * - Elimina caracteres de control invisibles (\p{C}) y recorta extremos.
 * El modificador /u hace que las regex trabajen por caracteres UTF-8, no por bytes.
 */
function sanitizar(string $texto): string
{
    if (!mb_check_encoding($texto, 'UTF-8')) {
        $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
    }
    $texto = strip_tags($texto);
    $texto = preg_replace('/\s+/u', ' ', $texto);
    $texto = preg_replace('/\p{C}/u', '', $texto);
    return trim($texto);
}

/* =========================================================
 * 3. MAYÚSCULAS / MINÚSCULAS SEGURAS (multibyte)
 * ========================================================= */

/** Palabras que se quedan en minúscula dentro de un nombre propio (salvo si van primero). */
const PARTICULAS = ['de', 'del', 'la', 'las', 'los', 'y', 'e', 'da', 'van', 'von'];

/**
 * "maría JOSÉ de la ROSA" -> "María José de la Rosa"; "muñoz-PÉREZ" -> "Muñoz-Pérez".
 * mb_strtolower + mb_strtoupper sobre la 1ª letra: con strtoupper la 'á' o la 'ñ' se romperían.
 * La regex \p{L}+ recorre cada palabra aunque esté separada por guion o apóstrofo.
 */
function nombrePropio(string $texto): string
{
    $primera = true;

    return preg_replace_callback('/\p{L}+/u', function (array $m) use (&$primera): string {
        $palabra = $m[0];
        $esParticula = !$primera && in_array($palabra, PARTICULAS, true);
        $primera = false;

        return $esParticula
            ? $palabra
            : mb_strtoupper(mb_substr($palabra, 0, 1)) . mb_substr($palabra, 1);
    }, mb_strtolower(sanitizar($texto)));
}

/* =========================================================
 * 4. NORMALIZACIÓN PARA BÚSQUEDA / ALMACENAMIENTO
 * ========================================================= */

/**
 * Clave canónica: "  ÁNGEL   Muñoz-Pérez! " -> "angel munoz perez".
 * Dos textos que "significan lo mismo" producen la misma clave.
 */
function claveBusqueda(string $texto): string
{
    $texto = quitarTildes(mb_strtolower(sanitizar($texto)));
    $texto = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $texto);   // signos -> espacio
    return trim($texto);
}

/** Versión para URLs: "Café con Leche" -> "cafe-con-leche". */
function slug(string $texto): string
{
    return str_replace(' ', '-', claveBusqueda($texto));
}

/**
 * Busca sin importar tildes, mayúsculas, espacios ni signos.
 * Devuelve [índice => valor original] de los elementos que contienen todas las palabras de la consulta.
 */
function buscar(string $consulta, array $lista): array
{
    $palabras = array_filter(explode(' ', claveBusqueda($consulta)));
    if (!$palabras) {
        return [];
    }

    $resultado = [];
    foreach ($lista as $i => $valor) {
        $clave = claveBusqueda((string) $valor);
        $todas = array_reduce($palabras, fn($ok, $p) => $ok && str_contains($clave, $p), true);
        if ($todas) {
            $resultado[$i] = $valor;
        }
    }
    return $resultado;
}

/** Aplica todo el procesador y devuelve cada versión del texto. */
function procesar(string $texto): array
{
    $limpio = sanitizar($texto);
    return [
        'Sanitizado' => $limpio,
        'Sin tildes' => quitarTildes($limpio),
        'MAYÚSCULAS' => mb_strtoupper($limpio),
        'minúsculas' => mb_strtolower($limpio),
        'Nombre propio' => nombrePropio($limpio),
        'Clave búsqueda' => claveBusqueda($limpio),
        'Slug' => slug($limpio),
    ];
}

/* =========================================================
 * 5. PRESENTACIÓN
 * ========================================================= */

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/** Hace visibles los espacios para ver qué ha limpiado sanitizar(). */
function visible(string $texto): string
{
    return e('«' . str_replace(["\t", "\n"], ['\t', '\n'], $texto) . '»');
}