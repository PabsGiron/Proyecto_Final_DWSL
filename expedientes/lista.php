<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../";
$pagina_actual = "expedientes";

$esAdmin = esAdmin();

if (!($esAdmin || esVeterinario())) {
    header("Location: ../principal.php");
    exit;
}

$sql = "SELECT 
            m.id_mascota,
            m.nombre AS mascota,
            m.especie,
            m.raza,
            m.sexo,
            p.nombre   AS propietario,
            p.telefono AS telefono,
            COUNT(c.id_consulta)        AS total_consultas,
            MAX(c.fecha_consulta)       AS ultima_consulta
        FROM mascotas m
        INNER JOIN propietarios p ON m.id_propietario = p.id_propietario
        LEFT JOIN consultas c     ON c.id_mascota = m.id_mascota
        GROUP BY 
            m.id_mascota, m.nombre, m.especie, m.raza, m.sexo, p.nombre, p.telefono
        ORDER BY m.nombre ASC";

$expedientes = $conexion->query($sql);
if (!$expedientes) {
    die("Error al obtener expedientes: " . $conexion->error);
}
$hayResultados = ($expedientes->num_rows > 0);

function formatoFecha($fechaSQL) {
    if (!$fechaSQL) return "-";
    $dt = new DateTime($fechaSQL);
    return $dt->format("d/m/Y H:i");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expedientes - Clínica Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
    <style>
        @media print {
            .sidebar, .btn, .no-print { display: none !important; }
            .main-bg { background: white !important; }
            .card-mini { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="main-bg">

<div class="d-flex layout-wrapper">

    <?php require "../sidebar.php"; ?>

    <div class="flex-grow-1 p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Expedientes de animales</h4>
            <?php if ($hayResultados): ?>
                <button class="btn btn-outline-light btn-sm no-print" onclick="window.print()">
                    Imprimir listado
                </button>
            <?php endif; ?>
        </div>

        <div class="card-mini p-3">

            <?php if (!$hayResultados): ?>
                <p class="text-center text-muted mb-0">
                    No hay animales registrados para mostrar expedientes.
                </p>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Raza</th>
                            <th>Sexo</th>
                            <th>Dueño</th>
                            <th>Teléfono</th>
                            <th>Total consultas</th>
                            <th>Última consulta</th>
                            <th class="text-end no-print">Expediente</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $contador = 1;
                        while ($row = $expedientes->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $contador++ ?></td>
                                <td><?= htmlspecialchars($row["mascota"]) ?></td>
                                <td><?= htmlspecialchars($row["especie"] ?? "-") ?></td>
                                <td><?= htmlspecialchars($row["raza"] ?? "-") ?></td>
                                <td><?= htmlspecialchars($row["sexo"]) ?></td>
                                <td><?= htmlspecialchars($row["propietario"]) ?></td>
                                <td><?= htmlspecialchars($row["telefono"]) ?></td>
                                <td><?= (int)$row["total_consultas"] ?></td>
                                <td><?= formatoFecha($row["ultima_consulta"]) ?></td>
                                <td class="text-end no-print">
                                    <a href="../mascotas/expediente.php?id=<?= $row['id_mascota'] ?>"
                                       class="btn btn-sm btn-outline-info">
                                        Ver expediente
                                    </a>
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