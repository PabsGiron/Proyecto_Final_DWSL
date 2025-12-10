<?php
require "../auth.php"; require "../conexion.php";
if(isset($_GET['id'])) {
    $stmt = $conexion->prepare("DELETE FROM citas WHERE id_cita = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
}
header("Location: lista.php");
?>