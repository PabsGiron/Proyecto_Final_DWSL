<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../"; $pagina_actual = "consultas";
$esAdmin = esAdmin();
if (!($esAdmin || esVeterinario())) { header("Location: ../principal.php"); exit; }

date_default_timezone_set("America/El_Salvador");

$errores = []; $mensaje = ""; $tipoMensaje = "danger";

$id_consulta = intval($_GET["id"] ?? 0);
if ($id_consulta <= 0) { header("Location: lista.php"); exit; }

$stmtCons = $conexion->prepare("SELECT * FROM consultas WHERE id_consulta = ?");
$stmtCons->bind_param("i", $id_consulta);
$stmtCons->execute();
$consultaDB = $stmtCons->get_result()->fetch_assoc();
if (!$consultaDB) { header("Location: lista.php"); exit; }

$mascotas = $conexion->query("SELECT m.id_mascota, m.nombre AS mascota, p.nombre AS propietario FROM mascotas m INNER JOIN propietarios p ON m.id_propietario = p.id_propietario ORDER BY p.nombre");
$veterinarios = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol = 'veterinario' ORDER BY nombre_completo");

$id_mascota = $consultaDB["id_mascota"];
$id_veterinario = $consultaDB["id_veterinario"];
$fecha_consulta = ""; $hora_consulta = "";

if ($consultaDB["fecha_consulta"]) {
    $dt = new DateTime($consultaDB["fecha_consulta"]);
    $fecha_consulta = $dt->format("Y-m-d");
    $hora_consulta = $dt->format("H:i");
}

