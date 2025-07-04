<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Verificar que haya sesión iniciada
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Sistema de Residentes Realmedic</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar con usuario y logout -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-text text-white">
            👤 Conectado como: <strong><?= $_SESSION['usuario'] ?></strong> (<?= $_SESSION['role'] ?>)
        </span>
        <a href="logout.php" class="btn btn-outline-light ms-auto">Cerrar sesión</a>
    </div>
</nav>

<!-- Contenido principal -->
<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-primary">🏥 Sistema de Gestión de Ingresos - Realmedic</h2>

        <p class="lead">Bienvenido al sistema. Selecciona una opción para continuar:</p>

        <div class="d-grid gap-3 col-md-6 mx-auto">
            <a href="registrar.php" class="btn btn-outline-primary btn-lg">➕ Registrar nuevo ingreso</a>
            <a href="panel_ingresos.php" class="btn btn-outline-success btn-lg">📋 Ver panel de ingresos</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="usuarios.php" class="btn btn-outline-warning btn-lg">👥 Gestión de usuarios</a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
