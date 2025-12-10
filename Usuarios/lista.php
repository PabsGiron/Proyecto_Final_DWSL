<?php
require "../auth.php";
requireRol("admin");
require "../conexion.php";

$ruta = "../";
$pagina_actual = "usuarios";

const ID_SUPERADMIN = 1; 

$resultado = $conexion->query("SELECT id_usuario, nombre_completo, correo, rol, fecha_creacion FROM usuarios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - Clínica Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">

<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Usuarios</h4>
            <a href="crear.php" class="btn btn-main">+ Nuevo usuario</a>
        </div>

        <div class="table-card p-3">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Nombre completo</th><th>Correo</th><th>Rol</th><th>Fecha</th><th style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?= $u["id_usuario"] ?></td>
                                <td><?= htmlspecialchars($u["nombre_completo"]) ?></td>
                                <td><?= htmlspecialchars($u["correo"]) ?></td>
                                <td>
                                    <?php if ($u["id_usuario"] == ID_SUPERADMIN): ?>
                                        <span class="badge bg-warning text-dark">Superadmin</span>
                                    <?php else: ?>
                                        <?= ucfirst($u["rol"]) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= date("d/m/Y", strtotime($u["fecha_creacion"])) ?></td>
                                <td>
                                    <a href="editar.php?id=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    
                                    <?php if ($u["id_usuario"] != $_SESSION["id_usuario"] && $u["id_usuario"] != ID_SUPERADMIN): ?>
                                        <a href="eliminar.php?id=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if ($resultado->num_rows === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted">No hay usuarios registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>