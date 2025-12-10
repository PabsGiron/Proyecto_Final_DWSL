<?php
require "../auth.php";
require "../conexion.php";

date_default_timezone_set("America/El_Salvador");

$nueva_especie        = trim($_POST["nueva_especie"] ?? "");
$id_especie_existente = intval($_POST["id_especie_existente"] ?? 0);
$nombre_raza          = trim($_POST["nombre_raza"] ?? "");

$redirect = $_SERVER['HTTP_REFERER'] ?? 'crear.php';

if ($nombre_raza === "") { header("Location: $redirect"); exit; }

if ($nueva_especie !== "" && !preg_match('/^[\p{L}\s]+$/u', $nueva_especie)) { header("Location: $redirect"); exit; }
if (!preg_match('/^[\p{L}\s]+$/u', $nombre_raza)) { header("Location: $redirect"); exit; }

if (($nueva_especie === "" && $id_especie_existente <= 0) || ($nueva_especie !== "" && $id_especie_existente > 0)) { header("Location: $redirect"); exit; }

$id_especie = 0;

if ($nueva_especie !== "") {
    $stmtCheck = $conexion->prepare("SELECT id_especie FROM especies WHERE nombre = ?");
    $stmtCheck->bind_param("s", $nueva_especie);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result()->fetch_assoc();

    if ($resCheck) {
        $id_especie = $resCheck["id_especie"];
    } else {
        $stmt = $conexion->prepare("INSERT INTO especies (nombre) VALUES (?)");
        $stmt->bind_param("s", $nueva_especie);
        if ($stmt->execute()) {
            $id_especie = $stmt->insert_id;
        }
    }
} else {
    $id_especie = $id_especie_existente;
}

if ($id_especie > 0) {
    $stmtR = $conexion->prepare("INSERT INTO razas (id_especie, nombre) VALUES (?, ?)");
    $stmtR->bind_param("is", $id_especie, $nombre_raza);
    $stmtR->execute();
}

header("Location: $redirect");
exit;