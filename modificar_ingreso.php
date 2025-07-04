<?php
$host = 'localhost';
$dbname = 'sitiosnuevos_hospital';
$username = 'sitiosnuevos_cirtugia';
$password = 'Realmedic2020';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE ingresos SET nombre=?, apellido=?, cedula=?, fecha_entrada=?, tratante=?, tipo_ingreso=? WHERE id=?");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['cedula'],
        $_POST['fecha_entrada'],
        $_POST['tratante'],
        $_POST['tipo_ingreso'],
        $_POST['id']
    ]);
    header("Location: panel_ingresos.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID no especificado.");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM ingresos WHERE id = ?");
$stmt->execute([$id]);
$ingreso = $stmt->fetch();

if (!$ingreso) {
    die("Ingreso no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Ingreso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="mb-4 text-primary">✏️ Modificar ingreso de <?= htmlspecialchars($ingreso['nombre']) ?> <?= htmlspecialchars($ingreso['apellido']) ?></h3>

            <form method="POST">
                <input type="hidden" name="id" value="<?= $ingreso['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Nombre:</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($ingreso['nombre']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Apellido:</label>
                    <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($ingreso['apellido']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cédula:</label>
                    <input type="text" name="cedula" class="form-control" value="<?= htmlspecialchars($ingreso['cedula']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fecha de entrada:</label>
                    <input type="date" name="fecha_entrada" class="form-control" value="<?= $ingreso['fecha_entrada'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Médico tratante:</label>
                    <input type="text" name="tratante" class="form-control" value="<?= htmlspecialchars($ingreso['tratante']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de ingreso:</label>
                    <select name="tipo_ingreso" class="form-select" required>
                        <option value="cirugia programada" <?= $ingreso['tipo_ingreso'] === 'cirugia programada' ? 'selected' : '' ?>>Cirugía programada</option>
                        <option value="emergencia" <?= $ingreso['tipo_ingreso'] === 'emergencia' ? 'selected' : '' ?>>Emergencia</option>
                        <option value="hospitalización" <?= $ingreso['tipo_ingreso'] === 'hospitalización' ? 'selected' : '' ?>>Hospitalización</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="panel_ingresos.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>