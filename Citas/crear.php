<?php
require "../auth.php";
require "../conexion.php";
$ruta = "../"; $pagina_actual = "citas";
date_default_timezone_set("America/El_Salvador");

$mascotas = $conexion->query("SELECT m.id_mascota, m.nombre AS mascota, p.nombre AS propietario FROM mascotas m INNER JOIN propietarios p ON m.id_propietario = p.id_propietario ORDER BY p.nombre");
$veterinarios = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol='veterinario'");

$errores=[]; $mensaje=""; 
$id_mascota=""; $id_veterinario=$_SESSION["id_usuario"]; $fecha=date("Y-m-d"); $hora=""; $motivo="";

if ($_SERVER["REQUEST_METHOD"]==="POST") {
    $id_mascota = $_POST["id_mascota"];
    $id_veterinario = $_POST["id_veterinario"];
    $fecha = $_POST["fecha"];
    $hora = $_POST["hora"];
    $motivo = trim($_POST["motivo"]);

    if (empty($motivo)) $errores["motivo"]="Motivo requerido";
    
    $citaTime = new DateTime("$fecha $hora");
    $ahora = new DateTime("now");
    if ($citaTime < $ahora) $errores["fecha"]="La cita no puede ser en el pasado.";

    if (empty($errores)) {
        $stmt = $conexion->prepare("INSERT INTO citas (id_mascota, id_veterinario, fecha_cita, hora_cita, motivo) VALUES (?,?,?,?,?)");
        $stmt->bind_param("iisss", $id_mascota, $id_veterinario, $fecha, $hora, $motivo);
        if ($stmt->execute()) {
            $mensaje="Cita agendada."; 
            $motivo=""; $hora=""; 
        } else $mensaje="Error SQL";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Nueva Cita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between mb-3">
            <h4 class="fw-bold">Agendar Cita</h4>
            <a href="lista.php" class="btn btn-outline-secondary btn-sm">Volver</a>
        </div>
        <div class="card-mini p-4">
            <?php if($mensaje): ?><div class="alert alert-success"><?=$mensaje?></div><?php endif; ?>
            <?php if($errores): ?><div class="alert alert-danger"><?=implode("<br>",$errores)?></div><?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label>Mascota</label>
                    <select name="id_mascota" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php while($m=$mascotas->fetch_assoc()): ?>
                            <option value="<?=$m['id_mascota']?>" <?=($id_mascota==$m['id_mascota'])?'selected':''?>><?=$m['mascota']?> (<?=$m['propietario']?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Veterinario</label>
                    <select name="id_veterinario" class="form-select" required>
                        <?php while($v=$veterinarios->fetch_assoc()): ?>
                            <option value="<?=$v['id_usuario']?>" <?=($id_veterinario==$v['id_usuario'])?'selected':''?>><?=$v['nombre_completo']?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" required min="<?=date('Y-m-d')?>" value="<?=$fecha?>">
                    </div>
                    <div class="col-6">
                        <label>Hora</label>
                        <input type="time" name="hora" class="form-control" required value="<?=$hora?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Motivo</label>
                    <input type="text" name="motivo" class="form-control" required value="<?=$motivo?>">
                </div>
                <button class="btn btn-main">Guardar Cita</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>