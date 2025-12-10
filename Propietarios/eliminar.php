<?php
require "../auth.php";
require "../conexion.php";

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($id > 0) {
    $stmt = $conexion->prepare("DELETE FROM propietarios WHERE id_propietario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: lista.php");
exit;
