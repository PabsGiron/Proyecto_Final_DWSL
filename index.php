<?php
require "auth.php";
require "conexion.php";

date_default_timezone_set("America/El_Salvador");

$nombre_usuario = $_SESSION["nombre"];
$rol_usuario    = $_SESSION["rol"];
$id_usuario     = $_SESSION["id_usuario"];

$ruta = "";               
$pagina_actual = "dashboard"; 

$esAdmin = esAdmin();
$hoy     = date("Y-m-d");
$ahora   = date("Y-m-d H:i:s");
$hace7   = date("Y-m-d", strtotime("-7 days"));

if ($esAdmin) {
    $sqlCount = "SELECT COUNT(*) AS total FROM citas WHERE fecha_cita = ? AND estado = 'Pendiente'";
    $stmtCount = $conexion->prepare($sqlCount);
    $stmtCount->bind_param("s", $hoy);
} else {
    $sqlCount = "SELECT COUNT(*) AS total FROM citas WHERE fecha_cita = ? AND id_veterinario = ? AND estado = 'Pendiente'";
    $stmtCount = $conexion->prepare($sqlCount);
    $stmtCount->bind_param("si", $hoy, $id_usuario);
}
$stmtCount->execute();
$resCount  = $stmtCount->get_result()->fetch_assoc();
$citasHoy  = $resCount ? (int)$resCount["total"] : 0;

$resMasc = $conexion->query("SELECT COUNT(*) AS total FROM mascotas");
$rowMasc = $resMasc ? $resMasc->fetch_assoc() : ["total" => 0];
$nuevasMascotas = (int)$rowMasc["total"];

if ($esAdmin) {
    $sqlCurso = "SELECT COUNT(*) AS total FROM consultas";
    $stmtCurso = $conexion->prepare($sqlCurso);
} else {
    $sqlCurso = "SELECT COUNT(*) AS total FROM consultas WHERE id_veterinario = ?";
    $stmtCurso = $conexion->prepare($sqlCurso);
    $stmtCurso->bind_param("i", $id_usuario);
}
$stmtCurso->execute();
$rowCurso = $stmtCurso->get_result()->fetch_assoc();
$totalConsultas = $rowCurso ? (int)$rowCurso["total"] : 0;

if ($esAdmin) {
    $sqlHist = "SELECT COUNT(*) AS total FROM consultas WHERE DATE(fecha_consulta) >= ?";
    $stmtHist = $conexion->prepare($sqlHist);
    $stmtHist->bind_param("s", $hace7);
} else {
    $sqlHist = "SELECT COUNT(*) AS total FROM consultas WHERE DATE(fecha_consulta) >= ? AND id_veterinario = ?";
    $stmtHist = $conexion->prepare($sqlHist);
    $stmtHist->bind_param("si", $hace7, $id_usuario);
}
$stmtHist->execute();
$rowHist = $stmtHist->get_result()->fetch_assoc();
$historialesActualizados = $rowHist ? (int)$rowHist["total"] : 0;

if ($esAdmin) {
    $sqlProx = "SELECT c.hora_cita, m.nombre AS mascota, m.especie, p.nombre AS propietario, c.motivo, c.estado
                FROM citas c
                INNER JOIN mascotas m ON c.id_mascota = m.id_mascota
                INNER JOIN propietarios p ON m.id_propietario = p.id_propietario
                WHERE c.fecha_cita = ? AND c.estado = 'Pendiente'
                ORDER BY c.hora_cita ASC";
    $stmtProx = $conexion->prepare($sqlProx);
    $stmtProx->bind_param("s", $hoy);
} else {
    $sqlProx = "SELECT c.hora_cita, m.nombre AS mascota, m.especie, p.nombre AS propietario, c.motivo, c.estado
                FROM citas c
                INNER JOIN mascotas m ON c.id_mascota = m.id_mascota
                INNER JOIN propietarios p ON m.id_propietario = p.id_propietario
                WHERE c.fecha_cita = ? AND c.id_veterinario = ? AND c.estado = 'Pendiente'
                ORDER BY c.hora_cita ASC";
    $stmtProx = $conexion->prepare($sqlProx);
    $stmtProx->bind_param("si", $hoy, $id_usuario);
}
$stmtProx->execute();
$citasDelDia = $stmtProx->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Clínica Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Css/style.css">
</head>

<body>

<div class="d-flex layout-wrapper">

    <?php require "sidebar.php"; ?>

    <div class="flex-grow-1 p-4 main-bg">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Bienvenido, <?= htmlspecialchars($nombre_usuario) ?></h4>
            <span class="badge text-bg-success">
                Rol: <?= ucfirst(htmlspecialchars($rol_usuario)) ?>
            </span>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 card-mini">
                    <h6 class="text-muted">Citas para hoy</h6>
                    <h3 class="fw-bold text-primary"><?= $citasHoy ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 card-mini">
                    <h6 class="text-muted">Total Mascotas</h6>
                    <h3 class="fw-bold"><?= $nuevasMascotas ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 card-mini">
                    <h6 class="text-muted">Consultas Totales</h6>
                    <h3 class="fw-bold"><?= $totalConsultas ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 card-mini">
                    <h6 class="text-muted">Actividad (7 días)</h6>
                    <h3 class="fw-bold"><?= $historialesActualizados ?></h3>
                </div>
            </div>
        </div>

        <div class="table-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Agenda del día (<?= date("d/m/Y") ?>)</h5>
                <a href="citas/crear.php" class="btn btn-sm btn-main">+ Agendar Cita</a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Hora</th>
                            <th>Mascota</th>
                            <th>Especie</th>
                            <th>Dueño</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($citasDelDia->num_rows > 0): ?>
                            <?php while ($row = $citasDelDia->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        <?= date("h:i A", strtotime($row["hora_cita"])) ?>
                                    </td>
                                    <td><?= htmlspecialchars($row["mascota"]) ?></td>
                                    <td><?= htmlspecialchars($row["especie"]) ?></td>
                                    <td><?= htmlspecialchars($row["propietario"]) ?></td>
                                    <td><?= htmlspecialchars($row["motivo"]) ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x" style="font-size: 2rem;"></i><br>
                                    No hay citas pendientes para hoy.
                                </td>
                            </tr>
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