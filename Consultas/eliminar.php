<?php
require "../auth.php";
require "../conexion.php";

$id_usuario = $_SESSION["id_usuario"];
$esAdmin    = esAdmin();

if (!isset($_GET["id"])) {
    header("Location: lista.php");
    exit;
}

$id_consulta = intval($_GET["id"]);

$sql = "SELECT id_veterinario FROM consultas WHERE id_consulta = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_consulta);
$stmt->execute();
$result = $stmt->get_result();
$consulta = $result->fetch_assoc();

if (!$consulta) {
    header("Location: lista.php");
    exit;
}

if (!$esAdmin && $consulta["id_veterinario"] != $id_usuario) {
    header("Location: lista.php");
    exit;
}

$sqlDel = "DELETE FROM consultas WHERE id_consulta = ?";
$stmtDel = $conexion->prepare($sqlDel);
$stmtDel->bind_param("i", $id_consulta);
$stmtDel->execute();

header("Location: lista.php");
exit;
