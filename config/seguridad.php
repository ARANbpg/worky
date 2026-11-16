<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function exigirRol($rolPermitido) {

    if (!isset($_SESSION["usuario_id"])) {
        header("Location: ../login.php");
        exit;
    }

    if ($_SESSION["rol"] !== $rolPermitido) {
        http_response_code(403);
        exit("Acceso denegado. No tienes permiso.");
    }
}