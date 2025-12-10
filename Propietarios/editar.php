<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../"; $pagina_actual = "propietarios";
$esAdmin = esAdmin();
if (!($esAdmin || esVeterinario())) { header("Location: ../principal.php"); exit; }

$errores = []; $mensaje = ""; $tipoMensaje = "danger";

$id_propietario = intval($_GET["id"] ?? 0);
if ($id_propietario <= 0) { header("Location: lista.php"); exit; }

$stmt = $conexion->prepare("SELECT * FROM propietarios WHERE id_propietario = ?");
$stmt->bind_param("i", $id_propietario);
$stmt->execute();
$duenoDB = $stmt->get_result()->fetch_assoc();
if (!$duenoDB) { header("Location: lista.php"); exit; }

$nombre = $duenoDB["nombre"];
$telefono = $duenoDB["telefono"];
$direccion = $duenoDB["direccion"];
$correo = $duenoDB["correo"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $direccion = trim($_POST["direccion"] ?? "");
    $correo = trim($_POST["correo"] ?? "");

    if ($nombre === "" || !preg_match('/^[\p{L}\s]+$/u', $nombre)) $errores["nombre"] = "Nombre inválido.";
    if ($telefono === "" || !preg_match('/^[0-9+\-\s()]+$/', $telefono)) $errores["telefono"] = "Teléfono inválido.";
    if ($direccion === "") $errores["direccion"] = "Dirección requerida.";
    if ($correo === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores["correo"] = "Correo inválido.";

    if (empty($errores)) {
        $sqlUpd = "UPDATE propietarios SET nombre=?, telefono=?, direccion=?, correo=? WHERE id_propietario=?";
        $stmtUpd = $conexion->prepare($sqlUpd);
        $stmtUpd->bind_param("ssssi", $nombre, $telefono, $direccion, $correo, $id_propietario);
        if ($stmtUpd->execute()) {
            $mensaje = "Actualizado correctamente.";
            $tipoMensaje = "success";
        } else {
            $mensaje = "Error SQL: " . $conexion->error;
        }
    } else {
        $mensaje = "Verifique los datos ingresados.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar dueño</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Editar dueño</h4>
            <a href="lista.php" class="btn btn-outline-secondary btn-sm">Volver a la lista</a>
        </div>
        <div class="card-mini p-4">
            <?php if ($mensaje !== ""): ?>
                <div class="alert alert-<?= $tipoMensaje ?> py-2 mb-3"><?= $mensaje ?></div>
            <?php endif; ?>
            <form method="POST" id="formDueno" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control" required minlength="2" maxlength="100" 
                           pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" value="<?= htmlspecialchars($nombre) ?>">
                    <div class="invalid-feedback">Solo letras y espacios.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" required minlength="7" maxlength="20" 
                           pattern="[0-9+\-\s()]+" value="<?= htmlspecialchars($telefono) ?>">
                    <div class="invalid-feedback">Solo números y símbolos válidos.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección</label>
                    <textarea name="direccion" class="form-control" required rows="3"><?= htmlspecialchars($direccion) ?></textarea>
                    <div class="invalid-feedback">Campo obligatorio.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" required value="<?= htmlspecialchars($correo) ?>">
                    <div class="invalid-feedback">Correo inválido.</div>
                </div>
                <button class="btn btn-main">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formDueno");
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