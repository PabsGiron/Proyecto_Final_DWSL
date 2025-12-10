<?php
require "../auth.php";
require "../conexion.php";

date_default_timezone_set("America/El_Salvador");

$ruta = "../";
$pagina_actual = "citas";

$esAdmin = esAdmin();
$id_usuario = $_SESSION["id_usuario"];

$fecha_filtro = $_GET["fecha"] ?? date("Y-m-d");
$estado_filtro = $_GET["estado"] ?? "";

$sql = "SELECT c.*, m.nombre AS mascota, p.nombre AS propietario, u.nombre_completo AS veterinario 
        FROM citas c
        INNER JOIN mascotas m ON c.id_mascota = m.id_mascota
        INNER JOIN propietarios p ON m.id_propietario = p.id_propietario
        LEFT JOIN usuarios u ON c.id_veterinario = u.id_usuario
        WHERE c.fecha_cita = ?";

$params = [$fecha_filtro]; 
$tipos = "s";

if ($estado_filtro !== "") {
    $sql .= " AND c.estado = ?";
    $params[] = $estado_filtro; 
    $tipos .= "s";
}

if (!$esAdmin) {
    $sql .= " AND c.id_veterinario = ?";
    $params[] = $id_usuario; 
    $tipos .= "i";
}

$sql .= " ORDER BY c.hora_cita ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$citas = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda de Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">

<div class="d-flex layout-wrapper">

    <?php require "../sidebar.php"; ?>

    <div class="flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Agenda de Citas</h4>
            <a href="crear.php" class="btn btn-main">+ Nueva Cita</a>
        </div>

        <div class="card-mini p-3 mb-3">
            <form class="row g-2 align-items-end" method="GET">
                <div class="col-md-3">
                    <label class="form-label mb-1">Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($fecha_filtro) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Pendiente" <?= $estado_filtro=="Pendiente"?'selected':'' ?>>Pendiente</option>
                        <option value="Realizada" <?= $estado_filtro=="Realizada"?'selected':'' ?>>Realizada</option>
                        <option value="Cancelada" <?= $estado_filtro=="Cancelada"?'selected':'' ?>>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100">Filtrar</button>
                </div>
                <?php if(isset($_GET['fecha']) || isset($_GET['estado'])): ?>
                <div class="col-md-2">
                    <a href="lista.php" class="btn btn-outline-danger w-100">Limpiar</a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-card p-3">
            <?php if ($citas->num_rows === 0): ?>
                <p class="text-center text-muted py-3">No hay citas programadas para esta fecha.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hora</th>
                                <th>Mascota</th>
                                <th>Dueño</th>
                                <th>Veterinario</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($c = $citas->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?= date("h:i A", strtotime($c["hora_cita"])) ?></td>
                                <td><?= htmlspecialchars($c["mascota"]) ?></td>
                                <td><?= htmlspecialchars($c["propietario"]) ?></td>
                                <td><?= htmlspecialchars($c["veterinario"] ?? 'No asignado') ?></td>
                                <td><?= htmlspecialchars($c["motivo"]) ?></td>
                                <td>
                                    <?php 
                                        $badge = match($c["estado"]) {
                                            'Pendiente' => 'bg-warning text-dark',
                                            'Realizada' => 'bg-success',
                                            'Cancelada' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= $c["estado"] ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="editar.php?id=<?= $c['id_cita'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <a href="eliminar.php?id=<?= $c['id_cita'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar cita?');">X</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>