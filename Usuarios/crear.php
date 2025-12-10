<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../"; $pagina_actual = "usuarios";
$esAdmin = esAdmin();
if (!$esAdmin) { header("Location: ../principal.php"); exit; }

$errores = []; $mensaje = ""; $tipoMensaje = "danger";
$nombre_completo = ""; $correo = ""; $password = ""; $rol = "veterinario";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_completo = trim($_POST["nombre_completo"] ?? "");
    $correo          = trim($_POST["correo"] ?? "");
    $password        = $_POST["password"] ?? "";
    $rol             = $_POST["rol"] ?? "";

    if ($nombre_completo === "" || !preg_match('/^[\p{L}\s]+$/u', $nombre_completo)) $errores["nombre_completo"] = "Solo letras y espacios.";
    
    if ($correo === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores["correo"] = "Correo inválido.";
    else {
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? LIMIT 1");
        $stmt->bind_param("s", $correo); $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errores["correo"] = "Correo ya existe.";
    }

    if (strlen($password) < 4) $errores["password"] = "Mínimo 4 caracteres.";
    if ($rol === "") $errores["rol"] = "Requerido.";

    if (empty($errores)) {
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre_completo, $correo, $password, $rol);
        if ($stmt->execute()) {
            $mensaje = "Usuario creado."; $tipoMensaje = "success";
            $nombre_completo = ""; $correo = ""; $password = ""; $rol = "veterinario";
        } else { $mensaje = "Error SQL: " . $conexion->error; }
    } else {
        $mensaje = "Revise los campos en rojo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Nuevo usuario</h4>
            <a href="lista.php" class="btn btn-outline-secondary btn-sm">Volver</a>
        </div>
        <div class="card-mini p-4">
            <?php if ($mensaje !== ""): ?>
                <div class="alert alert-<?= $tipoMensaje ?> py-2 mb-3"><?= $mensaje ?></div>
            <?php endif; ?>
            
            <form method="POST" id="formUsuario" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre_completo" class="form-control <?= isset($errores['nombre_completo'])?'is-invalid':'' ?>" 
                           required minlength="3" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" 
                           value="<?= htmlspecialchars($nombre_completo) ?>">
                    <div class="invalid-feedback">Solo letras y espacios.</div>
                    <?php if(isset($errores["nombre_completo"])):?><div class="invalid-feedback d-block"><?=$errores["nombre_completo"]?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control <?= isset($errores['correo'])?'is-invalid':'' ?>" 
                           required value="<?= htmlspecialchars($correo) ?>">
                    <div class="invalid-feedback">Correo inválido.</div>
                    <?php if(isset($errores["correo"])):?><div class="invalid-feedback d-block"><?=$errores["correo"]?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control <?= isset($errores['password'])?'is-invalid':'' ?>" 
                           required minlength="4">
                    <div class="invalid-feedback">Mínimo 4 caracteres.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="admin" <?= $rol === "admin" ? "selected" : "" ?>>Admin</option>
                        <option value="veterinario" <?= $rol === "veterinario" ? "selected" : "" ?>>Veterinario</option>
                    </select>
                </div>
                <button class="btn btn-main">Guardar</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formUsuario");
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