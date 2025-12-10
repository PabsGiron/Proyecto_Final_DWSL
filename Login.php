<?php
session_start();
require "conexion.php";

if (isset($_SESSION["id_usuario"])) {
    header("Location: principal.php");
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$sqlCount = "SELECT COUNT(*) AS total FROM usuarios";
$resCount = $conexion->query($sqlCount);
$rowCount = $resCount->fetch_assoc();
$totalUsuarios = intval($rowCount['total']);
$esInstalacion = ($totalUsuarios === 0); 

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST["correo"];
    $password = $_POST["password"];

    $query = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if ($password === $usuario["password"]) {
            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["nombre"]     = $usuario["nombre_completo"];
            $_SESSION["rol"]        = $usuario["rol"];
            header("Location: index.php");
            exit;
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "El correo no existe.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Clínica Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Css/style.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow-sm p-4 login-card">
        <div class="text-center mb-3">
            <div class="brand-icon">🐾</div>
            <h4 class="fw-bold">Clínica Veterinaria</h4>
            <p class="text-muted">Iniciar sesión</p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-danger py-2">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" name="correo" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <button class="btn btn-main w-100 mb-3">Entrar</button>
        </form>

        <?php if ($esInstalacion): ?>
            <div class="alert alert-warning text-center">
                <strong>¡Sistema vacío!</strong><br>
                No hay usuarios registrados.
                <a href="crear_admin.php" class="btn btn-sm btn-outline-dark w-100 mt-2">
                    Crear Primer Superadmin
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>