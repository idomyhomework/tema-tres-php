<?php
declare(strict_types=1);

/*
 * Simulamos un navegador sin cookies: PHP NO enviará la cookie de sesión
 * y aceptará el identificador que llegue por GET o POST.
 * Estas directivas deben establecerse ANTES de session_start().
 */
ini_set('session.use_cookies', '0');       // no usar cookies para el ID de sesión
ini_set('session.use_only_cookies', '0');  // permitir el ID en la URL / formulario
ini_set('session.use_trans_sid', '0');     // propagamos el SID a mano, no de forma automática
ini_set('session.use_strict_mode', '1');   // un ID inventado en la URL no crea sesión con ese ID

session_start();

/*
 * SID vale "PHPSESSID=abc123..." cuando no se usan cookies, así que
 * se puede pegar directamente en los enlaces: <a href="paso2.php?<?= SID ?>">
 */

/** Campo oculto con el ID de sesión, para formularios POST. */
function campoSesion(): string
{
    return '<input type="hidden" name="' . e(session_name()) . '" value="' . e(session_id()) . '">';
}

/** Cuenta la visita a la página y guarda el ID visto en ella. */
function registrarVisita(string $pagina): void
{
    $_SESSION['visitas_total'] = ($_SESSION['visitas_total'] ?? 0) + 1;
    $_SESSION['visitas'][$pagina] = ($_SESSION['visitas'][$pagina] ?? 0) + 1;
    $_SESSION['ids'][$pagina] = session_id();
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
