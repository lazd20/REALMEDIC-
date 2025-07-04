<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$dbname = 'sitiosnuevos_hospital';
$username = 'sitiosnuevos_cirtugia';
$password = 'Realmedic2020';

$fechaSeleccionada = $_GET['fecha'] ?? date('Y-m-d');
$altas = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT i.*, u.username AS usuario_registro
                          FROM ingresos i
                          JOIN usuarios u ON i.usuario_id = u.id
                          WHERE i.fecha_salida = :fecha
                          ORDER BY i.fecha_salida DESC");
    $stmt->bindParam(':fecha', $fechaSeleccionada);
    $stmt->execute();
    $altas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Altas - Realmedic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4 text-primary">📋 Reporte de Altas - <?= date('d/m/Y', strtotime($fechaSeleccionada)) ?></h2>

    <form class="row g-3 mb-4" method="GET">
        <div class="col-auto">
            <label for="fecha" class="col-form-label">Seleccionar fecha:</label>
        </div>
        <div class="col-auto">
            <input type="date" name="fecha" id="fecha" class="form-control" value="<?= $fechaSeleccionada ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Ver altas</button>
            <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
        </div>
    </form>

    <?php if (count($altas) > 0): ?>

        <p class="text-end text-muted fst-italic mb-2 d-print-block">
            Generado por: <?= htmlspecialchars($_SESSION['usuario']) ?> el <?= date('d/m/Y H:i:s') ?>
        </p>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Cédula</th>
                        <th>Fecha Entrada</th>
                        <th>Fecha Salida</th>
                        <th>Tipo Ingreso</th>
                        <th>Tratante</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($altas as $alta): ?>
                        <tr>
                            <td class="text-center"><?= $alta['id'] ?></td>
                            <td><?= htmlspecialchars($alta['nombre']) ?></td>
                            <td><?= htmlspecialchars($alta['apellido']) ?></td>
                            <td><?= htmlspecialchars($alta['cedula']) ?></td>
                            <td class="text-center"><?= $alta['fecha_entrada'] ?></td>
                            <td class="text-center"><?= $alta['fecha_salida'] ?></td>
                            <td class="text-center"><?= ucfirst($alta['tipo_ingreso']) ?></td>
                            <td><?= htmlspecialchars($alta['tratante']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($alta['usuario_registro']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            No se encontraron altas para la fecha seleccionada.
        </div>
    <?php endif; ?>
</div>
</body>
</html>