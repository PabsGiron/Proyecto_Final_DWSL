<?php
require "../auth.php";
require "../conexion.php";

$ruta = "../"; $pagina_actual = "consultas";
$id_usuario = $_SESSION["id_usuario"];
$esAdmin    = esAdmin();

if (!isset($_GET["id"])) { header("Location: lista.php"); exit; }
$id_consulta = intval($_GET["id"]);

$sql = "SELECT c.*, 
               m.nombre AS nombre_mascota, m.especie AS especie_mascota, m.raza AS raza_mascota, m.sexo AS sexo_mascota, m.color AS color_mascota, m.fecha_nacimiento,
               p.nombre AS nombre_propietario, p.telefono, p.direccion, p.correo AS correo_propietario,
               u.nombre_completo AS nombre_veterinario, u.correo AS correo_veterinario
        FROM consultas c
        INNER JOIN mascotas m     ON c.id_mascota = m.id_mascota
        INNER JOIN propietarios p ON m.id_propietario = p.id_propietario
        INNER JOIN usuarios u     ON c.id_veterinario = u.id_usuario
        WHERE c.id_consulta = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_consulta);
$stmt->execute();
$consulta = $stmt->get_result()->fetch_assoc();

if (!$consulta) { header("Location: lista.php"); exit; }
if (!$esAdmin && $consulta["id_veterinario"] != $id_usuario) { header("Location: lista.php"); exit; }

$autoImprimir = isset($_GET["imprimir"]) && $_GET["imprimir"] == "1";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Consulta #<?= $consulta["id_consulta"] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
    <style>
        .header-print { display: none; }

        @media print {
            @page { 
                margin: 1cm; 
                size: letter; 
            }
            body { 
                background: white !important; 
                font-family: Arial, sans-serif;
                font-size: 12pt;
                color: #000;
            }
            .sidebar, .btn, .no-print, .breadcrumb { display: none !important; }
            
            .main-bg, .layout-wrapper, .flex-grow-1 { 
                padding: 0 !important; 
                margin: 0 !important; 
                width: 100% !important;
                display: block !important;
            }
            
            .card-mini { 
                box-shadow: none !important; 
                border: none !important; 
                padding: 0 !important;
                margin-bottom: 20px !important;
            }

            .header-print {
                display: block;
                text-align: center;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .header-print h2 { margin: 0; font-weight: bold; text-transform: uppercase; }
            .header-print p { margin: 0; font-size: 10pt; }

            .section-title {
                background-color: #f0f0f0 !important; 
                font-weight: bold;
                padding: 5px 10px;
                border: 1px solid #ccc;
                margin-bottom: 10px;
                margin-top: 15px;
                font-size: 11pt;
                -webkit-print-color-adjust: exact; 
            }

            .info-row {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 5px;
            }
            .info-col {
                flex: 1;
                min-width: 30%;
                margin-right: 10px;
            }
            .info-label { font-weight: bold; font-size: 10pt; }
            .info-value { font-size: 11pt; }

            .receta-box {
                border: 1px solid #000;
                padding: 10px;
                min-height: 100px;
                margin-top: 5px;
            }

            .total-box {
                text-align: right;
                font-size: 14pt;
                font-weight: bold;
                margin-top: 20px;
                border-top: 1px dashed #000;
                padding-top: 10px;
            }

            .firma-section {
                margin-top: 60px;
                display: flex;
                justify-content: flex-end;
            }
            .firma-line {
                border-top: 1px solid #000;
                width: 250px;
                text-align: center;
                padding-top: 5px;
            }
        }
    </style>
</head>
<body class="main-bg">

<div class="d-flex layout-wrapper">

    <?php require "../sidebar.php"; ?>

    <div class="flex-grow-1 p-4">

        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4 class="fw-bold">Detalle de Consulta</h4>
            <div class="d-flex gap-2">
                <a href="lista.php" class="btn btn-outline-secondary btn-sm">Volver</a>
                <button class="btn btn-dark btn-sm" onclick="window.print()">Imprimir Reporte</button>
            </div>
        </div>

        <div id="print-area">
            
            <div class="header-print">
                <h2>CLÍNICA VETERINARIA GIRÓN</h2>
                <p>4ta Avenida Sur N43, Corinto, El Salvador</p>
                <p>Tel: (503) 7931-8491 | Email: clinica@gmail.com</p>
                <br>
                <div style="display:flex; justify-content:space-between; font-size:10pt;">
                    <span><strong>Comprobante de Consulta #<?= $consulta["id_consulta"] ?></strong></span>
                    <span>Fecha: <?= date("d/m/Y H:i", strtotime($consulta["fecha_consulta"])) ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="section-title">DATOS DEL PROPIETARIO</div>
                    <p><span class="info-label">Nombre:</span> <?= htmlspecialchars($consulta["nombre_propietario"]) ?></p>
                    <p><span class="info-label">Teléfono:</span> <?= htmlspecialchars($consulta["telefono"]) ?></p>
                    <p><span class="info-label">Dirección:</span> <?= htmlspecialchars($consulta["direccion"]) ?></p>
                </div>
                <div class="col-6">
                    <div class="section-title">DATOS DEL PACIENTE</div>
                    <p><span class="info-label">Mascota:</span> <?= htmlspecialchars($consulta["nombre_mascota"]) ?> (<?= htmlspecialchars($consulta["especie_mascota"]) ?>)</p>
                    <p><span class="info-label">Raza:</span> <?= htmlspecialchars($consulta["raza_mascota"]) ?></p>
                    <p><span class="info-label">Peso:</span> <?= $consulta["peso_kg"] ?> kg | <span class="info-label">Tamaño:</span> <?= htmlspecialchars($consulta["tamano"]) ?></p>
                </div>
            </div>

            <div class="section-title">DETALLES DE LA CONSULTA</div>
            
            <div class="mb-2">
                <span class="info-label">Observación Clínica:</span><br>
                <?= nl2br(htmlspecialchars($consulta["observacion_clinica"])) ?>
            </div>
            
            <div class="mb-2">
                <span class="info-label">Diagnóstico / Conclusión:</span><br>
                <?= nl2br(htmlspecialchars($consulta["conclusion_diagnostico"])) ?>
            </div>

            <div class="section-title">RECETA MÉDICA / TRATAMIENTO</div>
            <div class="receta-box">
                <?= nl2br(htmlspecialchars($consulta["receta"])) ?>
            </div>

            <div class="row mt-3">
                <div class="col-6">
                    <p class="mt-4"><small>Atendido por: Dr/a. <?= htmlspecialchars($consulta["nombre_veterinario"]) ?></small></p>
                </div>
                <div class="col-6">
                    <div class="total-box">
                        TOTAL A PAGAR: $<?= number_format($consulta["costo_servicio"], 2) ?>
                    </div>
                </div>
            </div>

            <div class="firma-section">
                <div class="firma-line">
                    Firma y Sello del Veterinario
                </div>
            </div>

        </div> </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($autoImprimir): ?>
<script>window.addEventListener("load", function () { window.print(); });</script>
<?php endif; ?>
</body>
</html>