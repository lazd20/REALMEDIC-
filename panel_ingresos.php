<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$dbname = 'sitiosnuevos_hospital';
$username = 'sitiosnuevos_cirtugia';
$password = 'Realmedic2020';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT i.*, u.username AS usuario_registro, h.numero AS habitacion_numero 
                         FROM ingresos i 
                         JOIN usuarios u ON i.usuario_id = u.id 
                         LEFT JOIN habitaciones h ON i.habitacion_id = h.id
                         ORDER BY i.fecha_entrada DESC");
    $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Traer habitaciones libres
    $habitaciones_libres = $pdo->query("SELECT * FROM habitaciones WHERE estado = 'libre' ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Ingresos - Realmedic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">🩺 Panel de Ingresos de Pacientes - Realmedic</h2>
        <div>
            <span class="me-3">👤 <?= htmlspecialchars($_SESSION['usuario']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar sesión</a>
        </div>
    </div>

    <?php if ($_SESSION['role'] !== 'viewer'): ?>
        <div class="mb-3 d-flex gap-2">
            <a href="registrar.php" class="btn btn-success">+ Nuevo ingreso</a>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalHabitaciones">🏨 Habitaciones libres</button>
            <a href="reporte_altas.php" class="btn btn-warning">📄 Ver reporte de altas</a>
        </div>
     
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cédula</th>
                    <th>Fecha Entrada</th>
                    <th>Fecha Salida</th>
                    <th>Tratante</th>
                    <th>Tipo Ingreso</th>
                    <th>Habitación</th>
                    <th>Estado</th>
                    <th>Registrado por</th>
                    <?php if ($_SESSION['role'] !== 'viewer'): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingresos as $ingreso): ?>
                    <tr>
                        <td class="text-center"><?= $ingreso['id'] ?></td>
                        <td><?= htmlspecialchars($ingreso['nombre']) ?></td>
                        <td><?= htmlspecialchars($ingreso['apellido']) ?></td>
                        <td><?= htmlspecialchars($ingreso['cedula']) ?></td>
                        <td class="text-center"><?= $ingreso['fecha_entrada'] ?></td>
                        <td class="text-center"><?= $ingreso['fecha_salida'] ?? '<span class="text-muted">—</span>' ?></td>
                        <td><?= htmlspecialchars($ingreso['tratante']) ?></td>
                        <td class="text-center"><?= ucfirst($ingreso['tipo_ingreso']) ?></td>
                        <td class="text-center"><?= $ingreso['habitacion_numero'] ? "Habitación {$ingreso['habitacion_numero']}" : '<span class="text-muted">Emergencia</span>' ?></td>
                        <td class="text-center">
                            <?php
                            $estado = $ingreso['estado'] ?? 'ingresado';
                            switch ($estado) {
                                case 'ingresado':
                                    $badgeClass = 'badge bg-warning text-dark';
                                    break;
                                case 'alta':
                                    $badgeClass = 'badge bg-success';
                                    break;
                                case 'alta a petición':
                                    $badgeClass = 'badge bg-info text-dark';
                                    break;
                                default:
                                    $badgeClass = 'badge bg-secondary';
                                    break;
                            }
                            echo "<span class='$badgeClass'>" . ucfirst($estado) . "</span>";
                            ?>
                        </td>
                        <td class="text-center"><?= htmlspecialchars($ingreso['usuario_registro']) ?></td>

                        <?php if ($_SESSION['role'] !== 'viewer'): ?>
                            <td class="text-center">
                                <?php if ($_SESSION['usuario_id'] == $ingreso['usuario_id']): ?>
                                    <a href="modificar_ingreso.php?id=<?= $ingreso['id'] ?>" class="btn btn-sm btn-primary">Modificar</a>
                                <?php endif; ?>

                                <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#modalEvolucion<?= $ingreso['id'] ?>">Evoluciones</button>

                                <?php if ($estado === 'ingresado'): ?>
                                    <a href="dar_alta.php?id=<?= $ingreso['id'] ?>" class="btn btn-sm btn-success">Dar de alta</a>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>

                    <!-- Modal Evoluciones -->
                    <div class="modal fade" id="modalEvolucion<?= $ingreso['id'] ?>" tabindex="-1" aria-labelledby="evolucionLabel<?= $ingreso['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <form method="POST" action="guardar_evolucion.php">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">📝 Evoluciones de <?= htmlspecialchars($ingreso['nombre']) ?> <?= htmlspecialchars($ingreso['apellido']) ?></h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" name="ingreso_id" value="<?= $ingreso['id'] ?>">
                              <textarea name="observacion" class="form-control mb-3" rows="4" required></textarea>

                              <h6>📋 Historial</h6>
                              <?php
                              $evoStmt = $pdo->prepare("SELECT e.*, u.username FROM evoluciones e JOIN usuarios u ON e.usuario_id = u.id WHERE e.ingreso_id = ? ORDER BY e.fecha DESC");
                              $evoStmt->execute([$ingreso['id']]);
                              $evoluciones = $evoStmt->fetchAll(PDO::FETCH_ASSOC);
                              ?>
                              <div class="list-group" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($evoluciones as $evo): ?>
                                  <div class="list-group-item small">
                                    <strong><?= htmlspecialchars($evo['username']) ?>:</strong>
                                    <span><?= nl2br(htmlspecialchars($evo['observacion'])) ?></span>
                                    <div class="text-muted text-end"><small><?= $evo['fecha'] ?></small></div>
                                  </div>
                                <?php endforeach; ?>
                                <?php if (count($evoluciones) === 0): ?>
                                  <div class="text-muted">Sin evoluciones registradas.</div>
                                <?php endif; ?>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-primary">Guardar evolución</button>
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de habitaciones libres -->
<div class="modal fade" id="modalHabitaciones" tabindex="-1" aria-labelledby="modalHabitacionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalHabitacionesLabel">🛏️ Habitaciones Disponibles</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?php
        try {
            $stmt = $pdo->query("SELECT numero, descripcion FROM habitaciones WHERE estado = 'libre' ORDER BY numero ASC");
            $habitaciones_libres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($habitaciones_libres) > 0): ?>
              <table class="table table-bordered table-striped text-center">
                <thead class="table-light">
                  <tr>
                    <th>Número</th>
                    <th>Descripción</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($habitaciones_libres as $hab): ?>
                    <tr>
                      <td><?= htmlspecialchars($hab['numero']) ?></td>
                      <td><?= htmlspecialchars($hab['descripcion']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="alert alert-warning text-center">
                No hay habitaciones disponibles en este momento.
              </div>
            <?php endif;
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>❌ Error al cargar habitaciones: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
