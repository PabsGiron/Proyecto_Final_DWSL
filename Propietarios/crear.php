<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../";
$pagina_actual = "propietarios";

$esAdmin = esAdmin();
if (!($esAdmin || esVeterinario())) {
    header("Location: ../principal.php");
    exit;
}

$errores = [];
$mensaje = "";
$tipoMensaje = "danger";

$nombre = ""; $telefono = ""; $direccion = ""; $correo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre    = trim($_POST["nombre"] ?? "");
    $telefono  = trim($_POST["telefono"] ?? "");
    $direccion = trim($_POST["direccion"] ?? "");
    $correo    = trim($_POST["correo"] ?? "");

    if ($nombre === "") $errores["nombre"] = "Nombre obligatorio.";
    elseif (!preg_match('/^[\p{L}\s]+$/u', $nombre)) $errores["nombre"] = "Solo letras y espacios.";

    if ($telefono === "") $errores["telefono"] = "Teléfono obligatorio.";
    elseif (!preg_match('/^[0-9+\-\s()]+$/', $telefono)) $errores["telefono"] = "Formato inválido.";

    if ($direccion === "") $errores["direccion"] = "Dirección obligatoria.";
    
    if ($correo === "") $errores["correo"] = "Correo obligatorio.";
    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores["correo"] = "Correo inválido.";

    if (empty($errores)) {
        $sql = "INSERT INTO propietarios (nombre, telefono, direccion, correo) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $telefono, $direccion, $correo);
        if ($stmt->execute()) {
            $mensaje = "Propietario registrado correctamente.";
            $tipoMensaje = "success";
            $nombre = ""; $telefono = ""; $direccion = ""; $correo = "";
        } else {
            $mensaje = "Error: " . $conexion->error;
        }
    } else {
        $mensaje = "Revisa los campos marcados en rojo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar dueño</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Registrar nuevo dueño</h4>
            <a href="lista.php" class="btn btn-outline-secondary btn-sm">Volver a la lista</a>
        </div>
        <div class="card-mini p-4">
            <?php if ($mensaje !== ""): ?>
                <div class="alert alert-<?= $tipoMensaje ?> py-2 mb-3"><?= $mensaje ?></div>
            <?php endif; ?>

            <form method="POST" id="formDueno" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                           required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" 
                           value="<?= htmlspecialchars($nombre) ?>">
                    <div class="invalid-feedback">El nombre es obligatorio y solo debe contener letras.</div>
                    <?php if (isset($errores["nombre"])): ?>
                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errores["nombre"]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control <?= isset($errores['telefono']) ? 'is-invalid' : '' ?>"
                           required minlength="7" maxlength="20" pattern="[0-9+\-\s()]+"
                           value="<?= htmlspecialchars($telefono) ?>">
                    <div class="invalid-feedback">Ingrese un teléfono válido (solo números y símbolos básicos).</div>
                    <?php if (isset($errores["telefono"])): ?>
                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errores["telefono"]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dirección</label>
                    <textarea name="direccion" class="form-control <?= isset($errores['direccion']) ? 'is-invalid' : '' ?>"
                              required maxlength="255" rows="3"><?= htmlspecialchars($direccion) ?></textarea>
                    <div class="invalid-feedback">La dirección es obligatoria.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="correo" class="form-control <?= isset($errores['correo']) ? 'is-invalid' : '' ?>"
                           required maxlength="100" value="<?= htmlspecialchars($correo) ?>">
                    <div class="invalid-feedback">Ingrese un correo válido.</div>
                    <?php if (isset($errores["correo"])): ?>
                        <div class="invalid-feedback d-block"><?= htmlspecialchars($errores["correo"]) ?></div>
                    <?php endif; ?>
                </div>

                <button class="btn btn-main">Guardar dueño</button>
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
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add("was-validated");
    });
});
</script>
</body>
</html>