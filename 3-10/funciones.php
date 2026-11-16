<?php
declare(strict_types=1);

/*
 * Cada parte es un fichero de texto plano:
 *   averias/pendientes/parte_<id>.txt   → incidencias abiertas
 *   averias/resueltas/parte_<id>.txt    → incidencias cerradas
 *
 * Formato del fichero (una línea "Clave: valor" por campo, después la descripción):
 *   ID: 20261116-153012-ab12
 *   Fecha: 2026-11-16 15:30:12
 *   Técnico: Ana
 *   Equipo: Impresora planta 2
 *   Prioridad: alta
 *   Título: No imprime
 *   ---
 *   Descripción libre en varias líneas...
 */

const DIR_AVERIAS = __DIR__ . '/averias';
const ESTADOS     = ['pendientes', 'resueltas'];
const PRIORIDADES = ['baja', 'media', 'alta'];
const SEPARADOR   = '---';

function rutaEstado(string $estado): string
{
    if (!in_array($estado, ESTADOS, true)) {
        throw new InvalidArgumentException('Estado no válido.');
    }
    $dir = DIR_AVERIAS . '/' . $estado;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

/** Valida el ID para impedir rutas tipo "../../etc/passwd". */
function idValido(string $id): bool
{
    return (bool) preg_match('/^\d{8}-\d{6}-[a-f0-9]{4}$/', $id);
}

function rutaParte(string $estado, string $id): string
{
    return rutaEstado($estado) . "/parte_$id.txt";
}

/** Busca en qué carpeta está un parte. Devuelve el estado o null. */
function estadoDeParte(string $id): ?string
{
    if (!idValido($id)) {
        return null;
    }
    foreach (ESTADOS as $estado) {
        if (is_file(rutaParte($estado, $id))) {
            return $estado;
        }
    }
    return null;
}

/** Quita saltos de línea de los campos de una sola línea (no deben romper el formato). */
function unaLinea(string $s): string
{
    return trim(str_replace(["\r", "\n"], ' ', $s));
}

/** Crea un parte nuevo escribiendo línea a línea con fopen() + fputs(). */
function crearParte(array $d): string
{
    $id   = date('Ymd-His') . '-' . bin2hex(random_bytes(2));
    $ruta = rutaParte('pendientes', $id);

    $f = fopen($ruta, 'x'); // 'x' = falla si ya existe: nunca se sobrescribe un parte
    if ($f === false) {
        throw new RuntimeException('No se pudo crear el parte.');
    }
    fputs($f, "ID: $id\n");
    fputs($f, 'Fecha: ' . date('Y-m-d H:i:s') . "\n");
    fputs($f, 'Técnico: ' . unaLinea($d['tecnico']) . "\n");
    fputs($f, 'Equipo: ' . unaLinea($d['equipo']) . "\n");
    fputs($f, 'Prioridad: ' . $d['prioridad'] . "\n");
    fputs($f, 'Título: ' . unaLinea($d['titulo']) . "\n");
    fputs($f, SEPARADOR . "\n");
    fputs($f, str_replace("\r\n", "\n", trim($d['descripcion'])) . "\n");
    fclose($f);

    return $id;
}

/** Lee solo la cabecera de un parte con fgets() (no carga la descripción). */
function leerCabecera(string $ruta): array
{
    $cab = [];
    $f = fopen($ruta, 'r');
    if ($f === false) {
        return $cab;
    }
    while (($linea = fgets($f)) !== false) {
        $linea = rtrim($linea, "\r\n");
        if ($linea === SEPARADOR) {
            break;
        }
        [$clave, $valor] = array_pad(explode(': ', $linea, 2), 2, '');
        $cab[$clave] = $valor;
    }
    fclose($f);
    return $cab;
}

/** Lista los partes de un estado, del más reciente al más antiguo. */
function listarPartes(string $estado): array
{
    $partes = [];
    foreach (glob(rutaEstado($estado) . '/parte_*.txt') ?: [] as $ruta) {
        $partes[] = leerCabecera($ruta) + ['Estado' => $estado];
    }
    usort($partes, fn($a, $b) => strcmp($b['ID'] ?? '', $a['ID'] ?? ''));
    return $partes;
}

/** Lee el parte completo con file_get_contents(). */
function leerParte(string $id): ?array
{
    $estado = estadoDeParte($id);
    if ($estado === null) {
        return null;
    }
    $contenido = file_get_contents(rutaParte($estado, $id));
    [$cabecera, $descripcion] = array_pad(explode("\n" . SEPARADOR . "\n", $contenido, 2), 2, '');

    return [
        'estado'      => $estado,
        'cabecera'    => leerCabecera(rutaParte($estado, $id)),
        'descripcion' => $descripcion,
        'bruto'       => $contenido,
    ];
}

/**
 * Cambia el estado moviendo el fichero de carpeta con rename().
 * Al resolver, añade al final una nota con file_put_contents(FILE_APPEND).
 */
function cambiarEstado(string $id, string $nuevo, string $nota = ''): bool
{
    $actual = estadoDeParte($id);
    if ($actual === null || $actual === $nuevo || !in_array($nuevo, ESTADOS, true)) {
        return false;
    }
    $origen  = rutaParte($actual, $id);
    $destino = rutaParte($nuevo, $id);

    $registro = "\n[" . date('Y-m-d H:i:s') . '] Movido a ' . strtoupper($nuevo);
    if (trim($nota) !== '') {
        $registro .= ': ' . unaLinea($nota);
    }
    file_put_contents($origen, $registro . "\n", FILE_APPEND | LOCK_EX);

    return rename($origen, $destino);
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
