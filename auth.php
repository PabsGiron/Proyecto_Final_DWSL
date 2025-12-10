<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if (!isset($_SESSION["id_usuario"])) {
    header("Location: Login.php");
    exit;
}

function esAdmin(): bool
{
    return isset($_SESSION["rol"]) && $_SESSION["rol"] === "admin";
}

function esVeterinario(): bool
{
    return isset($_SESSION["rol"]) && $_SESSION["rol"] === "veterinario";
}

function requireRol(string $rol): void
{
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== $rol) {
        header("Location: ../index.php");
        exit;
    }
}
?>