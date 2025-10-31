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
    $ranking = obtenerRankingEquipos();
    $config_hackathon = obtenerConfiguracionHackathon();
    $hackathon_activo = hackathonEstaActivo();
    $tiempo_restante = calcularTiempoRestanteGlobal();
    
    // Obtener el equipo ganador (primer lugar) y verificar si hay puntuación
    $equipo_ganador = !empty($ranking) ? $ranking[0] : null;
    $hay_ganador = $equipo_ganador && $equipo_ganador['puntuacion_total'] > 0;
    
} catch (Exception $e) {
    // Si hay error al obtener datos, mostrar página básica
    $ranking = [];
    $config_hackathon = null;
    $hackathon_activo = false;
    $tiempo_restante = 0;
    $equipo_ganador = null;
    $hay_ganador = false;
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
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; }
        .badge-espera { background-color: #6c757d; }
        .badge-compitiendo { background-color: #198754; }
        .actions-column { width: 120px; }
        .winner-modal { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
        .no-winner-modal { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; }
        .winner-crown { font-size: 4rem; animation: bounce 2s infinite; }
        .sad-face { font-size: 4rem; animation: pulse 2s infinite; }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-30px);}
            60% {transform: translateY(-15px);}
        }
        @keyframes pulse {
            0%, 100% {transform: scale(1);}
            50% {transform: scale(1.1);}
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
                       
                    <?php else: ?>
                        <p class="mb-1">⏳ Duración: <strong>1 hora 30 minutos</strong></p>
                        <p class="mb-0">👥 Equipos registrados: <strong><?php echo count($ranking); ?></strong></p>
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
                            <th width="30%">Nombre del Equipo</th>
                            <th width="15%">Código</th>
                            <th width="15%">Puntuación</th>
                            <th width="20%">Estado</th>
                            <th width="12%" class="text-center">Acciones</th>
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
                                <td class="text-center actions-column">
                                    <!-- Botón Eliminar -->
                                    <button type="button" class="btn btn-danger btn-sm" 
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
                                <td colspan="6" class="text-center py-5">
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
    <?php endif; ?>
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
                    <strong>🎯 Desafíos:</strong> Se activarán automáticamente
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

<!-- Modal del Ganador -->
<div class="modal fade" id="winnerModal" tabindex="-1" aria-labelledby="winnerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content winner-modal">
            <div class="modal-header border-0">
                <h2 class="modal-title text-center w-100" id="winnerModalLabel">🎉 ¡HACKATHON FINALIZADO! 🎉</h2>
            </div>
            <div class="modal-body text-center">
                <div class="winner-crown">👑</div>
                <h3 class="mt-3">EQUIPO GANADOR</h3>
                <h1 class="display-4 fw-bold text-dark" id="winnerTeamName"></h1>
                <h2 class="text-success" id="winnerScore"></h2>
                <p class="fs-5 mt-3">¡Felicidades por su excelente desempeño!</p>
                <div class="mt-4">
                    <span class="badge bg-success fs-6 p-2">🥇 PRIMER LUGAR</span>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-dark btn-lg" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sin Ganador -->
<div class="modal fade" id="noWinnerModal" tabindex="-1" aria-labelledby="noWinnerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content no-winner-modal">
            <div class="modal-header border-0">
                <h2 class="modal-title text-center w-100" id="noWinnerModalLabel">⏰ ¡HACKATHON FINALIZADO! ⏰</h2>
            </div>
            <div class="modal-body text-center">
                <div class="sad-face">😔</div>
                <h3 class="mt-3">NO HAY GANADOR</h3>
                <h1 class="display-4 fw-bold text-light">NINGUNO DE LOS EQUIPOS</h1>
                <h2 class="text-warning">PUDO COMPLETAR LOS NIVELES</h2>
                <p class="fs-5 mt-3">Los desafíos fueron muy difíciles esta vez.</p>
                <div class="mt-4">
                    <span class="badge bg-warning fs-6 p-2">🏆 MEJOR SUERTE PARA LA PRÓXIMA</span>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Variables globales
let tiempoRestante = <?php echo $tiempo_restante; ?>;
let tiempoAgotadoMostrado = false;
let sonidoReproducido = false;

// Elementos del DOM
const tiempoElement = document.getElementById('tiempo-global');
const winnerModal = new bootstrap.Modal(document.getElementById('winnerModal'));
const noWinnerModal = new bootstrap.Modal(document.getElementById('noWinnerModal'));
const finishSound = document.getElementById('finishSound');
const winnerTeamName = document.getElementById('winnerTeamName');
const winnerScore = document.getElementById('winnerScore');

// Configurar modal de eliminación
const eliminarModal = document.getElementById('eliminarModal');
eliminarModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const equipoId = button.getAttribute('data-equipo-id');
    const equipoNombre = button.getAttribute('data-equipo-nombre');
    
    document.getElementById('equipoIdEliminar').value = equipoId;
    document.getElementById('equipoNombreEliminar').textContent = 'Equipo: ' + equipoNombre;
});

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
            
            // Remover confeti después de la animación
            setTimeout(() => {
                confetti.remove();
            }, 5000);
        }, i * 20);
    }
}

// Función para mostrar el ganador o mensaje de no ganador
function mostrarResultadoFinal() {
    if (!tiempoAgotadoMostrado) {
        tiempoAgotadoMostrado = true;
        
        // Reproducir sonido (solo una vez)
        if (!sonidoReproducido) {
            finishSound.play().catch(e => console.log('Error reproduciendo sonido:', e));
            sonidoReproducido = true;
        }
        
        <?php if ($hay_ganador): ?>
        // Mostrar ganador
        winnerTeamName.textContent = '<?php echo htmlspecialchars($equipo_ganador['nombre_equipo']); ?>';
        winnerScore.textContent = '<?php echo $equipo_ganador['puntuacion_total']; ?> Puntos';
        crearConfeti();
        setTimeout(() => {
            winnerModal.show();
        }, 1000);
        <?php else: ?>
        // Mostrar mensaje de no ganador
        setTimeout(() => {
            noWinnerModal.show();
        }, 1000);
        <?php endif; ?>
    }
}

// Función principal para actualizar el tiempo
function actualizarTiempoGlobal() {
    if (!tiempoElement) return;
    
    // Verificar si el tiempo se agotó
    if (tiempoRestante <= 0) {
        tiempoElement.textContent = '00:00';
        tiempoElement.className = 'text-danger';
        mostrarResultadoFinal();
        return;
    }
    
    // Restar un segundo
    tiempoRestante--;
    
    // Actualizar display
    const minutos = Math.floor(tiempoRestante / 60);
    const segundos = tiempoRestante % 60;
    tiempoElement.textContent = `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
    
    // Cambiar color cuando queden menos de 5 minutos
    if (tiempoRestante < 300) {
        tiempoElement.className = 'text-warning';
    }
    
    // Cambiar color cuando queden menos de 1 minuto
    if (tiempoRestante < 60) {
        tiempoElement.className = 'text-danger';
    }
}

// Iniciar el temporizador solo si el hackathon está activo
<?php if ($hackathon_activo): ?>
const temporizador = setInterval(actualizarTiempoGlobal, 1000);

// Verificar inmediatamente si el tiempo ya se agotó
if (tiempoRestante <= 0) {
    mostrarResultadoFinal();
}
<?php endif; ?>
</script>

</body>
</html>