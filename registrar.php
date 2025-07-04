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

$mensaje = '';
$color = 'danger';

// Conexión y carga de habitaciones disponibles
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $habitaciones = $pdo->query("SELECT * FROM habitaciones WHERE estado = 'libre' ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO ingresos 
            (nombre, apellido, cedula, fecha_entrada, tratante, tipo_ingreso, habitacion_id, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $habitacion_id = !empty($_POST["habitacion_id"]) ? $_POST["habitacion_id"] : null;

        $stmt->execute([
            $_POST["nombre"],
            $_POST["apellido"],
            $_POST["cedula"],
            $_POST["fecha_entrada"],
            $_POST["tratante"],
            $_POST["tipo_ingreso"],
            $habitacion_id,
            $_SESSION["usuario_id"]
        ]);

        // Si se asignó habitación, cambiar su estado a ocupada
        if ($habitacion_id) {
            $update = $pdo->prepare("UPDATE habitaciones SET estado = 'ocupada' WHERE id = ?");
            $update->execute([$habitacion_id]);
        }

        header("Location: panel_ingresos.php");
        exit;

    } catch (PDOException $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Ingreso - Realmedic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="card-title text-center text-primary mb-4">📝 Registro de Ingreso de Paciente</h2>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $color ?> text-center">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nombre:</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Apellido:</label>
                    <input type="text" name="apellido" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cédula:</label>
                    <input type="text" name="cedula" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fecha de entrada:</label>
                    <input type="date" name="fecha_entrada" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Médico tratante:</label>
                    <input type="text" name="tratante" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de ingreso:</label>
                    <select name="tipo_ingreso" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="cirugia programada">Cirugía programada</option>
                        <option value="emergencia">Emergencia</option>
                        <option value="hospitalización">Hospitalización</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Habitación (opcional):</label>
                    <select name="habitacion_id" class="form-select">
                        <option value="">-- En emergencia o sin habitación --</option>
                        <?php foreach ($habitaciones as $hab): ?>
                            <option value="<?= $hab['id'] ?>">Habitación <?= $hab['numero'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Registrar ingreso</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>