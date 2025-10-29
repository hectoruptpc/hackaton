<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

// Verificar si es administrador (todos pueden iniciar por ahora)
$es_admin = true;

// Procesar inicio del hackathon
if ($es_admin && isset($_POST['iniciar_hackathon'])) {
    if (iniciarHackathonGlobal()) {
        $mensaje_exito = "¡Hackathon iniciado! Tiempo: 1 hora 30 minutos";
    } else {
        $mensaje_error = "Error al iniciar el hackathon";
    }
}

// Procesar reinicio (para testing)
if ($es_admin && isset($_POST['reiniciar_hackathon'])) {
    if (reiniciarHackathon()) {
        $mensaje_exito = "Hackathon reiniciado para testing";
    } else {
        $mensaje_error = "Error al reiniciar el hackathon";
    }
}

// Obtener ranking de equipos
function obtenerRankingEquipos() {
    global $db;
    $stmt = $db->prepare("SELECT nombre_equipo, codigo_equipo, puntuacion_total, tiempo_inicio, inicio_tardio FROM equipos ORDER BY puntuacion_total DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$ranking = obtenerRankingEquipos();
$config_hackathon = obtenerConfiguracionHackathon();
$hackathon_activo = hackathonEstaActivo();
$tiempo_restante = calcularTiempoRestanteGlobal();
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
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="text-center mb-3">
        <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
        <h1>Hackathon UPTPC</h1>
    </div>

    <!-- Panel de Administración -->
    <?php if ($es_admin): ?>
    <div class="card admin-panel mb-4">
        <div class="card-body">
            <h3 class="card-title">Panel de Control del Hackathon</h3>
            
            <!-- Estado actual -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Estado: 
                        <?php if ($hackathon_activo): ?>
                            <span class="badge bg-success">EN CURSO</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">NO INICIADO</span>
                        <?php endif; ?>
                    </h5>
                    <?php if ($hackathon_activo): ?>
                        <p class="mb-1">Tiempo restante: <strong id="tiempo-global">
                            <?php 
                            $minutos = floor($tiempo_restante / 60);
                            $segundos = $tiempo_restante % 60;
                            echo sprintf("%02d:%02d", $minutos, $segundos);
                            ?>
                        </strong></p>
                        <p class="mb-0">Iniciado: <?php echo date('H:i:s', strtotime($config_hackathon['tiempo_inicio_global'])); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <!-- Botones de control -->
                    <form method="post" class="d-inline">
                        <?php if (!$hackathon_activo): ?>
                            <button type="submit" name="iniciar_hackathon" class="btn btn-success btn-lg me-2" 
                                    onclick="return confirm('¿Estás seguro de iniciar el hackathon?\\n\\nDuración: 1 hora 30 minutos\\nTodos los niveles se activarán.')">
                                🚀 Iniciar Hackathon
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success btn-lg me-2" disabled>
                                ✅ Hackathon en Curso
                            </button>
                        <?php endif; ?>
                        
                        <!-- Botón de reinicio (solo para testing) -->
                        <button type="submit" name="reiniciar_hackathon" class="btn btn-warning btn-sm" 
                                onclick="return confirm('⚠️ ¿Reiniciar todo el hackathon?\\n\\nEsto borrará todas las puntuaciones y desafíos completados.\\nSolo para testing.')">
                            🔄 Reiniciar (Testing)
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if ($hackathon_activo): ?>
            <div class="alert alert-warning mb-0">
                <strong>⚠️ Hackathon en progreso</strong> - El tiempo corre para todos los equipos. 
                Los nuevos equipos empezarán con el tiempo que lleve activo el hackathon.
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Mensajes de éxito/error -->
    <?php if (isset($mensaje_exito)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?php echo $mensaje_exito; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($mensaje_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ❌ <?php echo $mensaje_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Ranking de Equipos -->
    <h2 class="text-center mb-4">Ranking de Equipos</h2>
    
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">Posición</th>
                            <th width="35%">Nombre del Equipo</th>
                            <th width="15%">Código</th>
                            <th width="20%">Puntuación</th>
                            <th width="22%">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking as $index => $equipo): 
                            $tiempo_equipo = $equipo['tiempo_inicio'] ? calcularTiempoTranscurrido($equipo['tiempo_inicio']) : 0;
                        ?>
                        <tr class="<?php echo $index == 0 ? 'top-1' : ($index == 1 ? 'top-2' : ($index == 2 ? 'top-3' : '')); ?>">
                            <td>
                                <strong><?php echo $index + 1; ?>°</strong>
                                <?php if ($index < 3): ?>
                                    <span class="badge bg-<?php echo $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger'); ?>">
                                        <?php echo $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($equipo['nombre_equipo']); ?>
                                <?php if ($equipo['inicio_tardio']): ?>
                                    <span class="badge bg-info status-badge" title="Equipo se unió después del inicio">TARDÍO</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo htmlspecialchars($equipo['codigo_equipo']); ?></code></td>
                            <td>
                                <strong class="fs-5"><?php echo $equipo['puntuacion_total']; ?></strong>
                                <small class="text-muted">puntos</small>
                            </td>
                            <td>
                                <?php if ($equipo['tiempo_inicio']): ?>
                                    <?php 
                                    $horas = floor($tiempo_equipo / 3600);
                                    $minutos = floor(($tiempo_equipo % 3600) / 60);
                                    $segundos = $tiempo_equipo % 60;
                                    ?>
                                    <span class="badge bg-success">Activo</span>
                                    <small class="text-muted"><?php echo sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos); ?></small>
                                <?php else: ?>
                                    <span class="badge bg-secondary">En espera</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($ranking)): ?>
            <div class="alert alert-info text-center">
                <h4>No hay equipos registrados aún</h4>
                <p>¡Sé el primero en crear un equipo!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary btn-lg">
            <?php echo isset($_SESSION['cedula']) ? 'Volver al Dashboard' : 'Crear Equipo'; ?>
        </a>
        
        <?php if ($hackathon_activo): ?>
            <a href="index.php" class="btn btn-success btn-lg ms-2">
                <?php echo isset($_SESSION['cedula']) ? 'Continuar Compitiendo' : 'Unirse al Hackathon'; ?>
            </a>
        <?php endif; ?>
    </div>
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