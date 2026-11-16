<?php

require_once('settings.php');

function createXML(): void
{
    if (!file_exists(FICHERO_XML)) {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $doc->appendChild($doc->createElement('biblioteca'));
        $doc->save(FICHERO_XML);
    }
}

function validateInput($post): array
{
    $d = [
        'autor' => trim((string) ($post['autor'] ?? '')),
        'isbn' => trim((string) ($post['isbn'] ?? '')),
        'title' => trim((string) ($post['title'] ?? '')),
        'year' => trim((string) ($post['year'] ?? '')),
    ];
    $errores = [];

    if (mb_strlen($d['autor']) < MIN_NOMBRE || mb_strlen($d['autor']) > MAX_NOMBRE) {
        $errores[] = 'El nombre es obligatorio (entre ' . MIN_NOMBRE . ' y ' . MAX_NOMBRE . ' caracteres).';
    } else if (mb_strlen($d['title']) < MIN_TITULO || mb_strlen($d['title']) > MAX_TITULO) {
        $errores[] = 'El titulo es obligatorio (entre ' . MIN_TITULO . ' y ' . MAX_TITULO . ' caracteres).';
    } else if (mb_strlen($d['isbn']) !== ISBN_LENGTH) {
        $errores[] = 'La longitud de isbn debe ser de ' . ISBN_LENGTH . ' caracteres';
    } else if ($d['year'] === '' || !ctype_digit($d['year'])) {
        $errores[] = 'El año es obligatorio y debe ser un número.';
    } else if ((int) $d['year'] > (int) date('Y')) {
        $errores[] = 'El libro viene del futuro???';
    }

    if (!empty($errores)) {
        return $errores;
    }

    return $d;
}

function saveInput(array $d, string $file = FICHERO_XML): void
{
    if (file_exists($file)) {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false; // para que la sangría quede bien al guardar
        $doc->formatOutput = true;
        $doc->load($file);
        $raiz = $doc->getElementsByTagName('biblioteca')->item(0);
        $libro = $doc->createElement('libro');
        $libro->setAttribute('isbn', $d['isbn']);
        $libro->setAttribute('autor', $d['autor']);
        $libro->setAttribute('titulo', $d['title']);
        $libro->setAttribute('año', $d['year']);

        $raiz->appendChild($libro);

        $doc->save($file);
    }
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}