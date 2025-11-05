<?php
// Activar mostrar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/conf/functions.php';

// Verificar autenticación administrativa
if (!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true) {
    header("Location: index.php");
    exit;
}

// Verificar si es administrador
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
            // Resetear variables de sesión para sonidos
            unset($_SESSION['banderas_reproducidas']);
            unset($_SESSION['max_puntuacion_global']);
        } else {
            $mensaje_error = "Error al reiniciar el hackathon";
        }
    } catch (Exception $e) {
        $mensaje_error = "Error: " . $e->getMessage();
    }
}

// Procesar eliminación de equipo
if ($es_admin && isset($_POST['eliminar_equipo'])) {
    try {
        $equipo_id = $_POST['equipo_id'];
        if (eliminarEquipo($equipo_id)) {
            $mensaje_exito = "Equipo eliminado exitosamente";
        } else {
            $mensaje_error = "Error al eliminar el equipo";
        }
    } catch (Exception $e) {
        $mensaje_error = "Error: " . $e->getMessage();
    }
}

// Obtener datos con manejo de errores
try {
    $ranking = obtenerRankingEquiposConTiempo();
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

// Inicializar el último ID conocido en la sesión
if (!isset($_SESSION['ultimo_equipo_id'])) {
    // Establecer el último ID como el ID más alto actual
    $ultimo_id = 0;
    if (!empty($ranking)) {
        $ultimo_id = max(array_column($ranking, 'id'));
    }
    $_SESSION['ultimo_equipo_id'] = $ultimo_id;
}

// Inicializar timestamp de verificación de puntuaciones
if (!isset($_SESSION['ultima_verificacion_puntuaciones'])) {
    $_SESSION['ultima_verificacion_puntuaciones'] = date('Y-m-d H:i:s');
}

// Inicializar timestamp de verificación de tiempos
if (!isset($_SESSION['ultima_verificacion_tiempo'])) {
    $_SESSION['ultima_verificacion_tiempo'] = date('Y-m-d H:i:s');
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
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; }
        .badge-espera { background-color: #6c757d; }
        .badge-compitiendo { background-color: #198754; }
        .actions-column { width: 120px; }
        
        /* TEMPORIZADOR MÁS GRANDE */
        .temporizador-grande {
            font-size: 4rem !important;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            padding: 10px 20px;
            border-radius: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: inline-block;
            margin: 10px 0;
        }
        
        /* Estados del temporizador */
        .temporizador-normal { color: #28a745; }
        .temporizador-advertencia { color: #ffc107; animation: pulse 1s infinite; }
        .temporizador-peligro { color: #dc3545; animation: pulse 0.5s infinite; }
        
        /* Efectos para nuevos equipos */
        .equipo-nuevo {
            animation: highlight 2s ease-in-out;
            background-color: #d4edda !important;
        }
        
        .badge-nuevo {
            background-color: #17a2b8;
            animation: blink 1s infinite;
        }
        
        /* Notificación flotante */
        .notificacion-flotante {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.5s ease-out;
        }
        
        /* Estilos para equipos ganadores */
        .primer-lugar-tabla {
            border: 3px solid #FFD700 !important;
            background: linear-gradient(135deg, #FFF9C4 0%, #FFEB3B 100%) !important;
            animation: pulse 2s infinite;
        }

        .segundo-lugar-tabla {
            border: 3px solid #C0C0C0 !important;
            background: linear-gradient(135deg, #F5F5F5 0%, #E0E0E0 100%) !important;
            animation: pulse 2s infinite;
        }

        .tercer-lugar-tabla {
            border: 3px solid #CD7F32 !important;
            background: linear-gradient(135deg, #FFE0B2 0%, #FFB74D 100%) !important;
            animation: pulse 2s infinite;
        }

        .ganador-parcial-tabla {
            border: 3px solid #28a745 !important;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
        }

        .empate-tabla {
            border: 3px solid #ffc107 !important;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
        }

        .equipo-completo {
            border: 2px solid #28a745 !important;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
            animation: pulse 2s infinite;
            position: relative;
        }

        .equipo-completo::after {
            content: "✅ COMPLETO";
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* Podio items */
        .podio-item {
            padding: 20px;
            border-radius: 15px;
            margin: 10px 0;
        }

        .primer-lugar {
            background: rgba(255, 215, 0, 0.2);
            border: 3px solid #FFD700;
        }

        .segundo-lugar {
            background: rgba(192, 192, 192, 0.2);
            border: 3px solid #C0C0C0;
        }

        .tercer-lugar {
            background: rgba(205, 127, 50, 0.2);
            border: 3px solid #CD7F32;
        }

        /* Animaciones para cambios de puntuación */
        .puntuacion-cambiando {
            animation: pulse 0.5s ease-in-out 3;
        }
        
        @keyframes highlight-change {
            0% { background-color: #fff3cd; }
            100% { background-color: transparent; }
        }
        
        .fila-actualizada {
            animation: highlight-change 2s ease-in-out;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-30px);}
            60% {transform: translateY(-15px);}
        }
        @keyframes pulse {
            0%, 100% {transform: scale(1);}
            50% {transform: scale(1.1);}
        }
        @keyframes highlight {
            0% { background-color: #d4edda; }
            100% { background-color: transparent; }
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: #f00;
            animation: fall linear forwards;
        }
        @keyframes fall {
            to {transform: translateY(100vh);}
        }
        .modal-danger .modal-content {
            border: 3px solid #dc3545;
        }
        
        /* Efectos para tiempo cambiando */
        .tiempo-cambiando {
            animation: pulse 0.5s ease-in-out 3;
            background-color: #e3f2fd !important;
        }

        @keyframes highlight-time {
            0% { background-color: #e3f2fd; }
            100% { background-color: transparent; }
        }

        .fila-tiempo-actualizado {
            animation: highlight-time 2s ease-in-out;
        }
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
                        <!-- TEMPORIZADOR MÁS GRANDE -->
                        <div class="text-center mb-3">
                            <p class="mb-1">⏰ Tiempo restante:</p>
                            <div id="tiempo-global" class="temporizador-grande temporizador-normal">
                                <?php 
                                $minutos = floor($tiempo_restante / 60);
                                $segundos = $tiempo_restante % 60;
                                echo sprintf("%02d:%02d", $minutos, $segundos);
                                ?>
                            </div>
                            <small class="text-muted">Tiempo global del hackathon</small>
                        </div>
                       
                    <?php else: ?>
                        <p class="mb-1">⏳ Duración: <strong>1 hora 30 minutos</strong></p>
                        <p class="mb-0">👥 Equipos registrados: <strong id="total-equipos"><?php echo count($ranking); ?></strong></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <!-- Botones de control -->
                    <div class="d-flex flex-column gap-3">
                        <!-- Botón Iniciar Hackathon -->
                        <?php if (!$hackathon_activo): ?>
                            <button type="button" class="btn btn-success btn-lg w-100 py-3" data-bs-toggle="modal" data-bs-target="#iniciarModal">
                                🚀 INICIAR HACKATHON
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-success btn-lg w-100 py-3" disabled>
                                ✅ HACKATHON EN CURSO
                            </button>
                        <?php endif; ?>
                        
                        <!-- Botón Reiniciar Hackathon -->
                        <button type="button" class="btn btn-warning btn-lg w-100 py-3" data-bs-toggle="modal" data-bs-target="#reiniciarModal">
                            🔄 REINICIAR HACKATHON (TESTING)
                        </button>
                    </div>
                    
                </div>
            </div>
            
            <?php if ($hackathon_activo): ?>
            <div class="alert alert-warning mb-0">
                <strong>⚠️ Hackathon en progreso</strong> - El tiempo corre para todos los equipos registrados. 
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">
                <strong>💡 Listo para comenzar</strong> - Cuando inicies el hackathon, todos los equipos existentes comenzarán simultáneamente.
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

    <?php if ($mensaje_error): ?>
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
    <h2 class="text-center mb-4">🏆 Ranking de Equipos</h2>
    
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">Posición</th>
                            <th width="25%">Nombre del Equipo</th>
                            <th width="12%">Código</th>
                            <th width="12%">Puntuación</th>
                            <th width="15%">Tiempo</th>
                            <th width="18%">Estado</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-equipos">
                        <?php if (!empty($ranking)): ?>
                            <?php foreach ($ranking as $index => $equipo): ?>
                            <tr class="<?php 
                                if ($index == 0) { echo 'top-1'; } 
                                elseif ($index == 1) { echo 'top-2'; } 
                                elseif ($index == 2) { echo 'top-3'; } 
                                else { echo ''; } 
                            ?>" data-equipo-id="<?php echo $equipo['id']; ?>">
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
                                    <small class="text-muted">🚩</small>
                                </td>
                                <td>
                                    <?php if ($equipo['tiempo_acumulado'] > 0): ?>
                                        <?php
                                        $minutos = floor($equipo['tiempo_acumulado'] / 60);
                                        $segundos = $equipo['tiempo_acumulado'] % 60;
                                        echo sprintf("%02d:%02d", $minutos, $segundos);
                                        ?>
                                        <?php if ($equipo['completado']): ?>
                                            <br><small class="text-success">✅ Completado</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">--:--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($equipo['estado'] == 1): ?>
                                        <span class="badge badge-compitiendo p-2">🏁 COMPITIENDO</span>
                                    <?php else: ?>
                                        <span class="badge badge-espera p-2">⏳ EN ESPERA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center actions-column">
                                    <!-- Botón Eliminar -->
                                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-equipo" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#eliminarModal"
                                            data-equipo-id="<?php echo $equipo['id']; ?>"
                                            data-equipo-nombre="<?php echo htmlspecialchars($equipo['nombre_equipo']); ?>"
                                            title="Eliminar equipo">
                                        🗑️ Eliminar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="alert alert-info">
                                        <h4>📋 No hay equipos registrados aún</h4>
                                        <p class="mb-3">¡Sé el primero en crear un equipo y participar en el hackathon!</p>
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
            <?php echo isset($_SESSION['cedula']) ? '🎮 Volver al Dashboard' : 'Volver al inicio de sesión'; ?>
        </a>
    </div>
</div>

<!-- Modal para Iniciar Hackathon -->
<div class="modal fade" id="iniciarModal" tabindex="-1" aria-labelledby="iniciarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="iniciarModalLabel">🚀 Iniciar Hackathon</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de iniciar el hackathon?</p>
                <div class="alert alert-info">
                    <strong>📅 Duración:</strong> 1 hora 30 minutos<br>
                    <strong>✅ Equipos que comenzarán:</strong> <?php echo count($ranking); ?><br>
                    <strong>🎯 Desafíos:</strong> 6 desafíos de seguridad
                </div>
                <p class="text-danger"><strong>⚠️ Esta acción no se puede deshacer</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" class="d-inline">
                    <button type="submit" name="iniciar_hackathon" class="btn btn-success">🚀 Iniciar Hackathon</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Reiniciar Hackathon -->
<div class="modal fade" id="reiniciarModal" tabindex="-1" aria-labelledby="reiniciarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-danger">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="reiniciarModalLabel">🔄 Reiniciar Hackathon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold">¿REINICIAR TODO EL HACKATHON?</p>
                <div class="alert alert-danger">
                    <strong>🔴 Esto borrará:</strong><br>
                    • Todas las puntuaciones<br>
                    • Desafíos completados<br>
                    • Tiempos de equipos<br>
                    • Estado del hackathon
                </div>
                <p class="text-warning"><strong>🎯 SOLO PARA TESTING</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" class="d-inline">
                    <button type="submit" name="reiniciar_hackathon" class="btn btn-warning">🔄 Reiniciar Hackathon</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Eliminar Equipo -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="eliminarModalLabel">🗑️ Eliminar Equipo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold">¿ESTÁS SEGURO DE ELIMINAR EL EQUIPO?</p>
                <div class="alert alert-danger">
                    <strong id="equipoNombreEliminar"></strong><br><br>
                    <strong>❌ Esta acción eliminará:</strong><br>
                    • Todos los miembros del equipo<br>
                    • Puntuaciones y progreso<br>
                    • Desafíos completados
                </div>
                <p class="text-danger"><strong>🚫 Esta acción NO se puede deshacer</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" id="formEliminarEquipo" class="d-inline">
                    <input type="hidden" name="equipo_id" id="equipoIdEliminar">
                    <button type="submit" name="eliminar_equipo" class="btn btn-danger">🗑️ Eliminar Equipo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Podio Completo (Cuando equipos completan los 6 desafíos) -->
<div class="modal fade" id="podioCompletoModal" tabindex="-1" aria-labelledby="podioCompletoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%);">
            <div class="modal-header border-0">
                <h2 class="modal-title text-center w-100 text-white" id="podioCompletoModalLabel">
                    🏆 PODIO OFICIAL 🏆
                </h2>
            </div>
            <div class="modal-body text-center">
                <div class="fs-1 mb-3">🎉 ¡FELICITACIONES! 🎉</div>
                <h4 class="text-white mb-4">Equipos que completaron los 6 desafíos</h4>
                <div id="podioList">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ganador por Tiempo (Cuando se acaba el tiempo y nadie completó todo) -->
<div class="modal fade" id="ganadorTiempoModal" tabindex="-1" aria-labelledby="ganadorTiempoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
            <div class="modal-header border-0">
                <h2 class="modal-title text-center w-100">⏰ TIEMPO AGOTADO</h2>
            </div>
            <div class="modal-body text-center">
                <div class="fs-1 mb-3">🎯</div>
                <h3>GANADOR POR PUNTUACIÓN</h3>
                <h2 id="ganadorTiempoNombre" class="fw-bold"></h2>
                <h4 class="text-warning" id="ganadorTiempoPuntos"></h4>
                <p class="mt-3">Mayor puntuación obtenida</p>
                <div class="alert alert-warning text-dark mt-3">
                    <strong>Nota:</strong> Ningún equipo completó los 6 desafíos
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Empate por Tiempo (Cuando se acaba el tiempo y hay empate) -->
<div class="modal fade" id="empateTiempoModal" tabindex="-1" aria-labelledby="empateTiempoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white;">
            <div class="modal-header border-0">
                <h2 class="modal-title text-center w-100">⏰ TIEMPO AGOTADO</h2>
            </div>
            <div class="modal-body text-center">
                <div class="fs-1 mb-3">⚖️</div>
                <h3>EMPATE EN PUNTUACIÓN</h3>
                <h4>Múltiples equipos con <span class="text-dark" id="puntuacionEmpateTiempo"></span></h4>
                <div id="listaEmpateTiempo" class="my-4">
                    <!-- Se llena dinámicamente -->
                </div>
                <div class="mt-4">
                    <button type="button" class="btn btn-danger btn-lg" id="btnIniciarDesempateTiempo">
                        🏆 INICIAR DESEMPATE
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Todos Fallaron -->
<div class="modal fade" id="todosFallaronModal" tabindex="-1" aria-labelledby="todosFallaronModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white;">
            <div class="modal-header border-0">
                <h2 class="modal-title text-center w-100">😔 RESULTADOS</h2>
            </div>
            <div class="modal-body text-center">
                <div class="fs-1 mb-3">💀</div>
                <h3>NINGÚN EQUIPO</h3>
                <h2 class="text-warning">LOGRO PUNTUAR</h2>
                <p class="mt-3">Los desafíos fueron muy desafiantes esta vez</p>
                <div class="alert alert-info mt-3">
                    <strong>🏆 Mejor suerte para la próxima</strong>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Audio para el sonido de finalización -->
<audio id="finishSound" preload="auto">
    <source src="aplausos.mp3" type="audio/mpeg">
    Tu navegador no soporta el elemento de audio.
</audio>

<!-- Audio para las banderas capturadas -->
<audio id="audioBandera1" preload="auto">
    <source src="estamos_siendo_atacados.mp3" type="audio/mpeg">
</audio>
<audio id="audioBandera2" preload="auto">
    <source src="nuestras_defensas_estan_cayendo.mp3" type="audio/mpeg">
</audio>
<audio id="audioBandera3" preload="auto">
    <source src="tumbaron_la_mitad_de_nuestras_defensas.mp3" type="audio/mpeg">
</audio>
<audio id="audioBandera4" preload="auto">
    <source src="si_no_hacemos_algo_todo_se_vendra_abajo.mp3" type="audio/mpeg">
</audio>
<audio id="audioBandera5" preload="auto">
    <source src="solo_nos_queda_una_defensa_que_no_avancen.mp3" type="audio/mpeg">
</audio>

<!-- Audio para victoria -->
<audio id="audioVictoria" preload="auto">
    <source src="aplausos.mp3" type="audio/mpeg">
</audio>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Variables globales
let tiempoRestante = <?php echo $tiempo_restante; ?>;
let tiempoAgotadoMostrado = false;
let sonidoReproducido = false;
let equiposActuales = new Map();
let podioCompletoMostrado = false;
let resultadoTiempoMostrado = false;

// Variables globales para sonidos
let banderasReproducidas = new Set(); // Para evitar repetir sonidos
let maxPuntuacionGlobal = 0; // Para trackear la máxima puntuación alcanzada
const audiosBanderas = {
    1: document.getElementById('audioBandera1'),
    2: document.getElementById('audioBandera2'), 
    3: document.getElementById('audioBandera3'),
    4: document.getElementById('audioBandera4'),
    5: document.getElementById('audioBandera5')
};
const audioVictoria = document.getElementById('audioVictoria');

// Constantes
const PUNTUACION_MAXIMA = 6; // 6 desafíos completados

// Elementos del DOM
const tiempoElement = document.getElementById('tiempo-global');
const finishSound = document.getElementById('finishSound');
const totalEquiposElement = document.getElementById('total-equipos');
const tablaEquipos = document.getElementById('tabla-equipos');

// ===== SISTEMA DE DETERMINACIÓN DE RESULTADOS =====

// Función para obtener los datos actualizados del ranking
function obtenerRankingActualizado() {
    return fetch('obtener_ranking_actual.php?t=' + Date.now())
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data;
            } else {
                throw new Error(data.error || 'Error al obtener ranking actualizado');
            }
        });
}

// Función para verificar si hay equipos que completaron los 6 desafíos
function verificarPodioCompleto(ranking) {
    if (!ranking || ranking.length === 0) return null;
    
    // Filtrar equipos que completaron todos los desafíos
    const equiposCompletos = ranking.filter(equipo => equipo.completado === true || equipo.puntuacion_total === PUNTUACION_MAXIMA);
    
    if (equiposCompletos.length === 0) {
        return null;
    }
    
    // Ordenar por tiempo acumulado (más rápido primero) - DESEMPATE POR TIEMPO
    const equiposOrdenados = equiposCompletos.sort((a, b) => {
        // Primero por puntuación (descendente)
        if (b.puntuacion_total !== a.puntuacion_total) {
            return b.puntuacion_total - a.puntuacion_total;
        }
        // Luego por tiempo acumulado (ascendente) - desempate
        return a.tiempo_acumulado - b.tiempo_acumulado;
    });
    
    // Asignar posiciones del podio considerando empates por tiempo
    const podio = {
        tipo: 'podio_completo',
        primero: equiposOrdenados[0],
        segundo: null,
        tercero: null,
        otros: [],
        empates: []
    };
    
    // Verificar empates para primer lugar
    const primerPuntuacion = podio.primero.puntuacion_total;
    const primerTiempo = podio.primero.tiempo_acumulado;
    const empatesPrimero = equiposOrdenados.filter(equipo => 
        equipo.puntuacion_total === primerPuntuacion && 
        equipo.tiempo_acumulado === primerTiempo
    );
    
    if (empatesPrimero.length > 1) {
        podio.empates.push({ posicion: 1, equipos: empatesPrimero });
    }
    
    // Buscar segundo lugar (excluyendo los empatados en primer lugar)
    const equiposRestantes = equiposOrdenados.filter(equipo => 
        !empatesPrimero.includes(equipo)
    );
    
    if (equiposRestantes.length > 0) {
        podio.segundo = equiposRestantes[0];
        
        // Verificar empates para segundo lugar
        const segundoPuntuacion = podio.segundo.puntuacion_total;
        const segundoTiempo = podio.segundo.tiempo_acumulado;
        const empatesSegundo = equiposRestantes.filter(equipo => 
            equipo.puntuacion_total === segundoPuntuacion && 
            equipo.tiempo_acumulado === segundoTiempo
        );
        
        if (empatesSegundo.length > 1) {
            podio.empates.push({ posicion: 2, equipos: empatesSegundo });
        }
        
        // Buscar tercer lugar (excluyendo los empatados en primer y segundo lugar)
        const equiposParaTercero = equiposRestantes.filter(equipo => 
            !empatesSegundo.includes(equipo)
        );
        
        if (equiposParaTercero.length > 0) {
            podio.tercero = equiposParaTercero[0];
            
            // Verificar empates para tercer lugar
            const tercerPuntuacion = podio.tercero.puntuacion_total;
            const tercerTiempo = podio.tercero.tiempo_acumulado;
            const empatesTercero = equiposParaTercero.filter(equipo => 
                equipo.puntuacion_total === tercerPuntuacion && 
                equipo.tiempo_acumulado === tercerTiempo
            );
            
            if (empatesTercero.length > 1) {
                podio.empates.push({ posicion: 3, equipos: empatesTercero });
            }
            
            // Los demás equipos completos
            podio.otros = equiposParaTercero.slice(1).filter(equipo => 
                !empatesTercero.includes(equipo)
            );
        }
    }
    
    return podio;
}

// Función para determinar resultado cuando se acaba el tiempo
function determinarResultadoTiempo(ranking) {
    if (!ranking || ranking.length === 0) return null;
    
    const maxPuntuacion = ranking[0].puntuacion_total;
    
    // Si el máximo es 0, todos fallaron
    if (maxPuntuacion === 0) {
        return { tipo: 'todos_fallaron' };
    }
    
    // Buscar equipos con la máxima puntuación
    const equiposMaxPuntuacion = ranking.filter(equipo => equipo.puntuacion_total === maxPuntuacion);
    
    if (equiposMaxPuntuacion.length === 1) {
        return { 
            tipo: 'ganador_tiempo', 
            ganador: equiposMaxPuntuacion[0],
            puntuacion: maxPuntuacion
        };
    } else {
        // En caso de empate en puntuación, desempatar por tiempo acumulado
        const equiposOrdenadosPorTiempo = equiposMaxPuntuacion.sort((a, b) => {
            // Ordenar por tiempo acumulado (menor tiempo primero)
            return a.tiempo_acumulado - b.tiempo_acumulado;
        });
        
        // Verificar si hay empate también en tiempo
        const primerTiempo = equiposOrdenadosPorTiempo[0].tiempo_acumulado;
        const equiposEmpatadosTiempo = equiposOrdenadosPorTiempo.filter(equipo => 
            equipo.tiempo_acumulado === primerTiempo
        );
        
        if (equiposEmpatadosTiempo.length === 1) {
            // Solo un equipo con el menor tiempo
            return { 
                tipo: 'ganador_tiempo', 
                ganador: equiposOrdenadosPorTiempo[0],
                puntuacion: maxPuntuacion
            };
        } else {
            // Empate tanto en puntuación como en tiempo
            return { 
                tipo: 'empate_tiempo', 
                ganadores: equiposEmpatadosTiempo,
                puntuacion: maxPuntuacion,
                tiempo: primerTiempo
            };
        }
    }
}

// Función principal para verificar PODIO COMPLETO (6 desafíos)
async function verificarPodio() {
    if (podioCompletoMostrado) return;
    
    try {
        const data = await obtenerRankingActualizado();
        const ranking = data.ranking;
        
        const podio = verificarPodioCompleto(ranking);
        
        if (podio) {
            mostrarPodioCompleto(podio);
            podioCompletoMostrado = true;
        }
        
    } catch (error) {
        console.error('Error al verificar podio:', error);
    }
}

// Función para mostrar resultados cuando se acaba el tiempo
async function mostrarResultadoTiempo() {
    if (resultadoTiempoMostrado) return;
    
    try {
        const data = await obtenerRankingActualizado();
        const ranking = data.ranking;
        
        const resultado = determinarResultadoTiempo(ranking);
        
        if (!sonidoReproducido) {
            finishSound.play().catch(e => console.log('Error reproduciendo sonido:', e));
            sonidoReproducido = true;
        }
        
        if (resultado) {
            switch (resultado.tipo) {
                case 'ganador_tiempo':
                    mostrarGanadorTiempo(resultado.ganador, resultado.puntuacion);
                    resultadoTiempoMostrado = true;
                    break;
                    
                case 'empate_tiempo':
                    mostrarEmpateTiempo(resultado.ganadores, resultado.puntuacion, resultado.tiempo);
                    resultadoTiempoMostrado = true;
                    break;
                    
                case 'todos_fallaron':
                    mostrarTodosFallaron();
                    resultadoTiempoMostrado = true;
                    break;
            }
        }
        
    } catch (error) {
        console.error('Error al mostrar resultado tiempo:', error);
    }
}

// ===== FUNCIONES PARA MOSTRAR RESULTADOS =====

// 1. PODIO COMPLETO (Cuando equipos completan los 6 desafíos)
function mostrarPodioCompleto(podio) {
    if (!sonidoReproducido) {
        finishSound.play().catch(e => console.log('Error reproduciendo sonido:', e));
        sonidoReproducido = true;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('podioCompletoModal'));
    const podioList = document.getElementById('podioList');
    
    podioList.innerHTML = '';
    
    // Manejar empates en primer lugar
    const empatePrimero = podio.empates.find(empate => empate.posicion === 1);
    
    if (empatePrimero) {
        // Mostrar empate en primer lugar
        const empateDiv = document.createElement('div');
        empateDiv.className = 'podio-item primer-lugar mb-4';
        empateDiv.innerHTML = `
            <div class="text-center">
                <div class="fs-1">🏆</div>
                <h3 class="text-warning">EMPATE EN PRIMER LUGAR</h3>
                <h4>Mismo puntaje y tiempo</h4>
                ${empatePrimero.equipos.map(equipo => `
                    <div class="my-2">
                        <h4 class="fw-bold">${escapeHtml(equipo.nombre_equipo)}</h4>
                        <h5 class="text-success">${equipo.puntuacion_total}/6 Puntos - ${formatearTiempo(equipo.tiempo_acumulado)}</h5>
                    </div>
                `).join('')}
            </div>
        `;
        podioList.appendChild(empateDiv);
        
        empatePrimero.equipos.forEach(equipo => {
            marcarEquipoComoGanador(equipo.id, 'primer-lugar');
        });
    } else if (podio.primero) {
        // Primer lugar normal
        const primerLugar = document.createElement('div');
        primerLugar.className = 'podio-item primer-lugar mb-4';
        primerLugar.innerHTML = `
            <div class="text-center">
                <div class="fs-1">🥇</div>
                <h3 class="text-warning">PRIMER LUGAR</h3>
                <h2 class="fw-bold">${escapeHtml(podio.primero.nombre_equipo)}</h2>
                <h4 class="text-success">${podio.primero.puntuacion_total}/6 Puntos</h4>
                <h5 class="text-info">Tiempo: ${formatearTiempo(podio.primero.tiempo_acumulado)}</h5>
                <p class="text-muted">¡Completó todos los desafíos más rápido!</p>
            </div>
        `;
        podioList.appendChild(primerLugar);
        marcarEquipoComoGanador(podio.primero.id, 'primer-lugar');
    }
    
    // Manejar empates en segundo lugar
    const empateSegundo = podio.empates.find(empate => empate.posicion === 2);
    
    if (empateSegundo) {
        const empateDiv = document.createElement('div');
        empateDiv.className = 'podio-item segundo-lugar mb-4';
        empateDiv.innerHTML = `
            <div class="text-center">
                <div class="fs-1">🥈</div>
                <h4 class="text-secondary">EMPATE EN SEGUNDO LUGAR</h4>
                ${empateSegundo.equipos.map(equipo => `
                    <div class="my-2">
                        <h4 class="fw-bold">${escapeHtml(equipo.nombre_equipo)}</h4>
                        <h5 class="text-success">${equipo.puntuacion_total}/6 Puntos - ${formatearTiempo(equipo.tiempo_acumulado)}</h5>
                    </div>
                `).join('')}
            </div>
        `;
        podioList.appendChild(empateDiv);
        
        empateSegundo.equipos.forEach(equipo => {
            marcarEquipoComoGanador(equipo.id, 'segundo-lugar');
        });
    } else if (podio.segundo) {
        // Segundo lugar normal
        const segundoLugar = document.createElement('div');
        segundoLugar.className = 'podio-item segundo-lugar mb-4';
        segundoLugar.innerHTML = `
            <div class="text-center">
                <div class="fs-1">🥈</div>
                <h4 class="text-secondary">SEGUNDO LUGAR</h4>
                <h3 class="fw-bold">${escapeHtml(podio.segundo.nombre_equipo)}</h3>
                <h5 class="text-success">${podio.segundo.puntuacion_total}/6 Puntos</h5>
                <h6 class="text-info">Tiempo: ${formatearTiempo(podio.segundo.tiempo_acumulado)}</h6>
            </div>
        `;
        podioList.appendChild(segundoLugar);
        marcarEquipoComoGanador(podio.segundo.id, 'segundo-lugar');
    }
    
    // Manejar empates en tercer lugar
    const empateTercero = podio.empates.find(empate => empate.posicion === 3);
    
    if (empateTercero) {
        const empateDiv = document.createElement('div');
        empateDiv.className = 'podio-item tercer-lugar mb-4';
        empateDiv.innerHTML = `
            <div class="text-center">
                <div class="fs-1">🥉</div>
                <h4 class="text-danger">EMPATE EN TERCER LUGAR</h4>
                ${empateTercero.equipos.map(equipo => `
                    <div class="my-2">
                        <h4 class="fw-bold">${escapeHtml(equipo.nombre_equipo)}</h4>
                        <h5 class="text-success">${equipo.puntuacion_total}/6 Puntos - ${formatearTiempo(equipo.tiempo_acumulado)}</h5>
                    </div>
                `).join('')}
            </div>
        `;
        podioList.appendChild(empateDiv);
        
        empateTercero.equipos.forEach(equipo => {
            marcarEquipoComoGanador(equipo.id, 'tercer-lugar');
        });
    } else if (podio.tercero) {
        // Tercer lugar normal
        const tercerLugar = document.createElement('div');
        tercerLugar.className = 'podio-item tercer-lugar mb-4';
        tercerLugar.innerHTML = `
            <div class="text-center">
                <div class="fs-1">🥉</div>
                <h4 class="text-danger">TERCER LUGAR</h4>
                <h3 class="fw-bold">${escapeHtml(podio.tercero.nombre_equipo)}</h3>
                <h5 class="text-success">${podio.tercero.puntuacion_total}/6 Puntos</h5>
                <h6 class="text-info">Tiempo: ${formatearTiempo(podio.tercero.tiempo_acumulado)}</h6>
            </div>
        `;
        podioList.appendChild(tercerLugar);
        marcarEquipoComoGanador(podio.tercero.id, 'tercer-lugar');
    }
    
    // Otros equipos que completaron (mención honorífica)
    if (podio.otros && podio.otros.length > 0) {
        const otrosDiv = document.createElement('div');
        otrosDiv.className = 'otros-equipos mt-4';
        otrosDiv.innerHTML = `
            <h5 class="text-center text-muted">También completaron todos los desafíos:</h5>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                ${podio.otros.map(equipo => 
                    `<span class="badge bg-success">${escapeHtml(equipo.nombre_equipo)} (${formatearTiempo(equipo.tiempo_acumulado)})</span>`
                ).join('')}
            </div>
        `;
        podioList.appendChild(otrosDiv);
        
        podio.otros.forEach(equipo => {
            marcarEquipoComoGanador(equipo.id, 'completo');
        });
    }
    
    crearConfeti();
    modal.show();
}

// 2. GANADOR POR TIEMPO (Cuando se acaba el tiempo y nadie completó todo)
function mostrarGanadorTiempo(ganador, puntuacion) {
    const modal = new bootstrap.Modal(document.getElementById('ganadorTiempoModal'));
    document.getElementById('ganadorTiempoNombre').textContent = ganador.nombre_equipo;
    document.getElementById('ganadorTiempoPuntos').textContent = `${puntuacion}/6 Puntos`;
    
    crearConfeti();
    modal.show();
    marcarEquipoComoGanador(ganador.id, 'ganador-parcial');
}

// 3. EMPATE POR TIEMPO (Cuando se acaba el tiempo y hay empate en puntuación Y tiempo)
function mostrarEmpateTiempo(ganadores, puntuacion, tiempo) {
    const modal = new bootstrap.Modal(document.getElementById('empateTiempoModal'));
    const listaEmpate = document.getElementById('listaEmpateTiempo');
    
    listaEmpate.innerHTML = '';
    
    ganadores.forEach(equipo => {
        const equipoDiv = document.createElement('div');
        equipoDiv.className = 'equipo-empate mb-3 p-3 bg-light rounded';
        equipoDiv.innerHTML = `
            <h4 class="text-dark mb-1">${escapeHtml(equipo.nombre_equipo)}</h4>
            <h5 class="text-warning">${puntuacion}/6 Puntos</h5>
            <h6 class="text-info">Tiempo: ${formatearTiempo(equipo.tiempo_acumulado)}</h6>
            <span class="badge bg-warning">EMPATE EXACTO</span>
            <small class="d-block text-muted mt-1">Mismo puntaje y mismo tiempo</small>
        `;
        listaEmpate.appendChild(equipoDiv);
        marcarEquipoComoGanador(equipo.id, 'empate');
    });
    
    document.getElementById('puntuacionEmpateTiempo').textContent = `${puntuacion}/6 Puntos`;
    
    // Configurar botón de desempate
    document.getElementById('btnIniciarDesempateTiempo').onclick = function() {
        modal.hide();
        setTimeout(() => {
            iniciarDesempate(ganadores);
        }, 500);
    };
    
    modal.show();
}

// 4. TODOS FALLARON (nadie obtuvo puntos)
function mostrarTodosFallaron() {
    const modal = new bootstrap.Modal(document.getElementById('todosFallaronModal'));
    modal.show();
}

// Función para iniciar desempate
function iniciarDesempate(equipos) {
    console.log('Iniciando desempate para equipos:', equipos);
    mostrarNotificacion('🏆 Ronda de desempate iniciada!', 'warning');
    
    // Aquí puedes implementar la lógica específica del desempate
    // Por ejemplo, un desafío adicional o criterio de desempate
}

// ===== SISTEMA DE SONIDOS =====

// Función para reproducir sonidos según banderas capturadas
function reproducirSonidoBanderas(puntuacion) {
    // Solo reproducir si es una nueva bandera y no hemos reproducido este sonido antes
    if (puntuacion > maxPuntuacionGlobal && !banderasReproducidas.has(puntuacion)) {
        
        // Actualizar máxima puntuación
        maxPuntuacionGlobal = puntuacion;
        banderasReproducidas.add(puntuacion);
        
        // Reproducir sonido correspondiente
        if (audiosBanderas[puntuacion]) {
            console.log(`Reproduciendo sonido para ${puntuacion} banderas`);
            audiosBanderas[puntuacion].play().catch(e => {
                console.log('Error reproduciendo sonido de bandera:', e);
            });
        }
        
        // Si llegó a 6 banderas, reproducir sonido de victoria
        if (puntuacion === PUNTUACION_MAXIMA) {
            setTimeout(() => {
                audioVictoria.play().catch(e => {
                    console.log('Error reproduciendo sonido de victoria:', e);
                });
            }, 1000);
        }
    }
}

// Función para resetear los sonidos cuando se reinicia el hackathon
function resetearSonidos() {
    banderasReproducidas.clear();
    maxPuntuacionGlobal = 0;
    console.log('Sonidos reseteados para nuevo hackathon');
}

// Función para probar sonidos (solo para testing)
function probarSonido(numero) {
    if (numero === 'victoria') {
        audioVictoria.play().catch(e => console.log('Error probando sonido de victoria:', e));
    } else if (audiosBanderas[numero]) {
        audiosBanderas[numero].play().catch(e => console.log(`Error probando sonido ${numero}:`, e));
    }
}

// ===== SISTEMA DE ACTUALIZACIÓN AUTOMÁTICA =====

// Inicializar mapa de equipos actuales
document.addEventListener('DOMContentLoaded', function() {
    // Guardar los equipos actuales en el mapa
    const filasEquipos = document.querySelectorAll('#tabla-equipos tr[data-equipo-id]');
    filasEquipos.forEach(fila => {
        const equipoId = fila.getAttribute('data-equipo-id');
        equiposActuales.set(equipoId, fila);
    });
    
    // Configurar eventos de eliminación
    configurarEventosEliminacion();
    
    // Inicializar volumen de los audios
    Object.values(audiosBanderas).forEach(audio => {
        if (audio) {
            audio.volume = 0.7; // 70% de volumen
        }
    });
    if (audioVictoria) {
        audioVictoria.volume = 0.8; // 80% de volumen para victoria
    }
    
    // Iniciar monitoreo de nuevos equipos y puntuaciones
    iniciarMonitoreoEquipos();
});

// Configurar eventos para botones de eliminar
function configurarEventosEliminacion() {
    const botonesEliminar = document.querySelectorAll('.btn-eliminar-equipo');
    botonesEliminar.forEach(boton => {
        boton.addEventListener('click', function() {
            const equipoId = this.getAttribute('data-equipo-id');
            const equipoNombre = this.getAttribute('data-equipo-nombre');
            
            document.getElementById('equipoIdEliminar').value = equipoId;
            document.getElementById('equipoNombreEliminar').textContent = 'Equipo: ' + equipoNombre;
        });
    });
}

// Función para verificar cambios en tiempos acumulados (MEJORADA)
function verificarCambiosTiempo() {
    console.log('Verificando cambios de tiempo...');
    
    fetch('obtener_actualizaciones_tiempo.php?t=' + Date.now())
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.equipos_actualizados) {
                console.log('Datos recibidos:', data.equipos_actualizados);
                
                if (data.equipos_actualizados.length > 0) {
                    actualizarTiemposEquipos(data.equipos_actualizados);
                } else {
                    console.log('No hay equipos con tiempo actualizado');
                }
            } else {
                console.error('Error en datos:', data.error);
            }
        })
        .catch(error => {
            console.error('Error al verificar tiempos:', error);
            // Si falla, intentar con obtener_ranking_actual.php como respaldo
            obtenerRankingActualizado().then(data => {
                if (data.success) {
                    const equiposConTiempo = data.ranking.filter(equipo => equipo.tiempo_acumulado > 0);
                    if (equiposConTiempo.length > 0) {
                        actualizarTiemposEquipos(equiposConTiempo);
                    }
                }
            });
        });
}

// Función mejorada para actualizar tiempos
function actualizarTiemposEquipos(equiposActualizados) {
    console.log('Actualizando tiempos para equipos:', equiposActualizados);
    
    let huboCambios = false;
    
    equiposActualizados.forEach(equipo => {
        const equipoId = equipo.id.toString();
        if (equiposActuales.has(equipoId)) {
            const filaEquipo = equiposActuales.get(equipoId);
            const celdaTiempo = filaEquipo.querySelector('td:nth-child(5)');
            
            if (celdaTiempo && equipo.tiempo_acumulado > 0) {
                const nuevoTiempo = formatearTiempo(equipo.tiempo_acumulado);
                const contenidoActual = celdaTiempo.innerHTML;
                const nuevoContenido = `${nuevoTiempo}${equipo.completado ? '<br><small class="text-success">✅ Completado</small>' : ''}`;
                
                // Solo actualizar si el contenido cambió
                if (contenidoActual !== nuevoContenido) {
                    console.log(`Actualizando tiempo equipo ${equipoId}: ${nuevoTiempo}`);
                    celdaTiempo.innerHTML = nuevoContenido;
                    celdaTiempo.classList.add('puntuacion-cambiando');
                    
                    // Marcar como completo si es necesario
                    if (equipo.completado || equipo.puntuacion_total === PUNTUACION_MAXIMA) {
                        filaEquipo.classList.add('equipo-completo');
                        
                        // Reproducir sonido de victoria si completó todos los desafíos
                        if (equipo.puntuacion_total === PUNTUACION_MAXIMA && !banderasReproducidas.has('victoria')) {
                            banderasReproducidas.add('victoria');
                            setTimeout(() => {
                                audioVictoria.play().catch(e => {
                                    console.log('Error reproduciendo sonido de victoria:', e);
                                });
                            }, 500);
                        }
                    }
                    
                    setTimeout(() => {
                        celdaTiempo.classList.remove('puntuacion-cambiando');
                    }, 2000);
                    
                    huboCambios = true;
                }
            }
        }
    });
    
    // Si hubo cambios en tiempos, reordenar la tabla
    if (huboCambios) {
        console.log('Hubo cambios de tiempo, reordenando tabla...');
        obtenerRankingActualizado().then(data => {
            if (data.success) {
                reordenarTablaCompleta(data.ranking);
            }
        });
    }
}

// Función para monitorear nuevos equipos y cambios en puntuaciones automáticamente
function iniciarMonitoreoEquipos() {
    function verificarNuevosEquipos() {
        fetch('obtener_nuevos_equipos.php?t=' + Date.now())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const nuevosEquipos = data.nuevos_equipos || [];
                    const totalEquipos = data.total_equipos || 0;
                    
                    // Actualizar contador de equipos
                    if (totalEquiposElement) {
                        totalEquiposElement.textContent = totalEquipos;
                    }
                    
                    // Agregar nuevos equipos si los hay
                    if (nuevosEquipos.length > 0) {
                        nuevosEquipos.forEach(equipo => {
                            if (!equiposActuales.has(equipo.id.toString())) {
                                agregarEquipoDinamico(equipo);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error al verificar equipos:', error);
            });
    }

    function verificarCambiosPuntuaciones() {
        fetch('obtener_actualizaciones_puntuaciones.php?t=' + Date.now())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const equiposActualizados = data.equipos_actualizados || [];
                    const rankingCompleto = data.ranking_completo || [];
                    
                    // Si hay equipos con puntuaciones actualizadas
                    if (equiposActualizados.length > 0) {
                        actualizarPuntuacionesYRanking(equiposActualizados, rankingCompleto);
                    }
                }
            })
            .catch(error => {
                console.error('Error al verificar puntuaciones:', error);
            });
    }
    
    // Verificar nuevos equipos cada 2 segundos
    setInterval(verificarNuevosEquipos, 2000);
    
    // Verificar cambios en puntuaciones cada 3 segundos
    setInterval(verificarCambiosPuntuaciones, 3000);
    
    // Verificar cambios en tiempos cada 2 segundos
    setInterval(() => {
        console.log('Ejecutando verificación de tiempo...');
        verificarCambiosTiempo();
    }, 3000);
    
    // Verificar inmediatamente al cargar
    setTimeout(verificarNuevosEquipos, 1000);
    setTimeout(verificarCambiosPuntuaciones, 1500);
    setTimeout(() => {
        console.log('Ejecutando verificación de tiempo...');
        verificarCambiosTiempo();
    }, 2000);
}

// Función para agregar equipo dinámicamente
function agregarEquipoDinamico(equipo) {
    const nuevaFila = document.createElement('tr');
    nuevaFila.className = 'equipo-nuevo';
    nuevaFila.setAttribute('data-equipo-id', equipo.id);
    
    const posicionTemporal = equiposActuales.size + 1;
    
    nuevaFila.innerHTML = `
        <td>
            <strong class="fs-5">${posicionTemporal}°</strong>
        </td>
        <td>
            <strong>${escapeHtml(equipo.nombre_equipo)}</strong>
            <span class="badge badge-nuevo ms-2">NUEVO</span>
            ${equipo.inicio_tardio ? '<br><span class="badge bg-info status-badge mt-1" title="Equipo se unió después del inicio">TARDÍO</span>' : ''}
        </td>
        <td>
            <code class="fs-5">${escapeHtml(equipo.codigo_equipo)}</code>
        </td>
        <td>
            <strong class="fs-4 text-primary">${equipo.puntuacion_total}</strong>
            <small class="text-muted">🚩</small>
        </td>
        <td>
            ${equipo.tiempo_acumulado > 0 ? 
                `${formatearTiempo(equipo.tiempo_acumulado)}${equipo.completado ? '<br><small class="text-success">✅ Completado</small>' : ''}` : 
                '<span class="text-muted">--:--</span>'
            }
        </td>
        <td>
            <span class="badge ${equipo.estado == 1 ? 'badge-compitiendo' : 'badge-espera'} p-2">
                ${equipo.estado == 1 ? '🏁 COMPITIENDO' : '⏳ EN ESPERA'}
            </span>
        </td>
        <td class="text-center actions-column">
            <button type="button" class="btn btn-danger btn-sm btn-eliminar-equipo" 
                    data-bs-toggle="modal" 
                    data-bs-target="#eliminarModal"
                    data-equipo-id="${equipo.id}"
                    data-equipo-nombre="${escapeHtml(equipo.nombre_equipo)}"
                    title="Eliminar equipo">
                🗑️ Eliminar
            </button>
        </td>
    `;
    
    tablaEquipos.appendChild(nuevaFila);
    equiposActuales.set(equipo.id.toString(), nuevaFila);
    
    if (totalEquiposElement) {
        totalEquiposElement.textContent = equiposActuales.size;
    }
    
    configurarEventosEliminacion();
    
    mostrarNotificacion(`¡Nuevo equipo registrado: ${equipo.nombre_equipo}!`);
    
    setTimeout(() => {
        nuevaFila.classList.remove('equipo-nuevo');
        const badgeNuevo = nuevaFila.querySelector('.badge-nuevo');
        if (badgeNuevo) {
            badgeNuevo.remove();
        }
    }, 3000);
}

// Función para actualizar puntuaciones y reordenar ranking
function actualizarPuntuacionesYRanking(equiposActualizados, rankingCompleto) {
    let huboCambios = false;
    
    // Actualizar puntuaciones de equipos existentes
    equiposActualizados.forEach(equipoActualizado => {
        const equipoId = equipoActualizado.id.toString();
        if (equiposActuales.has(equipoId)) {
            const filaEquipo = equiposActuales.get(equipoId);
            
            const celdaPuntuacion = filaEquipo.querySelector('td:nth-child(4) strong');
            const puntuacionActual = parseInt(celdaPuntuacion.textContent);
            const nuevaPuntuacion = equipoActualizado.puntuacion_total;
            
            // Actualizar tiempo también si está disponible
            const celdaTiempo = filaEquipo.querySelector('td:nth-child(5)');
            const necesitaActualizarTiempo = equipoActualizado.tiempo_acumulado > 0;
            
            if (puntuacionActual !== nuevaPuntuacion || necesitaActualizarTiempo) {
                // REPRODUCIR SONIDO si hay nueva bandera capturada
                if (nuevaPuntuacion > puntuacionActual) {
                    console.log(`Nueva bandera capturada: ${nuevaPuntuacion}`);
                    reproducirSonidoBanderas(nuevaPuntuacion);
                }
                
                // Actualizar puntuación
                if (puntuacionActual !== nuevaPuntuacion) {
                    celdaPuntuacion.textContent = nuevaPuntuacion;
                    celdaPuntuacion.classList.add('puntuacion-cambiando');
                }
                
                // Actualizar tiempo
                if (necesitaActualizarTiempo) {
                    celdaTiempo.innerHTML = `${formatearTiempo(equipoActualizado.tiempo_acumulado)}${equipoActualizado.completado ? '<br><small class="text-success">✅ Completado</small>' : ''}`;
                    celdaTiempo.classList.add('puntuacion-cambiando');
                }
                
                // Marcar como completo si llegó a 6 puntos
                if (nuevaPuntuacion === PUNTUACION_MAXIMA || equipoActualizado.completado) {
                    filaEquipo.classList.add('equipo-completo');
                    
                    // Verificar PODIO inmediatamente cuando alguien completa los 6 desafíos
                    setTimeout(() => {
                        verificarPodio();
                    }, 1000);
                }
                
                setTimeout(() => {
                    celdaPuntuacion.classList.remove('puntuacion-cambiando');
                    if (necesitaActualizarTiempo) {
                        celdaTiempo.classList.remove('puntuacion-cambiando');
                    }
                }, 2000);
                
                huboCambios = true;
            }
        }
    });
    
    // Si hubo cambios significativos, reordenar toda la tabla
    if (huboCambios && rankingCompleto.length > 0) {
        reordenarTablaCompleta(rankingCompleto);
    }
}

// Función para reordenar completamente la tabla según el ranking
function reordenarTablaCompleta(rankingCompleto) {
    const tbody = document.getElementById('tabla-equipos');
    const filasExistentes = Array.from(tbody.querySelectorAll('tr[data-equipo-id]'));
    
    tbody.innerHTML = '';
    
    rankingCompleto.forEach((equipo, index) => {
        const equipoId = equipo.id.toString();
        let filaExistente = filasExistentes.find(fila => fila.getAttribute('data-equipo-id') === equipoId);
        
        if (!filaExistente) {
            filaExistente = crearFilaEquipo(equipo, index);
        } else {
            actualizarFilaEquipo(filaExistente, equipo, index);
        }
        
        tbody.appendChild(filaExistente);
        equiposActuales.set(equipoId, filaExistente);
    });
    
    configurarEventosEliminacion();
}

// Función para crear una nueva fila de equipo
function crearFilaEquipo(equipo, index) {
    const nuevaFila = document.createElement('tr');
    nuevaFila.setAttribute('data-equipo-id', equipo.id);
    
    let claseFila = '';
    if (index === 0) claseFila = 'top-1';
    else if (index === 1) claseFila = 'top-2';
    else if (index === 2) claseFila = 'top-3';
    
    nuevaFila.className = claseFila;
    
    nuevaFila.innerHTML = `
        <td>
            <strong class="fs-5">${index + 1}°</strong>
            ${index < 3 ? `
                <br>
                <span class="badge bg-${index === 0 ? 'warning' : (index === 1 ? 'secondary' : 'danger')} mt-1">
                    ${index === 0 ? '🥇 ORO' : (index === 1 ? '🥈 PLATA' : '🥉 BRONCE')}
                </span>
            ` : ''}
        </td>
        <td>
            <strong>${escapeHtml(equipo.nombre_equipo)}</strong>
            ${equipo.inicio_tardio ? '<br><span class="badge bg-info status-badge mt-1" title="Equipo se unió después del inicio">TARDÍO</span>' : ''}
        </td>
        <td>
            <code class="fs-5">${escapeHtml(equipo.codigo_equipo)}</code>
        </td>
        <td>
            <strong class="fs-4 text-primary">${equipo.puntuacion_total}</strong>
            <small class="text-muted">🚩</small>
        </td>
        <td>
            ${equipo.tiempo_acumulado > 0 ? 
                `${formatearTiempo(equipo.tiempo_acumulado)}${equipo.completado ? '<br><small class="text-success">✅ Completado</small>' : ''}` : 
                '<span class="text-muted">--:--</span>'
            }
        </td>
        <td>
            <span class="badge ${equipo.estado == 1 ? 'badge-compitiendo' : 'badge-espera'} p-2">
                ${equipo.estado == 1 ? '🏁 COMPITIENDO' : '⏳ EN ESPERA'}
            </span>
        </td>
        <td class="text-center actions-column">
            <button type="button" class="btn btn-danger btn-sm btn-eliminar-equipo" 
                    data-bs-toggle="modal" 
                    data-bs-target="#eliminarModal"
                    data-equipo-id="${equipo.id}"
                    data-equipo-nombre="${escapeHtml(equipo.nombre_equipo)}"
                    title="Eliminar equipo">
                🗑️ Eliminar
            </button>
        </td>
    `;
    
    return nuevaFila;
}

// Función para actualizar una fila existente de equipo
function actualizarFilaEquipo(fila, equipo, index) {
    const celdaPosicion = fila.querySelector('td:nth-child(1) strong');
    celdaPosicion.textContent = `${index + 1}°`;
    
    const badgePosicion = fila.querySelector('.badge');
    if (index < 3) {
        if (!badgePosicion) {
            const nuevoBadge = document.createElement('span');
            nuevoBadge.className = `badge bg-${index === 0 ? 'warning' : (index === 1 ? 'secondary' : 'danger')} mt-1`;
            nuevoBadge.textContent = index === 0 ? '🥇 ORO' : (index === 1 ? '🥈 PLATA' : '🥉 BRONCE');
            celdaPosicion.parentNode.appendChild(document.createElement('br'));
            celdaPosicion.parentNode.appendChild(nuevoBadge);
        } else {
            badgePosicion.className = `badge bg-${index === 0 ? 'warning' : (index === 1 ? 'secondary' : 'danger')} mt-1`;
            badgePosicion.textContent = index === 0 ? '🥇 ORO' : (index === 1 ? '🥈 PLATA' : '🥉 BRONCE');
        }
    } else if (badgePosicion) {
        badgePosicion.remove();
        const br = fila.querySelector('td:nth-child(1) br');
        if (br) br.remove();
    }
    
    fila.className = '';
    if (index === 0) fila.classList.add('top-1');
    else if (index === 1) fila.classList.add('top-2');
    else if (index === 2) fila.classList.add('top-3');
    
    const celdaPuntuacion = fila.querySelector('td:nth-child(4) strong');
    celdaPuntuacion.textContent = equipo.puntuacion_total;
    
    // Actualizar celda de tiempo (5ta columna)
    const celdaTiempo = fila.querySelector('td:nth-child(5)');
    if (equipo.tiempo_acumulado > 0) {
        celdaTiempo.innerHTML = `${formatearTiempo(equipo.tiempo_acumulado)}${equipo.completado ? '<br><small class="text-success">✅ Completado</small>' : ''}`;
    } else {
        celdaTiempo.innerHTML = '<span class="text-muted">--:--</span>';
    }
    
    // Actualizar celda de estado (6ta columna)
    const celdaEstado = fila.querySelector('td:nth-child(6) span');
    celdaEstado.className = `badge ${equipo.estado == 1 ? 'badge-compitiendo' : 'badge-espera'} p-2`;
    celdaEstado.textContent = equipo.estado == 1 ? '🏁 COMPITIENDO' : '⏳ EN ESPERA';
}

// ===== FUNCIONES UTILITARIAS =====

// Función para formatear segundos a MM:SS
function formatearTiempo(segundos) {
    if (segundos <= 0) return '--:--';
    
    const minutos = Math.floor(segundos / 60);
    const segundosRestantes = segundos % 60;
    
    return `${String(minutos).padStart(2, '0')}:${String(segundosRestantes).padStart(2, '0')}`;
}

// Función para marcar equipos ganadores en la tabla
function marcarEquipoComoGanador(equipoId, tipo) {
    const fila = document.querySelector(`tr[data-equipo-id="${equipoId}"]`);
    if (fila) {
        fila.classList.add('equipo-ganador');
        
        switch(tipo) {
            case 'primer-lugar':
                fila.classList.add('primer-lugar-tabla');
                break;
            case 'segundo-lugar':
                fila.classList.add('segundo-lugar-tabla');
                break;
            case 'tercer-lugar':
                fila.classList.add('tercer-lugar-tabla');
                break;
            case 'ganador-parcial':
                fila.classList.add('ganador-parcial-tabla');
                break;
            case 'empate':
                fila.classList.add('empate-tabla');
                break;
            case 'completo':
                fila.classList.add('completo-tabla');
                break;
        }
    }
}

// Función para escapar HTML (seguridad)
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Función para crear confeti
function crearConfeti() {
    const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
    for (let i = 0; i < 150; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
            document.body.appendChild(confetti);
            
            setTimeout(() => {
                confetti.remove();
            }, 5000);
        }, i * 20);
    }
}

// Función para mostrar notificación
function mostrarNotificacion(mensaje, tipo = 'success') {
    document.querySelectorAll('.notificacion-flotante').forEach(notif => notif.remove());
    
    const notificacion = document.createElement('div');
    notificacion.className = `alert alert-${tipo} alert-dismissible fade show notificacion-flotante`;
    
    notificacion.innerHTML = `
        <div class="d-flex align-items-center">
            <span class="fs-5 me-2">${tipo === 'success' ? '🎉' : 'ℹ️'}</span>
            <div>
                <strong>${tipo === 'success' ? '¡Nuevo equipo!' : 'Información'}</strong>
                <p class="mb-0">${mensaje}</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        if (notificacion.parentNode) {
            notificacion.remove();
        }
    }, 4000);
}

// Función para actualizar el temporizador con efectos visuales
function actualizarTiempoGlobal() {
    if (!tiempoElement) return;
    
    if (tiempoRestante <= 0) {
        tiempoElement.textContent = '00:00';
        tiempoElement.className = 'temporizador-grande temporizador-peligro';
        mostrarResultadoTiempo();
        return;
    }
    
    tiempoRestante--;
    
    const minutos = Math.floor(tiempoRestante / 60);
    const segundos = tiempoRestante % 60;
    tiempoElement.textContent = `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
    
    // Efectos visuales según el tiempo restante
    if (tiempoRestante < 300) {
        tiempoElement.className = 'temporizador-grande temporizador-advertencia';
    }
    
    if (tiempoRestante < 60) {
        tiempoElement.className = 'temporizador-grande temporizador-peligro';
    }
}

// Iniciar el temporizador solo si el hackathon está activo
<?php if ($hackathon_activo): ?>
const temporizador = setInterval(actualizarTiempoGlobal, 1000);

// Verificar inmediatamente si el tiempo ya se agotó
if (tiempoRestante <= 0) {
    mostrarResultadoTiempo();
}
<?php endif; ?>
</script>

</body>
</html>