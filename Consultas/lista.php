<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../";
$pagina_actual = "consultas";


$id_usuario = $_SESSION["id_usuario"];
$esAdmin    = esAdmin();

if ($esAdmin) {
    $sql = "SELECT c.id_consulta, c.fecha_consulta, c.peso_kg, c.tamano, c.costo_servicio,
                   m.nombre AS mascota, m.especie,
                   p.nombre AS propietario,
                   u.nombre_completo AS veterinario
            FROM consultas c
            INNER JOIN mascotas m     ON c.id_mascota = m.id_mascota
            INNER JOIN propietarios p ON m.id_propietario = p.id_propietario
            INNER JOIN usuarios u     ON c.id_veterinario = u.id_usuario
            ORDER BY c.fecha_consulta DESC";
    $stmt = $conexion->prepare($sql);
} else {
    $sql = "SELECT c.id_consulta, c.fecha_consulta, c.peso_kg, c.tamano, c.costo_servicio,
                   m.nombre AS mascota, m.especie,
                   p.nombre AS propietario,
                   u.nombre_completo AS veterinario
            FROM consultas c
            INNER JOIN mascotas m     ON c.id_mascota = m.id_mascota
            INNER JOIN propietarios p ON m.id_propietario = p.id_propietario
            INNER JOIN usuarios u     ON c.id_veterinario = u.id_usuario
            WHERE c.id_veterinario = ?
            ORDER BY c.fecha_consulta DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultas - Clínica Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">

<div class="d-flex layout-wrapper">

    <?php require "../sidebar.php"; ?>

    <div class="flex-grow-1 p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Consultas</h4>
            <?php if (esVeterinario() || esAdmin()): ?>
                <a href="crear.php" class="btn btn-main">+ Nueva consulta</a>
            <?php endif; ?>
        </div>

        <div class="table-card p-3">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Mascota</th>
                            <th>Especie</th>
                            <th>Propietario</th>
                            <th>Veterinario</th>
                            <th>Peso (kg)</th>
                            <th>Tamaño</th>
                            <th>Costo</th>
                            <th style="width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($c = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?= $c["id_consulta"] ?></td>
                                <td><?= $c["fecha_consulta"] ?></td>
                                <td><?= htmlspecialchars($c["mascota"]) ?></td>
                                <td><?= htmlspecialchars($c["especie"]) ?></td>
                                <td><?= htmlspecialchars($c["propietario"]) ?></td>
                                <td><?= htmlspecialchars($c["veterinario"]) ?></td>
                                <td><?= $c["peso_kg"] ?></td>
                                <td><?= htmlspecialchars($c["tamano"]) ?></td>
                                <td>$<?= number_format($c["costo_servicio"], 2) ?></td>
                                <td class="d-flex flex-wrap gap-1">
                                    <a href="ver.php?id=<?= $c['id_consulta'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                                    <a href="editar.php?id=<?= $c['id_consulta'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <a href="eliminar.php?id=<?= $c['id_consulta'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar esta consulta?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if ($resultado->num_rows === 0): ?>
                            <tr><td colspan="10" class="text-center text-muted">No hay consultas registradas.</td></tr>
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