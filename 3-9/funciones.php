<?php
declare(strict_types=1);

/*
 * Estado de la calculadora guardado en $_SESSION['calc']:
 *   pantalla     → lo que se ve en el display (string)
 *   acumulador   → resultado parcial (float|null)
 *   operador     → operador pendiente: + - * / (string|null)
 *   nuevaEntrada → true si el siguiente dígito debe empezar un número nuevo
 *   memoria      → valor de la memoria M (float)
 *   error        → true tras un error (p. ej. dividir entre 0)
 */

const OPERADORES = ['+', '-', '*', '/'];
const SIMBOLOS   = ['+' => '+', '-' => '−', '*' => '×', '/' => '÷'];

function iniciarSesion(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
}

function estadoInicial(float $memoria = 0.0): array
{
    return [
        'pantalla'     => '0',
        'acumulador'   => null,
        'operador'     => null,
        'nuevaEntrada' => true,
        'memoria'      => $memoria,
        'error'        => false,
    ];
}

function formatear(float $n): string
{
    if (!is_finite($n)) {
        return 'Error';
    }
    $s = rtrim(rtrim(sprintf('%.10F', $n), '0'), '.');
    return $s === '-0' ? '0' : $s;
}

function aplicar(float $a, float $b, string $op): ?float
{
    return match ($op) {
        '+' => $a + $b,
        '-' => $a - $b,
        '*' => $a * $b,
        '/' => $b == 0.0 ? null : $a / $b,
    };
}

/** Procesa una tecla y devuelve el nuevo estado. */
function pulsar(array $s, string $tecla): array
{
    // Tras un error solo se admite C / CE o teclas de memoria que no dependan de la pantalla
    if ($s['error'] && !in_array($tecla, ['C', 'CE', 'MC'], true)) {
        return $s;
    }

    $valor = (float) $s['pantalla'];

    switch (true) {
        // ---- Dígitos ----
        case ctype_digit($tecla) && strlen($tecla) === 1:
            if ($s['nuevaEntrada'] || $s['pantalla'] === '0') {
                $s['pantalla'] = $tecla;
            } elseif (strlen($s['pantalla']) < 15) {
                $s['pantalla'] .= $tecla;
            }
            $s['nuevaEntrada'] = false;
            break;

        case $tecla === '.':
            if ($s['nuevaEntrada']) {
                $s['pantalla'] = '0.';
            } elseif (!str_contains($s['pantalla'], '.')) {
                $s['pantalla'] .= '.';
            }
            $s['nuevaEntrada'] = false;
            break;

        case $tecla === '±':
            if ($s['pantalla'] !== '0') {
                $s['pantalla'] = str_starts_with($s['pantalla'], '-')
                    ? substr($s['pantalla'], 1)
                    : '-' . $s['pantalla'];
            }
            break;

        // ---- Operadores: permiten cálculos encadenados (2 + 3 × 4 → 5 × 4) ----
        case in_array($tecla, OPERADORES, true):
            if ($s['operador'] !== null && !$s['nuevaEntrada']) {
                $s = resolver($s, $valor);
                if ($s['error']) {
                    return $s;
                }
            } elseif ($s['operador'] === null) {
                $s['acumulador'] = $valor;
            }
            $s['operador']     = $tecla;   // si se pulsan dos seguidos, se sustituye
            $s['nuevaEntrada'] = true;
            break;

        case $tecla === '=':
            if ($s['operador'] !== null) {
                $s = resolver($s, $valor);
                $s['operador']   = null;
                $s['acumulador'] = null;
            }
            $s['nuevaEntrada'] = true;
            break;

        // ---- Borrado ----
        case $tecla === 'C':   // todo menos la memoria
            $s = estadoInicial($s['memoria']);
            break;

        case $tecla === 'CE':  // solo la entrada actual
            $s['pantalla']     = '0';
            $s['nuevaEntrada'] = true;
            $s['error']        = false;
            break;

        // ---- Memoria ----
        case $tecla === 'M+':
            $s['memoria']     += $valor;
            $s['nuevaEntrada'] = true;
            break;

        case $tecla === 'M-':
            $s['memoria']     -= $valor;
            $s['nuevaEntrada'] = true;
            break;

        case $tecla === 'MR':
            $s['pantalla']     = formatear($s['memoria']);
            $s['nuevaEntrada'] = true;
            break;

        case $tecla === 'MC':
            $s['memoria'] = 0.0;
            break;
    }

    return $s;
}

/** Aplica el operador pendiente entre el acumulador y el valor actual. */
function resolver(array $s, float $valor): array
{
    $r = aplicar((float) $s['acumulador'], $valor, $s['operador']);
    if ($r === null || !is_finite($r)) {
        $s['pantalla'] = 'Error: división entre 0';
        $s['error']    = true;
        $s['operador'] = null;
        $s['acumulador'] = null;
        return $s;
    }
    $s['acumulador'] = $r;
    $s['pantalla']   = formatear($r);
    return $s;
}

/** Texto de la operación en curso, p. ej. "12 ×". */
function expresion(array $s): string
{
    if ($s['operador'] === null || $s['acumulador'] === null) {
        return '';
    }
    return formatear($s['acumulador']) . ' ' . SIMBOLOS[$s['operador']];
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
