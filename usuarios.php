<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$host = 'localhost';
$dbname = 'sitiosnuevos_hospital';
$username = 'sitiosnuevos_cirtugia';
$password = 'Realmedic2020';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear nuevo usuario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
        $nuevoUser = trim($_POST['username']);
        $nuevoPass = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $nuevoRol = $_POST['role'];

        $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$nuevoUser, $nuevoPass, $nuevoRol]);
        header("Location: usuarios.php");
        exit;
    }

    // Actualizar usuario existente
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
        $id = $_POST['id'];
        $nuevoRol = $_POST['role'];
        $nuevaClave = trim($_POST['password']);

        if (!empty($nuevaClave)) {
            $claveHash = password_hash($nuevaClave, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios SET role = ?, password = ? WHERE id = ?");
            $stmt->execute([$nuevoRol, $claveHash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET role = ? WHERE id = ?");
            $stmt->execute([$nuevoRol, $id]);
        }

        header("Location: usuarios.php");
        exit;
    }

    // Eliminar
    if (isset($_GET['eliminar'])) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$_GET['eliminar']]);
        header("Location: usuarios.php");
        exit;
    }

    $usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">👥 Gestión de Usuarios</h2>

    <!-- Crear usuario -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="username" class="form-control" placeholder="Nuevo usuario" required>
        </div>
        <div class="col-md-4">
            <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
        </div>
        <div class="col-md-2">
            <select name="role" class="form-select" required>
                <option value="">Rol</option>
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
                <option value="viewer">Viewer</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" name="crear" class="btn btn-success w-100">+ Crear</button>
        </div>
    </form>

    <!-- Lista editable -->
    <table class="table table-bordered table-hover text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Contraseña nueva</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $user): ?>
                <tr>
                    <form method="POST">
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td>
                            <select name="role" class="form-select form-select-sm">
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                <option value="viewer" <?= $user['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                            </select>
                        </td>
                        <td>
                            <input type="password" name="password" class="form-control form-control-sm" placeholder="(sin cambios)">
                        </td>
                        <td>
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" name="guardar" class="btn btn-sm btn-primary">💾 Guardar</button>
                            <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                <a href="?eliminar=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este usuario?')">🗑 Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="index.php" class="btn btn-secondary mt-4">← Volver al Panel</a>
</div>
</body>
</html>