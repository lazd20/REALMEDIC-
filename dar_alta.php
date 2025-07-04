<?php
session_start();

$host = 'localhost';
$dbname = 'sitiosnuevos_hospital';
$username = 'sitiosnuevos_cirtugia';
$password = 'Realmedic2020';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// Procesar alta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $fecha_salida = $_POST['fecha_salida'];
    $estado = $_POST['estado'];
    $usuario_id = $_SESSION['usuario_id'];

    // 1. Obtener habitacion asignada
    $stmt = $pdo->prepare("SELECT habitacion_id FROM ingresos WHERE id = ?");
    $stmt->execute([$id]);
    $habitacion = $stmt->fetchColumn();

    // 2. Actualizar ingreso
    $stmt = $pdo->prepare("UPDATE ingresos SET fecha_salida = ?, estado = ? WHERE id = ?");
    $stmt->execute([$fecha_salida, $estado, $id]);

    // 3. Liberar habitación si tenía
    if ($habitacion) {
        $pdo->prepare("UPDATE habitaciones SET estado = 'libre' WHERE id = ?")->execute([$habitacion]);
    }

    // 4. Registrar auditoría
    $stmt = $pdo->prepare("INSERT INTO altas_auditoria (ingreso_id, usuario_id, fecha_alta, tipo_alta) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $usuario_id, date('Y-m-d H:i:s'), $estado]);

    header("Location: panel_ingresos.php");
    exit;
}

// Verificar ID de paciente
if (!isset($_GET['id'])) {
    die("ID no especificado.");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM ingresos WHERE id = ?");
$stmt->execute([$id]);
$paciente = $stmt->fetch();

if (!$paciente) {
    die("Paciente no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dar de Alta - Realmedic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4">🩺 Dar de alta al paciente: <?= htmlspecialchars($paciente['nombre']) . " " . htmlspecialchars($paciente['apellido']) ?></h3>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $paciente['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Fecha de salida:</label>
            <input type="date" name="fecha_salida" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo de alta:</label>
            <select name="estado" class="form-select" required>
                <option value="alta">Alta</option>
                <option value="alta a petición">Alta a petición</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Registrar alta</button>
        <a href="panel_ingresos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
