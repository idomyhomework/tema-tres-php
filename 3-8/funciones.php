<?php
declare(strict_types=1);

const COOKIE_HISTORIAL = 'historial_calc';
const MAX_HISTORIAL = 5;
const OPERACIONES = [
    'sumar' => '+',
    'restar' => '−',
    'multiplicar' => '×',
    'dividir' => '÷',
];

/** Realiza la operación. Lanza excepción si se divide entre cero. */
function calcular(float $a, float $b, string $op): float
{
    return match ($op) {
        'sumar' => $a + $b,
        'restar' => $a - $b,
        'multiplicar' => $a * $b,
        'dividir' => ($b == 0.0) || ($a == 0.0)
            ? throw new DivisionByZeroError('No se puede dividir entre cero.')
            : $a / $b,
        default => throw new InvalidArgumentException('Operación no válida.'),
    };
}

/** Formatea un número sin ceros sobrantes (2.50 → 2.5, 3.0 → 3). */
function formatear(float $n): string
{
    $s = rtrim(rtrim(number_format($n, 6, '.', ''), '0'), '.');
    return $s === '-0' ? '0' : $s;
}

/** Lee el historial de la cookie (JSON). Si está corrupta o manipulada devuelve []. */
function leerHistorial(): array
{
    $datos = json_decode($_COOKIE[COOKIE_HISTORIAL] ?? '[]', true);
    if (!is_array($datos)) {
        return [];
    }
    // Solo aceptamos cadenas: el contenido de una cookie lo controla el cliente
    $datos = array_values(array_filter($datos, 'is_string'));
    return array_slice($datos, 0, MAX_HISTORIAL);
}

/** Opciones seguras comunes a setcookie(). */
function opcionesCookie(int $expira): array
{
    return [
        'expires' => $expira,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

/** Añade una operación al principio y guarda solo las 5 últimas. */
function guardarEnHistorial(array $historial, string $operacion): array
{
    array_unshift($historial, $operacion);
    $historial = array_slice($historial, 0, MAX_HISTORIAL);

    setcookie(COOKIE_HISTORIAL, json_encode($historial, JSON_UNESCAPED_UNICODE), opcionesCookie(time() + 30 * 24 * 3600));
    $_COOKIE[COOKIE_HISTORIAL] = json_encode($historial); // disponible ya en esta petición
    return $historial;
}

/** Borra la cookie poniendo una fecha de caducidad pasada. */
function borrarHistorial(): void
{
    setcookie(COOKIE_HISTORIAL, '', opcionesCookie(time() - 3600));
    unset($_COOKIE[COOKIE_HISTORIAL]);
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
