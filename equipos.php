<?php
// Activar mostrar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/conf/functions.php';

// Verificar si es administrador (todos pueden iniciar por ahora)
$es_admin = true;

// Inicializar variables
$mensaje_exito = '';
$mensaje_error = '';

// Procesar inicio del hackathon
if ($es_admin && isset($_POST['iniciar_hackathon'])) {
    try {
        if (iniciarHackathonGlobal()) {
            $mensaje_exito = "¡Hackathon iniciado! Tiempo: 1 hora 30 minutos";
            
            // Iniciar tiempo para todos los equipos existentes que ya tienen miembros
            $equipos = obtenerRankingEquipos();
            foreach ($equipos as $equipo) {
                $miembros = contarMiembrosEquipo($equipo['id']);
                if ($miembros > 0) {
                    // Marcar que estos equipos empezaron desde el inicio
                    global $db;
                    $stmt = $db->prepare("UPDATE equipos SET tiempo_inicio = ?, inicio_tardio = FALSE, estado = 1 WHERE id = ?");
                    $stmt->execute([date('Y-m-d H:i:s'), $equipo['id']]);
                }
            }
        } else {
            $mensaje_error = "Error al iniciar el hackathon";
        }
    } catch (Exception $e) {
        $mensaje_error = "Error: " . $e->getMessage();
    }
}

// Procesar reinicio (para testing)
if ($es_admin && isset($_POST['reiniciar_hackathon'])) {
    try {
        if (reiniciarHackathon()) {
            $mensaje_exito = "Hackathon reiniciado para testing";
        } else {
            $mensaje_error = "Error al reiniciar el hackathon";
        }
    } catch (Exception $e) {
        $mensaje_error = "Error: " . $e->getMessage();
    }
}