$peso_kg = $consultaDB["peso_kg"];
$tamano_cm = preg_replace('/[^0-9.]/', '', $consultaDB["tamano"]??"");
$obs_clinica = $consultaDB["observacion_clinica"];
$obs_sistema = $consultaDB["observacion_sistema"];
$diagnostico = $consultaDB["conclusion_diagnostico"];
$receta = $consultaDB["receta"];
$costo_servicio = $consultaDB["costo_servicio"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_mascota = intval($_POST["id_mascota"]??0);
    $id_veterinario = intval($_POST["id_veterinario"]??0);
    $fecha_consulta = $_POST["fecha_consulta"]??"";
    $hora_consulta = $_POST["hora_consulta"]??"";
    $peso_kg = trim($_POST["peso_kg"]??"");
    $tamano_cm = trim($_POST["tamano_cm"]??"");
    $obs_clinica = trim($_POST["observacion_clinica"]??"");
    $obs_sistema = trim($_POST["observacion_sistema"]??"");
    $diagnostico = trim($_POST["conclusion_diagnostico"]??"");
    $receta = trim($_POST["receta"]??"");
    $costo_servicio = trim($_POST["costo_servicio"]??"");

    if ($id_mascota <= 0) $errores["id_mascota"] = "Requerido.";
    if ($costo_servicio === "" || $costo_servicio < 0) $errores["costo_servicio"] = "Costo inválido.";
    if ($peso_kg !== "" && $peso_kg < 0) $errores["peso_kg"] = "No negativos.";
    if ($tamano_cm !== "" && $tamano_cm < 0) $errores["tamano_cm"] = "No negativos.";

    if ($fecha_consulta && $hora_consulta) {
        try {
            $fIngresada = new DateTime("$fecha_consulta $hora_consulta");
            $ahora = new DateTime("now");
            if ($fIngresada > $ahora) $errores["fecha_consulta"] = "No se permite fecha futura.";
        } catch (Exception $e) { $errores["fecha_consulta"] = "Fecha inválida."; }
    }

    if (empty($errores)) {
        $fechaHora = "$fecha_consulta $hora_consulta:00";
        $tamanoStr = $tamano_cm !== "" ? ($tamano_cm . " cm") : null;
        $pesoInsert = ($peso_kg === "") ? null : floatval($peso_kg);
        $costoInsert = floatval($costo_servicio);

        $sql = "UPDATE consultas SET id_mascota=?, id_veterinario=?, fecha_consulta=?, peso_kg=?, tamano=?, observacion_clinica=?, observacion_sistema=?, conclusion_diagnostico=?, receta=?, costo_servicio=? WHERE id_consulta=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iisdsssssdi", $id_mascota, $id_veterinario, $fechaHora, $pesoInsert, $tamanoStr, $obs_clinica, $obs_sistema, $diagnostico, $receta, $costoInsert, $id_consulta);

        if ($stmt->execute()) {
            $mensaje = "Actualizado."; $tipoMensaje = "success";
        } else { $mensaje = "Error SQL: ".$conexion->error; }
    } else { $mensaje = "Corrija errores."; }
    
    $mascotas = $conexion->query("SELECT m.id_mascota, m.nombre AS mascota, p.nombre AS propietario FROM mascotas m INNER JOIN propietarios p ON m.id_propietario = p.id_propietario ORDER BY p.nombre, m.nombre");
    $veterinarios = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol = 'veterinario' ORDER BY nombre_completo");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar consulta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Editar consulta</h4>
            <a href="lista.php" class="btn btn-outline-secondary btn-sm">Volver a la lista</a>
        </div>
        <div class="card-mini p-4">
            <?php if ($mensaje !== ""): ?>
                <div class="alert alert-<?= $tipoMensaje ?> py-2 mb-3"><?= $mensaje ?></div>
            <?php endif; ?>
            <form method="POST" id="formConsulta" novalidate>
                <div class="mb-3">
                    <label class="form-label">Mascota</label>
                    <select name="id_mascota" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php while($m=$mascotas->fetch_assoc()): ?>
                            <option value="<?=$m['id_mascota']?>" <?=($id_mascota==$m['id_mascota'])?'selected':''?>>
                                <?=htmlspecialchars($m['mascota'])?> (Dueño: <?=htmlspecialchars($m['propietario'])?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Veterinario</label>
                    <select name="id_veterinario" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php while($v=$veterinarios->fetch_assoc()): ?>
                            <option value="<?=$v['id_usuario']?>" <?=($id_veterinario==$v['id_usuario'])?'selected':''?>><?=htmlspecialchars($v['nombre_completo'])?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha_consulta" id="fecha_consulta" class="form-control <?= isset($errores['fecha_consulta'])?'is-invalid':'' ?>" 
                               required max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($fecha_consulta) ?>">
                        <div class="invalid-feedback">No futura.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hora</label>
                        <input type="time" name="hora_consulta" id="hora_consulta" class="form-control" required value="<?= htmlspecialchars($hora_consulta) ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Peso (kg)</label>
                        <input type="number" name="peso_kg" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($peso_kg) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tamaño (cm)</label>
                        <input type="number" name="tamano_cm" class="form-control" step="0.1" min="0" value="<?= htmlspecialchars($tamano_cm) ?>">
                    </div>
                </div>
                <div class="mb-3"><label class="form-label">Obs. Clínica</label><textarea name="observacion_clinica" class="form-control" rows="2"><?=htmlspecialchars($obs_clinica)?></textarea></div>
                <div class="mb-3"><label class="form-label">Obs. Sistemas</label><textarea name="observacion_sistema" class="form-control" rows="2"><?=htmlspecialchars($obs_sistema)?></textarea></div>
                <div class="mb-3"><label class="form-label">Diagnóstico</label><textarea name="conclusion_diagnostico" class="form-control" rows="2"><?=htmlspecialchars($diagnostico)?></textarea></div>
                <div class="mb-3"><label class="form-label">Receta</label><textarea name="receta" class="form-control" rows="2"><?=htmlspecialchars($receta)?></textarea></div>
                <div class="mb-3">
                    <label class="form-label">Costo ($)</label>
                    <input type="number" name="costo_servicio" class="form-control" required step="0.01" min="0" value="<?= htmlspecialchars($costo_servicio) ?>">
                </div>
                <button class="btn btn-main">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formConsulta");
    const f = document.getElementById("fecha_consulta"), h = document.getElementById("hora_consulta");
    
    function chkTime(){
        const hoy=new Date(); const sel=new Date(f.value+"T00:00:00");
        const hoyCero=new Date(); hoyCero.setHours(0,0,0,0);
        if(sel.getTime()===hoyCero.getTime()){
            h.max = hoy.getHours().toString().padStart(2,'0')+":"+hoy.getMinutes().toString().padStart(2,'0');
        } else { h.removeAttribute("max"); }
    }
    f.addEventListener("change", chkTime); chkTime();

    form.addEventListener("submit", function (e) {
        if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
        form.classList.add("was-validated");
    });
});
</script>
</body>
</html>