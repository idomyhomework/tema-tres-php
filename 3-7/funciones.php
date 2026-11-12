<?php
declare(strict_types=1);

/*
 * Alfabeto español (27 letras):
 *   A B C D E F G H I J K  L  M  N  Ñ  O  P  Q  R  S  T  U  V  W  X  Y  Z
 *   0 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26
 * A-Z se calculan con ord()/chr(). La Ñ es multibyte en UTF-8, así que es un caso especial.
 */
const TAM_ABECEDARIO = 27;
const POS_ENE = 14;
const ACCIONES = ['cifrar', 'descifrar'];

/** Solo vocales con tilde y diéresis. La ñ NO se toca: forma parte del alfabeto. */
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
];

function quitarTildes(string $texto): string
{
    return str_replace(array_keys(MAPA_TILDES), array_values(MAPA_TILDES), $texto);
}

function esEne(string $c): bool
{
    return $c === 'Ñ' || $c === 'ñ';
}

function esMayuscula(string $c): bool
{
    return $c === 'Ñ' || ($c >= 'A' && $c <= 'Z' && strlen($c) === 1);
}

/** Posición 0-26 de una letra en el alfabeto español; null si no es letra (espacio, signo, número...). */
function posicion(string $c): ?int
{
    if (esEne($c)) {
        return POS_ENE;
    }
    if (strlen($c) !== 1 || !ctype_alpha($c)) {
        return null;
    }
    $p = ord(strtoupper($c)) - ord('A');      // A=0 ... Z=25 en ASCII
    return $p >= POS_ENE ? $p + 1 : $p;       // de la O en adelante se corre 1 por la Ñ
}

/** Operación inversa: de posición 0-26 a letra, respetando mayúscula/minúscula. */
function letra(int $pos, bool $mayuscula): string
{
    if ($pos === POS_ENE) {
        return $mayuscula ? 'Ñ' : 'ñ';
    }
    $ascii = $pos > POS_ENE ? $pos - 1 : $pos;  // deshacer el hueco de la Ñ
    return chr(($mayuscula ? ord('A') : ord('a')) + $ascii);
}

/** Desplaza una letra en módulo 27. Lo que no es letra se devuelve igual. */
function desplazarLetra(string $c, int $clave): string
{
    $pos = posicion($c);
    if ($pos === null) {
        return $c;
    }
    // "+ 27) % 27" evita resultados negativos al descifrar (clave negativa)
    $nueva = (($pos + $clave) % TAM_ABECEDARIO + TAM_ABECEDARIO) % TAM_ABECEDARIO;
    return letra($nueva, esMayuscula($c));
}

/** Cifra o descifra un texto. Descifrar = desplazar en sentido contrario. */
function cesar(string $texto, int $clave, string $accion): string
{
    if (!in_array($accion, ACCIONES, true)) {
        throw new InvalidArgumentException("Acción no válida: $accion");
    }
    $clave = $accion === 'cifrar' ? $clave : -$clave;

    // mb_str_split: recorre por caracteres, no por bytes (la Ñ ocupa 2)
    $caracteres = mb_str_split(quitarTildes($texto), 1, 'UTF-8');
    return implode('', array_map(fn($c) => desplazarLetra($c, $clave), $caracteres));
}

/** Mapa completo del alfabeto [posición => letra], generado con letra(). */
function alfabeto(): array
{
    return array_map(fn($p) => letra($p, true), range(0, TAM_ABECEDARIO - 1));
}

/**
 * Paso a paso de cada letra. Filas: [letra, ord(), posición, nueva posición, chr(), resultado]
 * Para la Ñ no se usa ord()/chr(), se indica como caso especial.
 */
function detalle(string $texto, int $clave, string $accion, int $max = 20): array
{
    $clave = $accion === 'cifrar' ? $clave : -$clave;
    $filas = [];

    foreach (mb_str_split(quitarTildes($texto), 1, 'UTF-8') as $c) {
        $pos = posicion($c);
        if ($pos === null) {
            continue;
        }
        $nueva = desplazarLetra($c, $clave);
        $filas[] = [
            $c,
            esEne($c) ? 'Ñ (especial)' : (string) ord($c),
            $pos,
            posicion($nueva),
            esEne($nueva) ? 'Ñ (especial)' : 'chr(' . ord($nueva) . ')',
            $nueva,
        ];
        if (count($filas) >= $max) {
            break;
        }
    }
    return $filas;
}

/** Valida los datos del formulario. Devuelve [datos, errores]. */
function validarFormulario(array $post): array
{
    $errores = [];
    $texto = trim((string) ($post['texto'] ?? ''));
    $accion = (string) ($post['accion'] ?? '');
    $clave = filter_var($post['clave'] ?? '', FILTER_VALIDATE_INT);

    if ($texto === '') {
        $errores[] = 'Escribe un texto.';
    }
    if (!in_array($accion, ACCIONES, true)) {
        $errores[] = 'Selecciona cifrar o descifrar.';
    }
    if ($clave === false) {
        $errores[] = 'La clave debe ser un número entero.';
    }
    return [['texto' => $texto, 'accion' => $accion, 'clave' => $clave === false ? 0 : $clave], $errores];
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}