// Obtener datos con manejo de errores
try {
    $ranking = obtenerRankingEquipos();
    $config_hackathon = obtenerConfiguracionHackathon();
    $hackathon_activo = hackathonEstaActivo();
    $tiempo_restante = calcularTiempoRestanteGlobal();
} catch (Exception $e) {
    // Si hay error al obtener datos, mostrar página básica
    $ranking = [];
    $config_hackathon = null;
    $hackathon_activo = false;
    $tiempo_restante = 0;
    $mensaje_error = "Error al cargar datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ranking de Equipos - Hackathon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .top-1 { background-color: #FFD700 !important; }
        .top-2 { background-color: #C0C0C0 !important; }
        .top-3 { background-color: #CD7F32 !important; }
        .status-badge { font-size: 0.7rem; }
        .admin-panel { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.075); }
        .error-panel { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; }
        .btn-warning { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none; }
        .badge-espera { background-color: #6c757d; }
        .badge-compitiendo { background-color: #198754; }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="text-center mb-3">
        <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
        <h1>Hackathon UPTPC</h1>
    </div>

    <!-- Panel de Error si hay problemas de base de datos -->
    <?php if (isset($mensaje_error) && strpos($mensaje_error, 'Error al cargar datos') !== false): ?>
    <div class="card error-panel mb-4">
        <div class="card-body text-center">
            <h3 class="card-title">⚠️ Error de Configuración</h3>
            <p class="mb-3"><?php echo $mensaje_error; ?></p>
            <p>Verifica que la base de datos esté configurada correctamente.</p>
            <a href="index.php" class="btn btn-light">Volver al Inicio</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Panel de Administración -->
    <?php if ($es_admin): ?>
    <div class="card admin-panel mb-4">
        <div class="card-body">
            <h3 class="card-title">🎯 Panel de Control del Administrador</h3>
            
            <!-- Estado actual -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Estado del Hackathon: 
                        <?php if ($hackathon_activo): ?>
                            <span class="badge bg-success">EN CURSO</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">NO INICIADO</span>
                        <?php endif; ?>
                    </h5>
                    <?php if ($hackathon_activo): ?>
                        <p class="mb-1">⏰ Tiempo restante: <strong id="tiempo-global" class="fs-4">
                            <?php 
                            $minutos = floor($tiempo_restante / 60);
                            $segundos = $tiempo_restante % 60;
                            echo sprintf("%02d:%02d", $minutos, $segundos);
                            ?>
                        </strong></p>
                        <p class="mb-0">🕐 Iniciado: <?php echo $config_hackathon ? date('H:i:s', strtotime($config_hackathon['tiempo_inicio_global'])) : 'N/A'; ?></p>
                    <?php else: ?>
                        <p class="mb-1">⏳ Duración: <strong>1 hora 30 minutos</strong></p>
                        <p class="mb-0">👥 Equipos listos: <strong><?php echo count($ranking); ?></strong></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <!-- Botones de control -->
                    <div class="d-flex flex-column gap-3">
                        <form method="post" class="d-inline">
                            <?php if (!$hackathon_activo): ?>
                                <button type="submit" name="iniciar_hackathon" class="btn btn-success btn-lg w-100 py-3" 
                                        onclick="return confirm('🚀 ¿Estás seguro de iniciar el hackathon?\\n\\n📅 Duración: 1 hora 30 minutos\\n✅ Todos los equipos existentes comenzarán\\n🎯 Los desafíos se activarán\\n\\n⚠️ Esta acción no se puede deshacer')">
                                    🚀 INICIAR HACKATHON
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-success btn-lg w-100 py-3" disabled>
                                    ✅ HACKATHON EN CURSO
                                </button>
                            <?php endif; ?>
                        </form>
                        
                        <!-- Botón de reinicio (solo para testing) -->
                        <form method="post" class="d-inline">
                            <button type="submit" name="reiniciar_hackathon" class="btn btn-warning btn-lg w-100 py-3" 
                                    onclick="return confirm('⚠️ ¿REINICIAR TODO EL HACKATHON?\\n\\n🔴 Esto borrará:\\n   • Todas las puntuaciones\\n   • Desafíos completados\\n   • Tiempos de equipos\\n   • Estado del hackathon\\n\\n🎯 SOLO PARA TESTING')">
                                🔄 REINICIAR HACKATHON (TESTING)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if ($hackathon_activo): ?>
            <div class="alert alert-warning mb-0">
                <strong>⚠️ Hackathon en progreso</strong> - El tiempo corre para todos los equipos registrados. 
                Los nuevos equipos que se registren deberán esperar al siguiente hackathon.
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">
                <strong>💡 Listo para comenzar</strong> - Cuando inicies el hackathon, todos los equipos existentes comenzarán simultáneamente.
                Asegúrate de que todos los equipos estén registrados antes de iniciar.
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Mensajes de éxito/error -->
    <?php if ($mensaje_exito): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <span class="fs-4 me-2">✅</span>
            <div>
                <h5 class="mb-1">¡Éxito!</h5>
                <p class="mb-0"><?php echo $mensaje_exito; ?></p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($mensaje_error && strpos($mensaje_error, 'Error al cargar datos') === false): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <span class="fs-4 me-2">❌</span>
            <div>
                <h5 class="mb-1">Error</h5>
                <p class="mb-0"><?php echo $mensaje_error; ?></p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Ranking de Equipos -->
    <?php if (!isset($mensaje_error) || strpos($mensaje_error, 'Error al cargar datos') === false): ?>
    <h2 class="text-center mb-4">🏆 Ranking de Equipos</h2>
    
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">Posición</th>
                            <th width="32%">Nombre del Equipo</th>
                            <th width="15%">Código</th>
                            <th width="20%">Puntuación</th>
                            <th width="25%">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ranking)): ?>
                            <?php foreach ($ranking as $index => $equipo): ?>
                            <tr class="<?php 
                                if ($index == 0) { echo 'top-1'; } 
                                elseif ($index == 1) { echo 'top-2'; } 
                                elseif ($index == 2) { echo 'top-3'; } 
                                else { echo ''; } 
                            ?>">
                                <td>
                                    <strong class="fs-5"><?php echo $index + 1; ?>°</strong>
                                    <?php if ($index < 3): ?>
                                        <br>
                                        <span class="badge bg-<?php echo $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger'); ?> mt-1">
                                            <?php echo $index == 0 ? '🥇 ORO' : ($index == 1 ? '🥈 PLATA' : '🥉 BRONCE'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></strong>
                                    <?php if ($equipo['inicio_tardio']): ?>
                                        <br>
                                        <span class="badge bg-info status-badge mt-1" title="Equipo se unió después del inicio">TARDÍO</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code class="fs-5"><?php echo htmlspecialchars($equipo['codigo_equipo']); ?></code>
                                </td>
                                <td>
                                    <strong class="fs-4 text-primary"><?php echo $equipo['puntuacion_total']; ?></strong>
                                    <small class="text-muted">puntos</small>
                                </td>
                                <td>
                                    <?php if ($equipo['estado'] == 1): ?>
                                        <span class="badge badge-compitiendo p-2">🏁 COMPITIENDO</span>
                                    <?php else: ?>
                                        <span class="badge badge-espera p-2">⏳ EN ESPERA</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="alert alert-info">
                                        <h4>📋 No hay equipos registrados aún</h4>
                                        <p class="mb-3">¡Sé el primero en crear un equipo y participar en el hackathon!</p>
                                        <a href="index.php" class="btn btn-primary btn-lg">➕ Crear Primer Equipo</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary btn-lg">
            <?php echo isset($_SESSION['cedula']) ? '🎮 Volver al Dashboard' : 'Volver al inicio de sesion'; ?>
        </a>
        
    </div>
    <?php endif; ?>
</div>

<!-- Script para actualizar el tiempo en tiempo real -->
<?php if ($hackathon_activo): ?>
<script>
function actualizarTiempoGlobal() {
    const tiempoElement = document.getElementById('tiempo-global');
    if (!tiempoElement) return;
    
    let tiempoTexto = tiempoElement.textContent;
    let [minutos, segundos] = tiempoTexto.split(':').map(Number);
    
    // Restar un segundo
    if (segundos === 0) {
        if (minutos === 0) {
            // Tiempo agotado, recargar página
            location.reload();
            return;
        }
        minutos--;
        segundos = 59;
    } else {
        segundos--;
    }
    
    // Actualizar display
    tiempoElement.textContent = 
        `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
}

// Actualizar cada segundo
setInterval(actualizarTiempoGlobal, 1000);
</script>
<?php endif; ?>

</body>
</html>