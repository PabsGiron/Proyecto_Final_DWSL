<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../"; $pagina_actual = "consultas";
$esAdmin = esAdmin();
if (!($esAdmin || esVeterinario())) { header("Location: ../principal.php"); exit; }

date_default_timezone_set("America/El_Salvador");

$errores = []; $mensaje = ""; $tipoMensaje = "danger";

$mascotas = $conexion->query("SELECT m.id_mascota, m.nombre AS mascota, p.nombre AS propietario FROM mascotas m INNER JOIN propietarios p ON m.id_propietario = p.id_propietario ORDER BY p.nombre, m.nombre");
$sinMascotas = ($mascotas->num_rows === 0);
$veterinarios = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol = 'veterinario' ORDER BY nombre_completo");
$sinVets = ($veterinarios->num_rows === 0);

$ahoraObj = new DateTime("now");
$id_mascota=""; $id_veterinario=$_SESSION["id_usuario"]??""; 
$fecha_consulta=$ahoraObj->format("Y-m-d"); 
$hora_consulta=$ahoraObj->format("H:i");
$peso_kg=""; $tamano_cm=""; $obs_clinica=""; $obs_sistema=""; $diagnostico=""; $receta=""; $costo_servicio="";

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

    if ($id_mascota <= 0) $errores["id_mascota"] = "Seleccione una mascota.";
    if ($id_veterinario <= 0) $errores["id_veterinario"] = "Seleccione un veterinario.";
    if ($costo_servicio === "" || !is_numeric($costo_servicio) || $costo_servicio < 0) 
        $errores["costo_servicio"] = "Costo inválido.";
    
    if ($peso_kg !== "" && (!is_numeric($peso_kg) || $peso_kg < 0)) 
        $errores["peso_kg"] = "Peso inválido.";
        
    if ($tamano_cm !== "" && (!is_numeric($tamano_cm) || $tamano_cm < 0)) 
        $errores["tamano_cm"] = "Tamaño inválido.";

    if ($fecha_consulta && $hora_consulta) {
        try {
            $fechaHoraIngresada = new DateTime("$fecha_consulta $hora_consulta");
            $ahoraMismo = new DateTime("now");
            
            if ($fechaHoraIngresada > $ahoraMismo) {
                $errores["fecha_consulta"] = "No se pueden registrar consultas en el futuro.";
                $errores["hora_consulta"] = "Verifique la hora.";
            }
        } catch (Exception $e) { $errores["fecha_consulta"] = "Fecha inválida."; }
    }

    if (empty($errores)) {
        $fechaHora = "$fecha_consulta $hora_consulta:00";
        $tamanoStr = $tamano_cm !== "" ? ($tamano_cm . " cm") : null;
        $pesoInsert = ($peso_kg === "") ? null : floatval($peso_kg);
        $costoInsert = floatval($costo_servicio);

        $stmt = $conexion->prepare("INSERT INTO consultas (id_mascota, id_veterinario, fecha_consulta, peso_kg, tamano, observacion_clinica, observacion_sistema, conclusion_diagnostico, receta, costo_servicio) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iisdsssssd", $id_mascota, $id_veterinario, $fechaHora, $pesoInsert, $tamanoStr, $obs_clinica, $obs_sistema, $diagnostico, $receta, $costoInsert);

        if ($stmt->execute()) {
            $mensaje="Consulta registrada."; $tipoMensaje="success";
            $id_mascota=""; $peso_kg=""; $tamano_cm=""; $obs_clinica=""; $obs_sistema=""; $diagnostico=""; $receta=""; $costo_servicio="";
            $now = new DateTime("now");
            $fecha_consulta=$now->format("Y-m-d"); $hora_consulta=$now->format("H:i");
        } else { $mensaje="Error SQL: ".$conexion->error; }
        
        $mascotas = $conexion->query("SELECT m.id_mascota, m.nombre AS mascota, p.nombre AS propietario FROM mascotas m INNER JOIN propietarios p ON m.id_propietario = p.id_propietario ORDER BY p.nombre, m.nombre");
        $veterinarios = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol = 'veterinario' ORDER BY nombre_completo");
    } else {
        $mensaje = "Revise los errores.";
        $mascotas = $conexion->query("SELECT m.id_mascota, m.nombre AS mascota, p.nombre AS propietario FROM mascotas m INNER JOIN propietarios p ON m.id_propietario = p.id_propietario ORDER BY p.nombre, m.nombre");
        $veterinarios = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol = 'veterinario' ORDER BY nombre_completo");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar consulta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Registrar nueva consulta</h4>
            <a href="lista.php" class="btn btn-outline-secondary btn-sm">Volver a la lista</a>
        </div>
        <div class="card-mini p-4">
            <?php if ($mensaje !== ""): ?>
                <div class="alert alert-<?= $tipoMensaje ?> py-2 mb-3"><?= $mensaje ?></div>
            <?php endif; ?>

            <?php if ($sinMascotas || $sinVets): ?>
                <div class="alert alert-warning">Faltan datos (mascotas o veterinarios) para crear consultas.</div>
            <?php else: ?>
                <form method="POST" id="formConsulta" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Mascota</label>
                        <select name="id_mascota" class="form-select <?= isset($errores['id_mascota'])?'is-invalid':'' ?>" required>
                            <option value="">Seleccione...</option>
                            <?php while($m=$mascotas->fetch_assoc()): ?>
                                <option value="<?=$m['id_mascota']?>" <?=($id_mascota==$m['id_mascota'])?'selected':''?>>
                                    <?=htmlspecialchars($m['mascota'])?> (Dueño: <?=htmlspecialchars($m['propietario'])?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione una mascota.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Veterinario</label>
                        <select name="id_veterinario" class="form-select <?= isset($errores['id_veterinario'])?'is-invalid':'' ?>" required>
                            <option value="">Seleccione...</option>
                            <?php while($v=$veterinarios->fetch_assoc()): ?>
                                <option value="<?=$v['id_usuario']?>" <?=($id_veterinario==$v['id_usuario'])?'selected':''?>>
                                    <?=htmlspecialchars($v['nombre_completo'])?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione un veterinario.</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha_consulta" id="fecha_consulta" 
                                   class="form-control <?= isset($errores['fecha_consulta'])?'is-invalid':'' ?>"
                                   required max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($fecha_consulta) ?>">
                            <div class="invalid-feedback">No puede ser futura.</div>
                            <?php if(isset($errores["fecha_consulta"])):?><div class="invalid-feedback d-block"><?=$errores["fecha_consulta"]?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora</label>
                            <input type="time" name="hora_consulta" id="hora_consulta" 
                                   class="form-control <?= isset($errores['hora_consulta'])?'is-invalid':'' ?>"
                                   required value="<?= htmlspecialchars($hora_consulta) ?>">
                            <div class="invalid-feedback">Hora inválida.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" name="peso_kg" class="form-control <?= isset($errores['peso_kg'])?'is-invalid':'' ?>" 
                                   step="0.01" min="0" value="<?= htmlspecialchars($peso_kg) ?>">
                            <div class="invalid-feedback">No puede ser negativo.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tamaño (cm)</label>
                            <input type="number" name="tamano_cm" class="form-control <?= isset($errores['tamano_cm'])?'is-invalid':'' ?>" 
                                   step="0.1" min="0" value="<?= htmlspecialchars($tamano_cm) ?>">
                            <div class="invalid-feedback">No puede ser negativo.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observación clínica</label>
                        <textarea name="observacion_clinica" class="form-control" rows="2"><?= htmlspecialchars($obs_clinica) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observación por sistemas</label>
                        <textarea name="observacion_sistema" class="form-control" rows="2"><?= htmlspecialchars($obs_sistema) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Conclusión / diagnóstico</label>
                        <textarea name="conclusion_diagnostico" class="form-control" rows="2"><?= htmlspecialchars($diagnostico) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Receta</label>
                        <textarea name="receta" class="form-control" rows="2"><?= htmlspecialchars($receta) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Costo ($)</label>
                        <input type="number" name="costo_servicio" class="form-control <?= isset($errores['costo_servicio'])?'is-invalid':'' ?>" 
                               required step="0.01" min="0" value="<?= htmlspecialchars($costo_servicio) ?>">
                        <div class="invalid-feedback">Requerido. No puede ser negativo.</div>
                    </div>

                    <button class="btn btn-main">Guardar consulta</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formConsulta");
    const fechaEl = document.getElementById("fecha_consulta");
    const horaEl = document.getElementById("hora_consulta");

    function validarHora() {
        const hoy = new Date();
        const fechaSelec = new Date(fechaEl.value + "T00:00:00");
        
        const hoySinHora = new Date(); hoySinHora.setHours(0,0,0,0);
        
        if (fechaSelec.getTime() === hoySinHora.getTime()) {
            const horaActual = hoy.getHours().toString().padStart(2,'0') + ":" + hoy.getMinutes().toString().padStart(2,'0');
            horaEl.max = horaActual; 
        } else {
            horaEl.removeAttribute("max");
        }
    }

    fechaEl.addEventListener("change", validarHora);
    validarHora(); 

    form.addEventListener("submit", function (e) {
        if (!form.checkValidity()) {
            e.preventDefault(); e.stopPropagation();
        }
        form.classList.add("was-validated");
    });
});
</script>
</body>
</html>