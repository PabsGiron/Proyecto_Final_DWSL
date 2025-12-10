<?php
require "../auth.php"; require "../conexion.php";
$ruta = "../"; $pagina_actual = "citas";
date_default_timezone_set("America/El_Salvador");

$id = $_GET["id"]??0;
$stmt = $conexion->prepare("SELECT * FROM citas WHERE id_cita=?");
$stmt->bind_param("i", $id); $stmt->execute();
$cita = $stmt->get_result()->fetch_assoc();
if(!$cita) header("Location: lista.php");

$veterinarios = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol='veterinario'");

if ($_SERVER["REQUEST_METHOD"]==="POST") {
    $fecha = $_POST["fecha"]; $hora = $_POST["hora"]; 
    $motivo = $_POST["motivo"]; $estado = $_POST["estado"]; $id_vet = $_POST["id_veterinario"];
    
    $stmtUpd = $conexion->prepare("UPDATE citas SET fecha_cita=?, hora_cita=?, motivo=?, estado=?, id_veterinario=? WHERE id_cita=?");
    $stmtUpd->bind_param("ssssii", $fecha, $hora, $motivo, $estado, $id_vet, $id);
    if($stmtUpd->execute()) header("Location: lista.php");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Editar Cita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <h4>Editar Cita</h4>
        <div class="card-mini p-4">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-6"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?=$cita['fecha_cita']?>" required></div>
                    <div class="col-6"><label>Hora</label><input type="time" name="hora" class="form-control" value="<?=$cita['hora_cita']?>" required></div>
                </div>
                <div class="mb-3">
                    <label>Veterinario</label>
                    <select name="id_veterinario" class="form-select" required>
                        <?php while($v=$veterinarios->fetch_assoc()): ?>
                            <option value="<?=$v['id_usuario']?>" <?=($cita['id_veterinario']==$v['id_usuario'])?'selected':''?>><?=$v['nombre_completo']?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3"><label>Motivo</label><input type="text" name="motivo" class="form-control" value="<?=htmlspecialchars($cita['motivo'])?>" required></div>
                <div class="mb-3">
                    <label>Estado</label>
                    <select name="estado" class="form-select">
                        <option value="Pendiente" <?=($cita['estado']=='Pendiente')?'selected':''?>>Pendiente</option>
                        <option value="Realizada" <?=($cita['estado']=='Realizada')?'selected':''?>>Realizada</option>
                        <option value="Cancelada" <?=($cita['estado']=='Cancelada')?'selected':''?>>Cancelada</option>
                    </select>
                </div>
                <button class="btn btn-main">Actualizar</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>