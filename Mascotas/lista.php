<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../";
$pagina_actual = "mascotas";

$esAdmin = esAdmin();

if (!($esAdmin || esVeterinario())) {
    header("Location: ../principal.php");
    exit;
}

$busqueda = trim($_GET["q"] ?? "");

$sql = "SELECT m.id_mascota, m.nombre AS mascota, m.especie, m.raza, m.sexo, m.color, 
               p.nombre AS propietario, p.telefono AS telefono 
        FROM mascotas m 
        INNER JOIN propietarios p ON m.id_propietario = p.id_propietario";

$params = [];
$tipos  = "";

if ($busqueda !== "") {
    $sql .= " WHERE m.nombre LIKE ? OR m.especie LIKE ? OR m.raza LIKE ? OR p.nombre LIKE ?";
    $busquedaSQL = "%$busqueda%";
    $params = [$busquedaSQL, $busquedaSQL, $busquedaSQL, $busquedaSQL];
    $tipos  = "ssss";
}

$sql .= " ORDER BY m.nombre ASC";

$stmt = $conexion->prepare($sql);
if ($busqueda !== "") {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$mascotas = $stmt->get_result();
$hayResultados = ($mascotas->num_rows > 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mascotas - Clínica Veterinaria</title>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">

<div class="d-flex layout-wrapper">

    <?php require "../sidebar.php"; ?>

    <div class="flex-grow-1 p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Listado de Mascotas</h4>
            <a href="crear.php" class="btn btn-main">+ Nueva mascota</a>
        </div>

        <form class="row mb-3" method="GET">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" 
                       placeholder="Buscar por nombre, dueño, especie o raza..." 
                       value="<?= htmlspecialchars($busqueda) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary w-100">Buscar</button>
            </div>
            <?php if ($busqueda !== ""): ?>
                <div class="col-md-2">
                    <a href="lista.php" class="btn btn-outline-danger w-100">Limpiar</a>
                </div>
            <?php endif; ?>
        </form>

        <div class="card-mini p-3">
            <?php if (!$hayResultados): ?>
                <p class="text-center text-muted mb-0">No se encontraron mascotas registradas.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Raza</th>
                            <th>Sexo</th>
                            <th>Dueño</th>
                            <th>Teléfono</th>
                            <th class="text-end" style="min-width: 240px;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $contador = 1; 
                        while ($m = $mascotas->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?= $contador++ ?></td>
                                <td><?= htmlspecialchars($m["mascota"]) ?></td>
                                <td><?= htmlspecialchars($m["especie"] ?? "-") ?></td>
                                <td><?= htmlspecialchars($m["raza"] ?? "-") ?></td>
                                <td><?= htmlspecialchars($m["sexo"]) ?></td>
                                <td><?= htmlspecialchars($m["propietario"]) ?></td>
                                <td><?= htmlspecialchars($m["telefono"]) ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="expediente.php?id=<?= $m['id_mascota'] ?>" 
                                           class="btn btn-sm btn-outline-info">Expediente</a>
                                        
                                        <a href="editar.php?id=<?= $m['id_mascota'] ?>" 
                                           class="btn btn-sm btn-outline-primary">Editar</a>
                                        
                                        <a href="eliminar.php?id=<?= $m['id_mascota'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('¿Seguro que deseas eliminar esta mascota?');">Eliminar</a>
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