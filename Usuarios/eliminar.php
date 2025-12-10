<?php
require "../auth.php";
require "../conexion.php";

if (!esAdmin()) {
    header("Location: ../principal.php");
    exit;
}

const ID_SUPERADMIN = 1;

if (!isset($_GET["id"])) {
    header("Location: lista.php");
    exit;
}

$id_eliminar = intval($_GET["id"]);
$mi_id       = $_SESSION["id_usuario"];

if ($id_eliminar == $mi_id) {
    header("Location: lista.php");
    exit;
}

if ($id_eliminar == ID_SUPERADMIN) {
    header("Location: lista.php");
    exit;
}

$sql = "DELETE FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_eliminar);
$stmt->execute();

header("Location: lista.php");
exit;