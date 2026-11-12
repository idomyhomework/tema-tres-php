<?php
function conectar(): PDO {
    static $pdo = null;          // una sola conexión por petición
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=portal;charset=utf8mb4',
            'portal_app',
            'idomyhomeworkGANG-',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // los errores lanzan excepción
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // filas como arrays
            ]
        );
    }
    return $pdo;
}
