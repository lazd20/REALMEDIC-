<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$dbname = 'sitiosnuevos_hospital';
$username = 'sitiosnuevos_cirtugia';
$password = 'Realmedic2020';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO evoluciones (ingreso_id, usuario_id, observacion) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['ingreso_id'],
        $_SESSION['usuario_id'],
        trim($_POST['observacion'])
    ]);

    header("Location: panel_ingresos.php");
    exit;

} catch (PDOException $e) {
    die("❌ Error al guardar: " . $e->getMessage());
}
