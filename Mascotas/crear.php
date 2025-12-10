<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../"; $pagina_actual = "mascotas";
$esAdmin = esAdmin();
if (!($esAdmin || esVeterinario())) { header("Location: ../principal.php"); exit; }

date_default_timezone_set("America/El_Salvador");

$errores = []; $mensaje = ""; $tipoMensaje = "danger";

$propietarios = $conexion->query("SELECT id_propietario, nombre FROM propietarios ORDER BY nombre");
$sinPropietarios = ($propietarios->num_rows === 0);
$especies = $conexion->query("SELECT id_especie, nombre FROM especies ORDER BY nombre");
$especiesModal = $conexion->query("SELECT id_especie, nombre FROM especies ORDER BY nombre");

$nombre_mascota = ""; $id_propietario = ""; $sexo = "Macho"; $color = ""; $fecha_nac = ""; $id_especie = ""; $id_raza = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_mascota = trim($_POST["nombre"] ?? "");
    $id_propietario = intval($_POST["id_propietario"] ?? 0);
    $sexo = $_POST["sexo"] ?? "Macho";
    $color = trim($_POST["color"] ?? "");
    $fecha_nac = $_POST["fecha_nacimiento"] ?? "";
    $id_especie = intval($_POST["id_especie"] ?? 0);
    $id_raza = intval($_POST["id_raza"] ?? 0);

    if ($nombre_mascota === "" || !preg_match('/^[\p{L}\s]+$/u', $nombre_mascota)) $errores["nombre"] = "Nombre inválido (solo letras).";
    if ($color === "" || !preg_match('/^[\p{L}\s]+$/u', $color)) $errores["color"] = "Color inválido (solo letras).";
    if ($id_propietario <= 0) $errores["propietario"] = "Seleccione dueño.";
    if ($id_especie <= 0) $errores["especie"] = "Seleccione especie.";
    if ($id_raza <= 0) $errores["raza"] = "Seleccione raza.";

    if ($fecha_nac !== "") {
        try {
            $fechaObj = new DateTime($fecha_nac);
            $hoy = new DateTime("today");
            if ($fechaObj > $hoy) {
                $errores["fecha_nacimiento"] = "La fecha no puede ser futura.";
            }
        } catch (Exception $e) {
            $errores["fecha_nacimiento"] = "Fecha inválida.";
        }
    }

    if (empty($errores)) {
        $nEspecie=""; $nRaza="";
        $rE=$conexion->query("SELECT nombre FROM especies WHERE id_especie=$id_especie")->fetch_assoc();
        if($rE)$nEspecie=$rE['nombre'];
        $rR=$conexion->query("SELECT nombre FROM razas WHERE id_raza=$id_raza")->fetch_assoc();
        if($rR)$nRaza=$rR['nombre'];

        $stmt=$conexion->prepare("INSERT INTO mascotas (nombre, especie, raza, sexo, color, fecha_nacimiento, id_propietario) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssi", $nombre_mascota, $nEspecie, $nRaza, $sexo, $color, $fecha_nac, $id_propietario);
        if($stmt->execute()){
            $mensaje="Mascota registrada."; $tipoMensaje="success";
            $nombre_mascota=""; $color=""; $fecha_nac=""; $id_especie=""; $id_raza="";
        } else { $mensaje="Error SQL"; }
        
        $propietarios = $conexion->query("SELECT id_propietario, nombre FROM propietarios ORDER BY nombre");
        $especies = $conexion->query("SELECT id_especie, nombre FROM especies ORDER BY nombre");
        $especiesModal = $conexion->query("SELECT id_especie, nombre FROM especies ORDER BY nombre");
    } else {
        $mensaje = "Revise los campos en rojo.";
        $propietarios = $conexion->query("SELECT id_propietario, nombre FROM propietarios ORDER BY nombre");
        $especies = $conexion->query("SELECT id_especie, nombre FROM especies ORDER BY nombre");
        $especiesModal = $conexion->query("SELECT id_especie, nombre FROM especies ORDER BY nombre");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar mascota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Registrar nueva mascota</h4>
            <a href="lista.php" class="btn btn-outline-secondary btn-sm">Volver</a>
        </div>
        <div class="card-mini p-4">
            <?php if ($mensaje !== ""): ?>
                <div class="alert alert-<?= $tipoMensaje ?> py-2 mb-3"><?= $mensaje ?></div>
            <?php endif; ?>

            <?php if ($sinPropietarios): ?>
                <div class="alert alert-warning">No hay dueños registrados.</div>
            <?php else: ?>
                <form method="POST" id="formMascota" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                               required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" 
                               value="<?= htmlspecialchars($nombre_mascota) ?>">
                        <div class="invalid-feedback">Solo letras y espacios.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Propietario</label>
                        <select name="id_propietario" class="form-select <?= isset($errores['propietario']) ? 'is-invalid' : '' ?>" required>
                            <option value="">Seleccione...</option>
                            <?php while($p=$propietarios->fetch_assoc()): ?>
                                <option value="<?=$p['id_propietario']?>" <?=($id_propietario==$p['id_propietario'])?'selected':''?>>
                                    <?=htmlspecialchars($p['nombre'])?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione un dueño.</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Especie y Raza</label>
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalEspecieRaza">+ Agregar</button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select name="id_especie" id="selectEspecie" class="form-select <?= isset($errores['especie']) ? 'is-invalid' : '' ?>" required>
                                    <option value="">Especie...</option>
                                    <?php while($e=$especies->fetch_assoc()): ?>
                                        <option value="<?=$e['id_especie']?>" <?=($id_especie==$e['id_especie'])?'selected':''?>><?=htmlspecialchars($e['nombre'])?></option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="invalid-feedback">Requerido.</div>
                            </div>
                            <div class="col-md-6">
                                <select name="id_raza" id="selectRaza" class="form-select <?= isset($errores['raza']) ? 'is-invalid' : '' ?>" required>
                                    <option value="">Raza...</option>
                                </select>
                                <div class="invalid-feedback">Requerido.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Sexo</label>
                            <select name="sexo" class="form-select" required>
                                <option value="Macho" <?= $sexo === "Macho" ? "selected" : "" ?>>Macho</option>
                                <option value="Hembra" <?= $sexo === "Hembra" ? "selected" : "" ?>>Hembra</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" name="color" class="form-control <?= isset($errores['color']) ? 'is-invalid' : '' ?>"
                                   required maxlength="50" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" 
                                   value="<?= htmlspecialchars($color) ?>">
                            <div class="invalid-feedback">Solo letras (Ej: Negro).</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control <?= isset($errores['fecha_nacimiento']) ? 'is-invalid' : '' ?>"
                                   max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($fecha_nac) ?>">
                            <div class="invalid-feedback">No puede ser futura.</div>
                        </div>
                    </div>
                    <button class="btn btn-main">Guardar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEspecieRaza" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="guardar_especie_raza.php" id="formEspecieRaza" novalidate><div class="modal-header"><h5 class="modal-title">Agregar</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Nueva especie</label><input type="text" name="nueva_especie" id="nuevaEspecieInput" class="form-control" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"><div class="invalid-feedback">Solo letras y espacios.</div><hr><label>O existente</label><select name="id_especie_existente" id="selectEspecieExistente" class="form-select"><option value="">-- Seleccione --</option><?php while($em=$especiesModal->fetch_assoc()):?><option value="<?=$em['id_especie']?>"><?=htmlspecialchars($em['nombre'])?></option><?php endwhile;?></select><hr><label>Nombre raza</label><input type="text" name="nombre_raza" class="form-control" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"><div class="invalid-feedback">Requerido. Solo letras y espacios.</div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button class="btn btn-success">Guardar</button></div></form></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectEspecie = document.getElementById("selectEspecie");
    const selectRaza = document.getElementById("selectRaza");
    function cargarRazas(idEspecie, seleccionado) {
        selectRaza.innerHTML = '<option value="">Cargando...</option>'; selectRaza.disabled = true;
        fetch("get_razas.php?id_especie=" + idEspecie).then(res => res.json()).then(data => {
            selectRaza.innerHTML = '<option value="">Seleccione raza</option>';
            data.forEach(r => {
                const opt = document.createElement("option"); opt.value = r.id_raza; opt.textContent = r.nombre;
                if(seleccionado && seleccionado == r.id_raza) opt.selected = true;
                selectRaza.appendChild(opt);
            });
            selectRaza.disabled = false;
        });
    }
    selectEspecie.addEventListener("change", function(){ if(this.value) cargarRazas(this.value, null); else { selectRaza.innerHTML='<option value="">...</option>'; selectRaza.disabled=true; } });
    <?php if($id_especie): ?> cargarRazas(<?=$id_especie?>, <?=$id_raza?$id_raza:'null'?>); <?php else: ?> selectRaza.disabled=true; <?php endif; ?>

    const ni=document.getElementById("nuevaEspecieInput"), se=document.getElementById("selectEspecieExistente"), fm=document.getElementById("formEspecieRaza");
    ni.addEventListener("input",()=>{ if(ni.value.trim().length>0){se.value="";se.disabled=true;}else{se.disabled=false;} });
    se.addEventListener("change",()=>{ if(se.value){ni.value="";ni.disabled=true;}else{ni.disabled=false;} });
    
    fm.addEventListener("submit", function (e) {
        const nueva = ni.value.trim();
        const existente = se.value;
        if ((nueva === "" && !existente) || (nueva !== "" && existente)) {
            e.preventDefault();
            alert("Elija una sola opción de especie.");
            return;
        }
        if (!fm.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        fm.classList.add("was-validated");
    });

    const form = document.getElementById("formMascota");
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