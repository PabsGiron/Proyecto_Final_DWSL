<?php
require "../auth.php";
require "../conexion.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_GET["id_especie"])) {
    echo json_encode([]);
    exit;
}

$id_especie = intval($_GET["id_especie"]);

$sql = "SELECT id_raza, nombre FROM razas WHERE id_especie = ? ORDER BY nombre";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_especie);
$stmt->execute();

$result = $stmt->get_result();
$razas = [];

while ($row = $result->fetch_assoc()) {
    $razas[] = $row;
}

echo json_encode($razas);
