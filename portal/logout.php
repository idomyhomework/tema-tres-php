<?php
    session_start();
    $_SESSION = [];
    session_destroy();
    //setcookie('recordar_nombre', '', time() - 3600, '/');  // solo si usas la cookie del punto 2
    header('Location: login.php');
    exit;