<?php
require "conexion.php";

$sqlCheck = "SELECT COUNT(*) AS total FROM usuarios";
$resCheck = $conexion->query($sqlCheck);
$row = $resCheck->fetch_assoc();

if ($row['total'] > 0) {
    header("Location: Login.php");
    exit;
}

$mensaje = "";
$tipoMensaje = "danger";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_completo = trim($_POST["nombre_completo"] ?? "");
    $correo          = trim($_POST["correo"] ?? "");
    $password        = $_POST["password"] ?? "";
    
    $rol = "admin"; 

    if ($nombre_completo === "" || $correo === "" || $password === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } elseif (strlen($password) < 4) {
        $mensaje = "La contraseña debe tener al menos 4 caracteres.";
    } else {
        $sql = "INSERT INTO usuarios (nombre_completo, correo, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssss", $nombre_completo, $correo, $password, $rol);

        if ($stmt->execute()) {
            header("Location: Login.php");
            exit;
        } else {
            $mensaje = "Error SQL: " . $conexion->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalación - Superadmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Css/style.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">

    <div class="card shadow-lg p-4" style="width: 100%; max-width: 450px;">
        <div class="text-center mb-4">
            <h2 class="display-4">🚀</h2>
            <h4 class="fw-bold text-primary">Bienvenido</h4>
            <p class="text-muted">Configuración inicial del sistema</p>
            <div class="alert alert-info py-2 small">
                Estás a punto de crear el <strong>Usuario Superadmin (ID 1)</strong>. 
                <br>Esta opción desaparecerá después de crearlo.
            </div>
        </div>

        <?php if ($mensaje !== ""): ?>
            <div class="alert alert-<?= $tipoMensaje ?>"><?= $mensaje ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nombre del Admin</label>
                <input type="text" name="nombre_completo" class="form-control" required placeholder="Ej: Nayib Bukele">
            </div>

            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="correo" class="form-control" required placeholder="admin@veterinaria.com">
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña Maestra</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100 fw-bold">Crear Superadmin e Iniciar</button>
        </form>
    </div>

</body>
</html>