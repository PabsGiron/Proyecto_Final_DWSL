<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../"; $pagina_actual = "usuarios";
$esAdmin = esAdmin();
if (!$esAdmin) { header("Location: ../principal.php"); exit; }

const ID_SUPERADMIN = 1;
$errores = []; $mensaje = ""; $tipoMensaje = "danger";

$id_usuario_target = intval($_GET["id"] ?? 0);
if ($id_usuario_target <= 0) { header("Location: lista.php"); exit; }

$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario_target);
$stmt->execute();
$usuarioDB = $stmt->get_result()->fetch_assoc();
if (!$usuarioDB) { header("Location: lista.php"); exit; }

$mi_id = $_SESSION["id_usuario"];
$soy_superadmin = ($mi_id == ID_SUPERADMIN);
$es_mismo_usuario = ($mi_id == $id_usuario_target);
$target_es_superadmin = ($id_usuario_target == ID_SUPERADMIN);
$target_es_admin = ($usuarioDB["rol"] === 'admin');
$puede_cambiar_rol = true;
if ($es_mismo_usuario || $target_es_superadmin || ($target_es_admin && !$soy_superadmin)) $puede_cambiar_rol = false;

$nombre_completo = $usuarioDB["nombre_completo"];
$correo = $usuarioDB["correo"];
$rol = $usuarioDB["rol"];
$password = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_completo = trim($_POST["nombre_completo"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";
    
    if ($puede_cambiar_rol) $rol = $_POST["rol"] ?? ""; else $rol = $usuarioDB["rol"];

    if ($nombre_completo === "" || !preg_match('/^[\p{L}\s]+$/u', $nombre_completo)) $errores["nombre_completo"] = "Solo letras y espacios.";
    if ($correo === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores["correo"] = "Correo inválido.";
    else {
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario <> ? LIMIT 1");
        $stmt->bind_param("si", $correo, $id_usuario_target); $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errores["correo"] = "Correo ya existe.";
    }

    $cambiarPassword = false;
    if ($password !== "") {
        $cambiarPassword = true;
        if (strlen($password) < 4) $errores["password"] = "Mínimo 4 caracteres.";
    }

    if (empty($errores)) {
        if ($cambiarPassword) {
            $stmt = $conexion->prepare("UPDATE usuarios SET nombre_completo=?, correo=?, password=?, rol=? WHERE id_usuario=?");
            $stmt->bind_param("ssssi", $nombre_completo, $correo, $password, $rol, $id_usuario_target);
        } else {
            $stmt = $conexion->prepare("UPDATE usuarios SET nombre_completo=?, correo=?, rol=? WHERE id_usuario=?");
            $stmt->bind_param("sssi", $nombre_completo, $correo, $rol, $id_usuario_target);
        }
        if ($stmt->execute()) {
            $mensaje = "Actualizado."; $tipoMensaje = "success"; $usuarioDB["rol"] = $rol; 
        } else { $mensaje = "Error SQL."; }
    } else { $mensaje = "Revise los errores."; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Editar usuario</h4>
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
                    <label class="form-label">Contraseña (opcional)</label>
                    <input type="password" name="password" class="form-control <?= isset($errores['password'])?'is-invalid':'' ?>" minlength="4">
                    <div class="invalid-feedback">Mínimo 4 caracteres.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select" required <?= !$puede_cambiar_rol ? 'disabled' : '' ?>>
                        <option value="admin" <?= $rol === "admin" ? "selected" : "" ?>>Admin</option>
                        <option value="veterinario" <?= $rol === "veterinario" ? "selected" : "" ?>>Veterinario</option>
                    </select>
                    <?php if (!$puede_cambiar_rol): ?>
                        <input type="hidden" name="rol" value="<?= htmlspecialchars($rol) ?>">
                        <div class="form-text text-danger">No tienes permiso para cambiar este rol.</div>
                    <?php endif; ?>
                </div>
                <button class="btn btn-main">Guardar cambios</button>
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