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

$busqueda = trim($_GET["q"] ?? "");

$sql = "SELECT id_propietario, nombre, telefono, direccion, correo FROM propietarios";
$params = [];
$tipos  = "";

if ($busqueda !== "") {
    $sql .= " WHERE nombre LIKE ? OR telefono LIKE ? OR correo LIKE ?";
    $busquedaSQL = "%$busqueda%";
    $params = [$busquedaSQL, $busquedaSQL, $busquedaSQL];
    $tipos  = "sss";
}

$sql .= " ORDER BY nombre ASC";

$stmt = $conexion->prepare($sql);

if ($busqueda !== "") {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
$hayResultados = ($resultado->num_rows > 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propietarios - Clínica Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="main-bg">

<div class="d-flex layout-wrapper">

    <?php require "../sidebar.php"; ?>

    <div class="flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Propietarios</h4>
            <a href="crear.php" class="btn btn-main">+ Nuevo propietario</a>
        </div>

        <form class="row mb-3" method="GET">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" 
                       placeholder="Buscar por nombre, teléfono o correo..." 
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

        <div class="table-card p-3">
            <?php if (!$hayResultados): ?>
                <p class="text-center text-muted mb-0">No se encontraron propietarios.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Dirección</th>
                                <th class="text-end" style="min-width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $contador = 1;
                            while ($p = $resultado->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?= $contador++ ?></td>
                                    <td><?= htmlspecialchars($p["nombre"]) ?></td>
                                    <td><?= htmlspecialchars($p["telefono"]) ?></td>
                                    <td><?= htmlspecialchars($p["correo"]) ?></td>
                                    <td><?= htmlspecialchars($p["direccion"]) ?></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="editar.php?id=<?= $p['id_propietario'] ?>" 
                                               class="btn btn-sm btn-outline-primary">Editar</a>
                                            
                                            <a href="eliminar.php?id=<?= $p['id_propietario'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('¿Seguro que deseas eliminar este propietario?');">Eliminar</a>
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