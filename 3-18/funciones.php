<?php

require_once "settings.php";

/** Valida el POST. Devuelve [datos limpios, errores]. */
function validarFormulario(array $post): array
{
    $d = [
        'nombre' => trim((string) ($post['nombre'] ?? '')),
        'email' => (string) ($post['email'] ?? ''),
        'valoracion' => trim((string) ($post['valoracion'] ?? '')),
        'comentario' => trim((string) ($post['comentario'] ?? '')),
    ];
    $errores = [];

    if (!isset($d['nombre']) || mb_strlen($d['nombre']) > MAX_NOMBRE || mb_strlen($d['nombre']) < MIN_NOMBRE) {
        $errores[] = 'El nombre es obligatorio (entre ' . MIN_NOMBRE . ' y ' . MAX_NOMBRE . ' caracteres).';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Introduce un email valido.';
    }
    $valoracion = filter_var(str_replace(',', '.', $d['valoracion']), FILTER_VALIDATE_FLOAT);
    if ($valoracion === false || $valoracion < MIN_VALORACION || $valoracion > MAX_VALORACION) {
        $errores[] = 'La valoracion debe estar entre ' . MIN_VALORACION . ' y ' . MAX_VALORACION . '.';
    }

    if (empty($errores)) {
        return $d;
    } else {
        return $errores;
    }
}

function guardarRespuesta(array $productos): void
{
    $json = json_encode($productos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(FICHERO_JSON, $json, LOCK_EX);
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

