<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../"; $pagina_actual = "mascotas";
$esAdmin = esAdmin();
if (!($esAdmin || esVeterinario())) { header("Location: ../principal.php"); exit; }

$id_mascota = intval($_GET["id"] ?? 0);
if ($id_mascota <= 0) { header("Location: lista.php"); exit; }

$sqlMascota = "SELECT m.*, p.nombre AS propietario_nombre, p.telefono AS propietario_telefono, p.direccion AS propietario_direccion, p.correo AS propietario_correo 
               FROM mascotas m INNER JOIN propietarios p ON m.id_propietario = p.id_propietario WHERE m.id_mascota = ?";
$stmtMasc = $conexion->prepare($sqlMascota);
$stmtMasc->bind_param("i", $id_mascota);
$stmtMasc->execute();
$mascota = $stmtMasc->get_result()->fetch_assoc();
if (!$mascota) { header("Location: lista.php"); exit; }

$sqlConsultas = "SELECT c.*, u.nombre_completo AS veterinario_nombre 
                 FROM consultas c LEFT JOIN usuarios u ON c.id_veterinario = u.id_usuario 
                 WHERE c.id_mascota = ? ORDER BY c.fecha_consulta DESC";
$stmtCons = $conexion->prepare($sqlConsultas);
$stmtCons->bind_param("i", $id_mascota);
$stmtCons->execute();
$consultas = $stmtCons->get_result();
$tieneConsultas = ($consultas->num_rows > 0);

function formatoFechaHora($fechaSQL) {
    if (!$fechaSQL) return "";
    $dt = new DateTime($fechaSQL);
    return $dt->format("d/m/Y H:i");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente - <?= htmlspecialchars($mascota["nombre"]) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
    <style>
        .header-print { display: none; }
        @media print {
            @page { margin: 1cm; size: letter; }
            body { background: white !important; font-family: Arial, sans-serif; font-size: 11pt; color: #000; }
            .sidebar, .btn, .no-print, .breadcrumb { display: none !important; }
            .main-bg, .layout-wrapper, .flex-grow-1 { padding: 0 !important; margin: 0 !important; width: 100% !important; display: block !important; }
            .card-mini { box-shadow: none !important; border: 1px solid #ddd !important; padding: 10px !important; margin-bottom: 15px !important; page-break-inside: avoid; }
            .header-print { display: block; text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
            .header-print h2 { margin: 0; font-weight: bold; font-size: 18pt; text-transform: uppercase; }
            .header-print p { margin: 0; font-size: 10pt; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ccc; padding: 6px; text-align: left; font-size: 10pt; }
            th { background-color: #f0f0f0 !important; font-weight: bold; }
            thead { display: table-header-group; } 
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="main-bg">
<div class="d-flex layout-wrapper">
    <?php require "../sidebar.php"; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4 class="fw-bold">Expediente Clínico</h4>
            <div>
                <a href="lista.php" class="btn btn-outline-secondary btn-sm me-2">Volver</a>
                <button class="btn btn-dark btn-sm" onclick="window.print()">Imprimir Expediente</button>
            </div>
        </div>
        <div id="print-area">
            <div class="header-print">
                <h2>CLÍNICA VETERINARIA GIRÓN</h2>
                <p>Historial Clínico Completo</p>
                <p>Fecha de emisión: <?= date("d/m/Y H:i") ?></p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card-mini mb-3">
                        <h5 class="fw-bold border-bottom pb-2">PACIENTE</h5>
                        <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($mascota["nombre"]) ?></p>
                        <p class="mb-1"><strong>Especie:</strong> <?= htmlspecialchars($mascota["especie"]) ?> | <strong>Raza:</strong> <?= htmlspecialchars($mascota["raza"]) ?></p>
                        <p class="mb-1"><strong>Sexo:</strong> <?= htmlspecialchars($mascota["sexo"]) ?> | <strong>Color:</strong> <?= htmlspecialchars($mascota["color"]) ?></p>
                        <p class="mb-0"><strong>Nacimiento:</strong> <?= $mascota["fecha_nacimiento"] ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-mini mb-3">
                        <h5 class="fw-bold border-bottom pb-2">PROPIETARIO</h5>
                        <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($mascota["propietario_nombre"]) ?></p>
                        <p class="mb-1"><strong>Teléfono:</strong> <?= htmlspecialchars($mascota["propietario_telefono"]) ?></p>
                        <p class="mb-1"><strong>Correo:</strong> <?= htmlspecialchars($mascota["propietario_correo"]) ?></p>
                        <p class="mb-0"><strong>Dirección:</strong> <?= htmlspecialchars($mascota["propietario_direccion"]) ?></p>
                    </div>
                </div>
            </div>
            <div class="card-mini p-0 border-0 shadow-none">
                <h5 class="fw-bold p-3 pb-0">HISTORIAL DE CONSULTAS</h5>
                <?php if (!$tieneConsultas): ?>
                    <p class="p-3 text-muted">No hay consultas registradas.</p>
                <?php else: ?>
                    <div class="table-responsive p-3">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 15%;">Fecha</th>
                                    <th style="width: 25%;">Datos Clínicos</th>
                                    <th style="width: 40%;">Diagnóstico y Observaciones</th>
                                    <th style="width: 15%;">Atendido por</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $contador = 1; while ($c = $consultas->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $contador++ ?></td>
                                        <td><?= formatoFechaHora($c["fecha_consulta"]) ?></td>
                                        <td>
                                            <small>
                                            <strong>Peso:</strong> <?= $c["peso_kg"] ?> kg<br>
                                            <strong>Tam:</strong> <?= $c["tamano"] ?><br>
                                            <strong>Costo:</strong> $<?= $c["costo_servicio"] ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($c["observacion_clinica"]): ?>
                                                <div class="mb-1"><strong>Obs:</strong> <?= $c["observacion_clinica"] ?></div>
                                            <?php endif; ?>
                                            <?php if ($c["conclusion_diagnostico"]): ?>
                                                <div class="text-primary"><strong>Diag:</strong> <?= $c["conclusion_diagnostico"] ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($c["veterinario_nombre"]) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="header-print" style="border-top: 1px solid #000; border-bottom: none; margin-top: 30px; padding-top: 10px;">
                <p>Fin del Expediente Clínico</p>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>