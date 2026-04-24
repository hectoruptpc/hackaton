<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();
header('X-Secret-Flag: FLAG{http_header_secret}');
require_once __DIR__ . '/conf/functions.php';

// 1. Si ya está en sesión, calcula el tiempo y muestra dashboard
if (isset($_SESSION['cedula'])) {
    $participante = validarSesion();
    if (!$participante) {
        header("Location: index.php");
        exit;
    }
    
    // Verificar si el hackathon está activo
    $config_hackathon = obtenerConfiguracionHackathon();
    $hackathon_activo = hackathonEstaActivo();
    $info_equipo = obtenerTiempoInicioEquipo($_SESSION['equipo_id']);
    
    // Obtener desafíos completados por el equipo
    $desafiosCompletados = obtenerDesafiosCompletados($_SESSION['equipo_id']);
    
    // Calcular tiempo transcurrido específico del equipo
    if ($hackathon_activo && $info_equipo['tiempo_inicio']) {
        $segundos_transcurridos = calcularTiempoTranscurrido($info_equipo['tiempo_inicio']);
    } else {
        $segundos_transcurridos = 0;
    }
    
    // Calcular tiempo restante global
    $tiempo_restante_global = calcularTiempoRestanteGlobal();

// 2. Si viene del formulario de crear equipo con todos los miembros
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_equipo'])) {
    $nombre_equipo = trim($_POST['nombre_equipo']);
    
    if (empty($nombre_equipo)) {
        $_SESSION['form_errors'] = ['El nombre del equipo es obligatorio.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Validar que haya al menos 3 miembros
    $miembros_minimos = 0;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty(trim($_POST["nombre_$i"])) && !empty(trim($_POST["cedula_$i"]))) {
            $miembros_minimos++;
        }
    }
    
    if ($miembros_minimos < 3) {
        $_SESSION['form_errors'] = ['Debes registrar al menos 3 miembros para el equipo.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Registrar el equipo
    $equipo_id = registrarEquipo($nombre_equipo);
    if (!$equipo_id) {
        $_SESSION['form_errors'] = ['Error al crear el equipo. El nombre puede estar en uso.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Registrar los miembros
    $miembros_registrados = 0;
    $errores_miembros = [];
    
    for ($i = 1; $i <= 4; $i++) {
        $nombre = trim($_POST["nombre_$i"]);
        $cedula = trim($_POST["cedula_$i"]);
        
        if (!empty($nombre) && !empty($cedula)) {
            if (!validarCedula($cedula)) {
                $errores_miembros[] = "La cédula del miembro $i solo debe contener números.";
                continue;
            }
            
            if (usuarioExiste($cedula)) {
                $errores_miembros[] = "La cédula $cedula ya está registrada en otro equipo.";
                continue;
            }
            
            if (!registrarParticipante($nombre, $cedula, $equipo_id)) {
                $errores_miembros[] = "Error al registrar el miembro $i.";
                continue;
            }
            
            $miembros_registrados++;
        } else if (!empty($nombre) && empty($cedula)) {
            $errores_miembros[] = `El miembro $i tiene nombre pero falta la cédula.`;
        } else if (empty($nombre) && !empty($cedula)) {
            $errores_miembros[] = `El miembro $i tiene cédula pero falta el nombre.`;
        }
    }
    
    // Si hay errores en miembros, mostrar modal
    if (!empty($errores_miembros)) {
        $_SESSION['form_errors'] = $errores_miembros;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if ($miembros_registrados < 3) {
        $_SESSION['form_errors'] = ['Debes registrar al menos 3 miembros completos para el equipo.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Iniciar sesión con el primer miembro registrado
    $primer_miembro = usuarioExiste(trim($_POST["cedula_1"]));
    if ($primer_miembro) {
        iniciarSesion($primer_miembro);
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['form_errors'] = ['Error al iniciar sesión.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

// 3. Si viene del formulario de acceso individual
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cedula_acceso'])) {
    $cedula = trim($_POST['cedula_acceso']);
    
    if (!validarCedula($cedula)) {
        $_SESSION['access_errors'] = ['La cédula solo debe contener números.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Verificar si el usuario existe
    $participante = usuarioExiste($cedula);
    if (!$participante) {
        $_SESSION['access_errors'] = ['No se encontró un equipo registrado con esta cédula.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Iniciar sesión
    iniciarSesion($participante);
    header("Location: index.php");
    exit;

// 4. Si viene del formulario de acceso administrativo
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_admin'])) {
    $codigo_ingresado = trim($_POST['codigo_admin']);
    $codigo_correcto = 'robotica';
    
    if ($codigo_ingresado === $codigo_correcto) {
        // Crear sesión de administrador
        $_SESSION['es_admin'] = true;
        $_SESSION['admin_autenticado'] = true;
        header("Location: equipos.php");
        exit;
    } else {
        $_SESSION['admin_errors'] = ['Código administrativo incorrecto.'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

// 5. Si no hay sesión, mostrar formulario de inicio
} else {
    // Si hay sesión temporal, limpiarla
    if (isset($_SESSION['equipo_temporal'])) {
        unset($_SESSION['equipo_temporal']);
        unset($_SESSION['nombre_equipo_temporal']);
    }
    
    // Verificar si hay mensajes de error para mostrar en modales
    $form_errors = $_SESSION['form_errors'] ?? [];
    $access_errors = $_SESSION['access_errors'] ?? [];
    $admin_errors = $_SESSION['admin_errors'] ?? [];
    
    // Limpiar los errores después de obtenerlos
    unset($_SESSION['form_errors']);
    unset($_SESSION['access_errors']); 
    unset($_SESSION['admin_errors']);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Inicio Hackaton</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 0; border-radius: 15px; }
            .member-form { border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-bottom: 15px; }
            .optional-member { background-color: #f8f9fa; }
            .admin-section { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; }
            .hidden { display: none !important; }
        </style>
    </head>
    <body>
    <div class="container mt-4">
        <!-- FLAG{source_code_secret} -->
        <div class="text-center mb-3">
            <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
            <h1>Hackathon UPTPC 2026 - Segundo Evento</h1>
        </div>
        
        <div class="hero-section text-center mb-5">
            <h2 class="display-4 mb-3">Desafío de Seguridad Informática</h2>
            <p class="lead mb-4">¡Forma tu equipo y compite por el primer lugar!</p>
            <p class="mb-4">Equipos de 3 a 4 personas - Tiempo limitado - Múltiples desafíos</p>
        </div>

        <div class="row justify-content-center">
            <!-- Formulario de Crear Nuevo Equipo -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h3 class="mb-0">Crear Nuevo Equipo</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" id="team-form">
                            <div class="mb-3">
                                <label for="nombre_equipo" class="form-label fs-5">Nombre del Equipo</label>
                                <input type="text" class="form-control form-control-lg" id="nombre_equipo" name="nombre_equipo" required placeholder="Ingresa el nombre de tu equipo">
                                <div class="form-text">Nombre corto y original.</div>
                            </div>
                            
                            <h5 class="mt-4 mb-3">Miembros del Equipo <small class="text-muted">(Mínimo 3, máximo 4)</small></h5>
                            
                            <!-- Capitan (Obligatorio) -->
                            <div class="member-form">
                                <h6 class="text-primary">Capitan <span class="text-danger">*</span></h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="nombre_1" placeholder="Nombre completo" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="cedula_1" placeholder="Cédula" pattern="\d+" maxlength="20" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Miembro 1 (Obligatorio) -->
                            <div class="member-form">
                                <h6 class="text-primary">Miembro 1 <span class="text-danger">*</span></h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="nombre_2" placeholder="Nombre completo" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="cedula_2" placeholder="Cédula" pattern="\d+" maxlength="20" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Miembro 2 (Obligatorio) -->
                            <div class="member-form">
                                <h6 class="text-primary">Miembro 2 <span class="text-danger">*</span></h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="nombre_3" placeholder="Nombre completo" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="cedula_3" placeholder="Cédula" pattern="\d+" maxlength="20" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Miembro 3 (Opcional) -->
                            <div class="member-form optional-member">
                                <h6 class="text-muted">Miembro 3 <small>(Opcional)</small></h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="nombre_4" placeholder="Nombre completo">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="cedula_4" placeholder="Cédula" pattern="\d+" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            
                            <div id="alert-container" class="mb-3"></div>
                            <button type="submit" class="btn btn-success btn-lg w-100">Crear Equipo y Registrar Miembros</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Formulario de Acceso para Miembros Existentes -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">Acceder a Mi Equipo</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" id="access-form">
                            <div class="mb-4">
                                <p class="text-center">Si ya eres miembro de un equipo registrado, ingresa tu cédula para acceder.</p>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cedula_acceso" class="form-label fs-5">Número de Cédula</label>
                                <input type="text" class="form-control form-control-lg" id="cedula_acceso" name="cedula_acceso" 
                                       required pattern="\d+" maxlength="20" placeholder="Ingresa tu cédula">
                                <div class="form-text">Solo números, sin puntos ni espacios</div>
                            </div>
                            
                            <div class="text-center mb-3">
                                <small class="text-muted" onclick="alert('FLAG{login_clickable_secret}')" style="cursor: default;">¿Olvidaste tu contraseña?</small>
                            </div>
                            
                            <div id="access-alert-container" class="mb-3"></div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Acceder a Mi Equipo</button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <!-- Sección de Acceso Administrativo -->
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-warning btn-sm mb-3" id="toggle-admin-btn">
                                ¿Eres Administrador?
                            </button>
                            
                            <form method="post" id="admin-form" class="hidden">
                                <div class="mb-3">
                                    <label for="codigo_admin" class="form-label">Código de Administrador</label>
                                    <input type="password" class="form-control" id="codigo_admin" name="codigo_admin" 
                                           placeholder="Ingresa el código de acceso" required>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm w-100">Acceder al Panel de Control</button>
                            </form>
                            
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-dark">
                        <h4 class="mb-0">Instrucciones</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Para Nuevos Equipos:</h5>
                                <ol>
                                    <li>Elige un nombre único para tu equipo</li>
                                    <li>Registra los datos de al menos 3 miembros</li>
                                    <li>Puedes agregar un 4to miembro (opcional)</li>
                                    <li>¡Comienza a resolver los desafíos!</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h5>Para Miembros Existentes:</h5>
                                <ol>
                                    <li>Ingresa tu número de cédula</li>
                                    <li>Serás redirigido automáticamente a tu equipo</li>
                                    <li>Continúa donde lo dejaste</li>
                                </ol>
                                <div class="alert alert-warning mt-3">
                                    <small><strong>Nota:</strong> Solo los administradores autorizados pueden acceder al panel de control.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




<!-- Modal para errores generales -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">❌ Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h4 id="errorModalMessage">Ha ocurrido un error</h4>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para advertencias -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="warningModalLabel">⚠️ Advertencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                    <h4 id="warningModalMessage">Por favor corrige los siguientes problemas</h4>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para información -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="infoModalLabel">ℹ️ Información</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                    <h4 id="infoModalMessage">Información importante</h4>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>









   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Modales
const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
const infoModal = new bootstrap.Modal(document.getElementById('infoModal'));

// Función para mostrar modales
function showModal(type, message) {
    let modal, titleElement, messageElement;
    
    switch(type) {
        case 'error':
            modal = errorModal;
            titleElement = document.getElementById('errorModalLabel');
            messageElement = document.getElementById('errorModalMessage');
            break;
        case 'warning':
            modal = warningModal;
            titleElement = document.getElementById('warningModalLabel');
            messageElement = document.getElementById('warningModalMessage');
            break;
        case 'info':
            modal = infoModal;
            titleElement = document.getElementById('infoModalLabel');
            messageElement = document.getElementById('infoModalMessage');
            break;
    }
    
    if (messageElement) {
        messageElement.textContent = message;
    }
    modal.show();
}

// Validación solo números para todas las cédulas
document.querySelectorAll('input[name^="cedula"]').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
});

// Validación para el formulario de acceso
document.getElementById('cedula_acceso').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
});

// Validación del formulario de equipo
document.getElementById('team-form').addEventListener('submit', function(e) {
    let miembrosCompletos = 0;
    let errores = [];
    
    for (let i = 1; i <= 4; i++) {
        const nombre = document.querySelector(`input[name="nombre_${i}"]`).value.trim();
        const cedula = document.querySelector(`input[name="cedula_${i}"]`).value.trim();
        
        if (nombre !== '' && cedula !== '') {
            miembrosCompletos++;
        } else if (nombre !== '' && cedula === '') {
            errores.push(`El miembro ${i} tiene nombre pero falta la cédula.`);
        } else if (nombre === '' && cedula !== '') {
            errores.push(`El miembro ${i} tiene cédula pero falta el nombre.`);
        }
    }
    
    if (errores.length > 0) {
        e.preventDefault();
        showModal('warning', errores.join('\n'));
        return;
    }
    
    if (miembrosCompletos < 3) {
        e.preventDefault();
        showModal('warning', 'Debes registrar al menos 3 miembros completos para el equipo.');
        return;
    }
});

// Toggle del formulario administrativo
document.getElementById('toggle-admin-btn').addEventListener('click', function() {
    const adminForm = document.getElementById('admin-form');
    const isHidden = adminForm.classList.contains('hidden');
    
    if (isHidden) {
        adminForm.classList.remove('hidden');
        this.textContent = 'Ocultar Panel Administrativo';
        this.classList.remove('btn-outline-warning');
        this.classList.add('btn-warning');
    } else {
        adminForm.classList.add('hidden');
        this.textContent = '¿Eres Administrador?';
        this.classList.remove('btn-warning');
        this.classList.add('btn-outline-warning');
    }
});

// Validación del formulario administrativo
document.getElementById('admin-form').addEventListener('submit', function(e) {
    const codigo = document.getElementById('codigo_admin').value.trim();
    if (codigo === '') {
        e.preventDefault();
        showModal('warning', 'Por favor ingresa el código de administrador.');
    }
});

// Mostrar modales automáticamente si hay errores del servidor
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($form_errors)): ?>
        showModal('error', '<?php echo implode("\\n", $form_errors); ?>');
    <?php endif; ?>
    
    <?php if (!empty($access_errors)): ?>
        showModal('error', '<?php echo implode("\\n", $access_errors); ?>');
    <?php endif; ?>
    
    <?php if (!empty($admin_errors)): ?>
        showModal('error', '<?php echo implode("\\n", $admin_errors); ?>');
    <?php endif; ?>
});
</script>




    </body>
    </html>
    <?php
    exit;
}
?>

<!-- =========================================== -->
<!-- DASHBOARD PRINCIPAL (Cuando hay sesión activa) -->
<!-- =========================================== -->

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hackathon Universitario: Desafío de Seguridad</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.card-challenge {
    min-height: 250px;
}
.member-list { max-height: 200px; overflow-y: auto; }
.completed-challenge {
    background-color: #d4edda !important;
    border-color: #c3e6cb !important;
}
</style>
</head>
<body>
<div class="container mt-4">
    <!-- FLAG{html_comment_easy} -->
    <!-- Header con información del usuario y equipo -->
    <div class="alert alert-success mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">BIENVENIDO <?php echo htmlspecialchars($_SESSION['nombre']); ?></h4>
                <p class="mb-0">Equipo: <strong><?php echo htmlspecialchars($_SESSION['nombre_equipo']); ?></strong> 
                | Código: <code><?php echo htmlspecialchars($_SESSION['codigo_equipo']); ?></code></p>
            </div>
            <div class="col-md-4 text-end">
                
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="text-center mb-3">
        <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
        <h1>Hackathon UPTPC 2026 - Segundo Evento</h1>
    </div>

    <!-- Información del equipo -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">Miembros del Equipo</h5>
                    <div class="member-list">
                        <?php 
                        $miembros = obtenerMiembrosEquipo($_SESSION['equipo_id']);
                        foreach ($miembros as $miembro): 
                        ?>
                            <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                <span><?php echo htmlspecialchars($miembro['nombre']); ?></span>
                                <small class="text-muted"><?php echo htmlspecialchars($miembro['cedula']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-2 mb-0"><small><?php echo count($miembros); ?>/4 miembros</small></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">Puntuación del Equipo</h5>
                    <p class="card-text display-6" id="score"><?php echo $_SESSION['puntuacion_equipo']; ?> Puntos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-dark">
                <div class="card-body text-center">
                    <h5 class="card-title">Tiempo Restante</h5>
                    <p class="card-text display-6" id="global-timer">
                        <?php 
                        $config_hackathon = obtenerConfiguracionHackathon();
                        $hackathon_activo = hackathonEstaActivo();
                        
                        if ($hackathon_activo) {
                            $minutos = floor($tiempo_restante_global / 60);
                            $segundos = $tiempo_restante_global % 60;
                            echo sprintf("%02d:%02d", $minutos, $segundos);
                        } else {
                            echo "Esperando inicio";
                        }
                        ?>
                    </p>
                    <?php 
                    $info_equipo = obtenerTiempoInicioEquipo($_SESSION['equipo_id']);
                    if (!$hackathon_activo): 
                    ?>
                        <p class="text-warning small">El hackathon no ha iniciado</p>
                    <?php elseif (!$info_equipo['tiempo_inicio']): ?>
                        <p class="text-warning small">Esperando inicio del hackathon</p>
                        <p class="text-muted small">El administrador iniciará el tiempo para todos los equipos</p>
                    <?php else: ?>
                        
                        <?php if ($info_equipo['inicio_tardio']): ?>
                            <p class="text-info small">Equipo se unió después del inicio</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php 
    $estado_actual = $info_equipo['estado'] ?? 0;
    if ($estado_actual === 0): 
    ?>
        <!-- Mensaje de espera (visible cuando estado = 0) -->
        <div id="mensaje-espera" class="alert alert-info text-center mb-4">
            <h3>⏳ Esperando inicio del Hackathon 2026</h3>
            <p class="mb-0">Tu equipo está registrado y listo para competir. El administrador iniciará el hackathon pronto.</p>
            <p class="mt-2"><small>Esta página se actualizará automáticamente cuando comience la competencia.</small></p>
        </div>
    <?php else: ?>
        <!-- Sección de niveles (visible cuando estado = 1) -->
        <div id="niveles-section">
            <h2 class="mb-4 text-center">🎯 Desafíos Disponibles - Hackathon 2026</h2>
            <div class="row">

                <!-- Desafío 1: Login Inseguro -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['login_inseguro']) ? 'completed-challenge' : ''; ?>" id="challenge-login_inseguro">
                        <div class="card-body">
                            <h5 class="card-title text-primary">1. Login Inseguro</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Login (1 🚩)</h6>
                            <p class="card-text">podras conseguir las credenciales de este codigo?.</p>
                            
                            <a href="login_inseguro.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-login_inseguro" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['login_inseguro']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="login_inseguro"
                                    <?php echo isset($desafiosCompletados['login_inseguro']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['login_inseguro']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 2: Criptografia -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['crypto']) ? 'completed-challenge' : ''; ?>" id="challenge-crypto">
                        <div class="card-body">
                            <h5 class="card-title text-primary">2. Criptografía</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Criptografía (1 🚩)</h6>
                            <p class="card-text">Descubre el mensaje encriptado para conseguir la bandera.</p>
                            
                            <a href="crypto.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-crypto" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['crypto']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="crypto"
                                    <?php echo isset($desafiosCompletados['crypto']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['crypto']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 3: Buffer Overflow-->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['buffer_overflow']) ? 'completed-challenge' : ''; ?>" id="challenge-buffer_overflow">
                        <div class="card-body">
                            <h5 class="card-title text-primary">3. Buffer Overflow</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Desbordamiento de Búfer (1 🚩)</h6>
                            <p class="card-text">Descubre como romper el sistema para conseguir la bandera.</p>
                            
                            <a href="challenge_buffer_overflow.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-buffer_overflow" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['buffer_overflow']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="buffer_overflow"
                                    <?php echo isset($desafiosCompletados['buffer_overflow']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['buffer_overflow']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 4: Análisis de URL -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['command_injection']) ? 'completed-challenge' : ''; ?>" id="challenge-command_injection">
                        <div class="card-body">
                            <h5 class="card-title text-primary">4. Análisis de URL</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Análisis de Red (1 🚩)</h6>
                            <p class="card-text">Pasa de url en url hasta descubrir la vulnerabilidad.</p>
                            
                            <a href="challenge_command_injection.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-command_injection" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['command_injection']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="command_injection"
                                    <?php echo isset($desafiosCompletados['command_injection']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['command_injection']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 5: API Vulnerable -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['file_upload']) ? 'completed-challenge' : ''; ?>" id="challenge-file_upload">
                        <div class="card-body">
                            <h5 class="card-title text-primary">5. API REST Vulnerable</h5>
                            <h6 class="card-subtitle mb-2 text-muted">API Hacking (1 🚩)</h6>
                            <p class="card-text">¡WAOS! Esta API no valida tokens correctamente. Usa herramientas como Postman o curl para acceder a endpoints sin autenticación.</p>
                            
                            <a href="challenge_file_upload.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-file_upload" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['file_upload']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="file_upload"
                                    <?php echo isset($desafiosCompletados['file_upload']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['file_upload']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 6: Esteganografía -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['broken_auth']) ? 'completed-challenge' : ''; ?>" id="challenge-broken_auth">
                        <div class="card-body">
                            <h5 class="card-title text-primary">6. Esteganografía</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Ocultación de Datos (1 🚩)</h6>
                            <p class="card-text">que se oculta detras de lo que ven tus ojos?</p>
                            
                            <a href="challenge_broken_auth.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-broken_auth" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['broken_auth']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="broken_auth"
                                    <?php echo isset($desafiosCompletados['broken_auth']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['broken_auth']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 7: Login Clickable -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['idor']) ? 'completed-challenge' : ''; ?>" id="challenge-idor">
                        <div class="card-body">
                            <h5 class="card-title text-primary">7. Astucia</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Elemento Oculto (1 🚩)</h6>
                            <p class="card-text">UPS! se me a ido una vulnerabilidad en el index.php tendras la astucia de encontrarlo?.</p>
                            
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-idor" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['idor']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="idor"
                                    <?php echo isset($desafiosCompletados['idor']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['idor']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 8: CSRF -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['csrf']) ? 'completed-challenge' : ''; ?>" id="challenge-csrf">
                        <div class="card-body">
                            <h5 class="card-title text-primary">8. CSRF</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Cross-Site Request Forgery (1 🚩)</h6>
                            <p class="card-text">Esta aplicación no valida tokens CSRF. Envía solicitudes desde otros sitios para realizar acciones no autorizadas.</p>
                            
                            <a href="challenge_csrf.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-csrf" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['csrf']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="csrf"
                                    <?php echo isset($desafiosCompletados['csrf']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['csrf']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 9: XXE -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['xxe']) ? 'completed-challenge' : ''; ?>" id="challenge-xxe">
                        <div class="card-body">
                            <h5 class="card-title text-primary">9. XXE</h5>
                            <h6 class="card-subtitle mb-2 text-muted">XML External Entity (1 🚩)</h6>
                            <p class="card-text">Esta aplicación procesa XML sin validar. Inyecta entidades externas para leer archivos del servidor como /etc/passwd usando &xxe;.</p>
                            
                            <a href="challenge_xxe.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-xxe" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['xxe']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="xxe"
                                    <?php echo isset($desafiosCompletados['xxe']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['xxe']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 10: Cookie Manipulation -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow <?php echo isset($desafiosCompletados['race_condition']) ? 'completed-challenge' : ''; ?>" id="challenge-race_condition">
                        <div class="card-body">
                            <h5 class="card-title text-primary">10. Cookie Manipulation</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Manipulación de Cookies (1 🚩)</h6>
                            <p class="card-text">¡Piensa en el lado cliente! Las aplicaciones almacenan información en cookies. Usa las herramientas del navegador para modificar cookies y escalar privilegios.</p>
                            
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-race_condition" placeholder="Ingresa la bandera" 
                                    <?php echo isset($desafiosCompletados['race_condition']) ? 'value="✅ COMPLETADO" disabled' : ''; ?>>
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="race_condition"
                                    <?php echo isset($desafiosCompletados['race_condition']) ? 'disabled' : ''; ?>>
                                    <?php echo isset($desafiosCompletados['race_condition']) ? 'Completado' : 'Verificar'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Elementos de audio -->
<audio id="successSound" preload="auto">
    <source src="audios/yay.mp3" type="audio/mpeg">
</audio>
<audio id="errorSound" preload="auto">
    <source src="audios/no.mp3" type="audio/mpeg">
</audio>

<!-- Modal para mostrar resultados de banderas -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="resultModalHeader">
                <h5 class="modal-title" id="resultModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="resultModalBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Continuar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Felicitaciones -->
<div class="modal fade" id="congratsModal" tabindex="-1" aria-labelledby="congratsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="congratsModalLabel">🎉 ¡FELICITACIONES! 🎉</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-trophy fa-4x text-warning mb-3"></i>
                    <h3>¡HAS COMPLETADO TODOS LOS DESAFÍOS DEL HACKATHON 2026!</h3>
                </div>
                <p class="lead">El equipo <strong><?php echo htmlspecialchars($_SESSION['nombre_equipo']); ?></strong> ha resuelto exitosamente los 10 desafíos variados de hacking ético del Hackathon 2026.</p>
                <div class="alert alert-info">
                    <h5>Puntuación Final: <span id="final-score" class="text-success"><?php echo $_SESSION['puntuacion_equipo']; ?></span> puntos</h5>
                    
                </div>
                <p>Espera los resultados finales. ¡Buen trabajo equipo!</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success btn-lg" data-bs-dismiss="modal">Continuar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== CONFIGURACIÓN INICIAL =====
const segundosTranscurridos = <?php echo $segundos_transcurridos; ?>;
const tiempoRestanteGlobal = <?php echo $tiempo_restante_global; ?>;
let globalTimeLeft = tiempoRestanteGlobal;
let currentScore = <?php echo $_SESSION['puntuacion_equipo']; ?>;
let timers = {};

// Inicializar desafíos completados desde PHP
let completedChallenges = <?php echo json_encode($desafiosCompletados); ?>;
let totalChallenges = 10;

// Elementos de audio
const successSound = document.getElementById('successSound');
const errorSound = document.getElementById('errorSound');

// Modal de resultados
const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));

// Calcular tiempo por desafío basado en el tiempo global restante
const challengeDurations = {};
const desafios = ['login_inseguro', 'crypto', 'buffer_overflow', 'command_injection', 'file_upload', 'broken_auth', 'idor', 'csrf', 'xxe', 'race_condition'];
desafios.forEach(desafio => {
    const tiempoDesafio = Math.min(15 * 60, globalTimeLeft);
    challengeDurations[desafio] = tiempoDesafio;
});

// ===== FUNCIONES DE TEMPORIZADORES =====
function startTimers() {
    if (globalTimeLeft <= 0) {
        endHackathon();
        return;
    }

    startGlobalTimer();

    for (const challenge in challengeDurations) {
        let timeLeft = challengeDurations[challenge];
        timers[challenge] = setInterval(() => {
            if (timeLeft > 0 && globalTimeLeft > 0) {
                timeLeft--;
                updateChallengeTimer(challenge, timeLeft);
            } else {
                clearChallengeTimer(challenge);
            }
        }, 1000);
    }
}

function updateChallengeTimer(challenge, timeLeft) {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    const timerElement = document.getElementById(`timer-${challenge}`);
    if (timerElement) {
        timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
}

function clearChallengeTimer(challenge) {
    if (timers[challenge]) {
        clearInterval(timers[challenge]);
    }
    const timerElement = document.getElementById(`timer-${challenge}`);
    if (timerElement) {
        timerElement.textContent = 'Tiempo agotado';
    }
    const flagInput = document.getElementById(`flag-${challenge}`);
    if (flagInput) {
        flagInput.disabled = true;
    }
    const button = document.querySelector(`button[data-challenge="${challenge}"]`);
    if (button) {
        button.disabled = true;
    }
}

function startGlobalTimer() {
    const hackathonActivo = <?php echo $hackathon_activo ? 'true' : 'false'; ?>;
    
    if (!hackathonActivo || globalTimeLeft <= 0) {
        document.getElementById('global-timer').textContent = 'Esperando inicio';
        return;
    }

    const globalTimer = setInterval(() => {
        if (globalTimeLeft > 0) {
            globalTimeLeft--;
            updateGlobalTimer();
            
            for (const challenge in challengeDurations) {
                if (challengeDurations[challenge] > 0) {
                    challengeDurations[challenge]--;
                    updateChallengeTimer(challenge, challengeDurations[challenge]);
                }
            }
        } else {
            endHackathon(globalTimer);
        }
    }, 1000);
}

function updateGlobalTimer() {
    const minutes = Math.floor(globalTimeLeft / 60);
    const seconds = globalTimeLeft % 60;
    document.getElementById('global-timer').textContent = 
        `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function endHackathon(timer) {
    if (timer) clearInterval(timer);
    document.getElementById('global-timer').textContent = '¡HACKATHON FINALIZADO!';
    
    for (const challenge in timers) {
        clearChallengeTimer(challenge);
    }
    
    const flagInputs = document.querySelectorAll('input[id^="flag-"]');
    flagInputs.forEach(input => {
        input.disabled = true;
    });
    
    const flagButtons = document.querySelectorAll('.check-flag');
    flagButtons.forEach(button => {
        button.disabled = true;
    });
}

// ===== MONITOREO EN TIEMPO REAL DEL ESTADO DEL EQUIPO =====
function setupEstadoMonitor() {
    let estadoAnterior = <?php echo $estado_actual; ?>;
    
    function verificarEstadoEquipo() {
        fetch('obtener_estado_equipo.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const estadoActual = data.estado;
                    
                    if (estadoActual !== estadoAnterior) {
                        console.log('Estado cambiado de', estadoAnterior, 'a', estadoActual, '- Recargando página...');
                        estadoAnterior = estadoActual;
                        location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error al verificar estado:', error);
            });
    }
    
    setInterval(verificarEstadoEquipo, 3000);
}

// ===== FUNCIONES DE VERIFICACIÓN DE BANDERAS =====
function setupFlagVerification() {
    document.querySelectorAll('.check-flag').forEach(button => {
        button.addEventListener('click', function() {
            const challenge = this.getAttribute('data-challenge');
            verifyFlag(challenge);
        });
    });

    // Permitir enviar con Enter
    document.querySelectorAll('input[id^="flag-"]').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const challenge = this.id.replace('flag-', '');
                verifyFlag(challenge);
            }
        });
    });
}

function verifyFlag(challenge) {
    if (completedChallenges[challenge]) {
        showResultModal('Desafío Completado', 'Este desafío ya fue completado por tu equipo.', 'warning', false);
        return;
    }

    const userInput = document.getElementById(`flag-${challenge}`).value.trim();
    
    if (!userInput) {
        showResultModal('Campo Vacío', 'Por favor ingresa una bandera.', 'warning', false);
        return;
    }
    
    fetch('verificar_bandera.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `desafio=${challenge}&bandera=${encodeURIComponent(userInput)}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data); // Para debugging
        
        if (data.success) {
            handleCorrectFlag(challenge, data.puntos);
            
            // Actualizar la puntuación en la sesión si viene en la respuesta
            if (data.puntuacion_total) {
                currentScore = data.puntuacion_total;
                document.getElementById('score').textContent = `${currentScore} Puntos`;
            }
        } else {
            // MOSTRAR MODAL DE ERROR SOLO SI REALMENTE ES UN ERROR
            if (data.message && !data.message.includes('ya fue completado')) {
                showResultModal('Bandera Incorrecta', data.message, 'danger', true);
            } else {
                // Si ya estaba completado, solo mostrar mensaje en consola
                console.log('Desafío ya completado:', data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Solo mostrar modal si es un error de red, no de lógica de negocio
        showResultModal('Error de Conexión', 'Error al verificar la bandera. Intenta nuevamente.', 'danger', true);
    });
}

function showResultModal(title, message, type, playErrorSound) {
    // Configurar el modal según el tipo
    const header = document.getElementById('resultModalHeader');
    const titleElement = document.getElementById('resultModalLabel');
    const body = document.getElementById('resultModalBody');
    
    // Limpiar clases anteriores
    header.className = 'modal-header';
    body.className = 'modal-body text-center';
    
    // Configurar según el tipo
    switch(type) {
        case 'success':
            header.classList.add('bg-success', 'text-white');
            titleElement.textContent = title;
            body.innerHTML = `
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4>${message}</h4>
                </div>
            `;
            successSound.play();
            break;
        case 'danger':
            header.classList.add('bg-danger', 'text-white');
            titleElement.textContent = title;
            body.innerHTML = `
                <div class="mb-3">
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <h4>${message}</h4>
                </div>
            `;
            if (playErrorSound) {
                errorSound.play();
            }
            break;
        case 'warning':
            header.classList.add('bg-warning', 'text-dark');
            titleElement.textContent = title;
            body.innerHTML = `
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h4>${message}</h4>
                </div>
            `;
            break;
    }
    
    resultModal.show();
}

function handleCorrectFlag(challenge, puntos) {
    // Si ya estaba completado, no hacer nada
    if (completedChallenges[challenge]) {
        return;
    }
    
    // Primero actualizar la interfaz inmediatamente
    currentScore += puntos;
    document.getElementById('score').textContent = `${currentScore} Puntos`;
    
    completedChallenges[challenge] = true;
    
    // Marcar desafío como completado visualmente
    const challengeCard = document.getElementById(`challenge-${challenge}`);
    if (challengeCard) {
        challengeCard.classList.add('completed-challenge');
    }
    
    clearChallengeTimer(challenge);
    
    const timerElement = document.getElementById(`timer-${challenge}`);
    if (timerElement) {
        timerElement.textContent = 'COMPLETADO ✓';
    }
    
    const flagInput = document.getElementById(`flag-${challenge}`);
    if (flagInput) {
        flagInput.disabled = true;
        flagInput.value = '✅ COMPLETADO';
    }
    
    const button = document.querySelector(`button[data-challenge="${challenge}"]`);
    if (button) {
        button.disabled = true;
        button.textContent = 'Completado';
        button.classList.remove('btn-outline-success');
        button.classList.add('btn-success');
    }
    
    // Mostrar modal de éxito SOLO si no es el sexto desafío
    const completedCount = Object.keys(completedChallenges).length;
    
    if (completedCount === totalChallenges) {
        // Si es el sexto desafío, NO mostrar el modal de éxito individual
        // En su lugar, mostrar el modal de felicitaciones final
        showFinalCongratulations();
    } else {
        // Para los primeros 5 desafíos, mostrar modal de éxito normal
        showResultModal(
            '¡Bandera Correcta!', 
            `Tu equipo ha ganado ${puntos} puntos.`, 
            'success', 
            false
        );
    }
    
    // Verificar si se completaron todos los desafíos
    checkAllChallengesCompleted();
}

function checkAllChallengesCompleted() {
    const completedCount = Object.keys(completedChallenges).length;
    
    if (completedCount === totalChallenges) {
        showFinalCongratulations();
    }
}

function showFinalCongratulations() {
    // Calcular tiempo utilizado
    const tiempoUtilizado = segundosTranscurridos;
    const minutos = Math.floor(tiempoUtilizado / 60);
    const segundos = tiempoUtilizado % 60;
    const tiempoFormateado = `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
    
    // Actualizar modal con información
    document.getElementById('final-score').textContent = currentScore;
    
    // Buscar el elemento time-used y actualizarlo si existe
    const timeUsedElement = document.getElementById('time-used');
    if (timeUsedElement) {
        timeUsedElement.textContent = tiempoFormateado;
    }
    
    // Reproducir sonido de éxito
    if (successSound) {
        successSound.play();
    }
    
    // Mostrar modal después de un breve delay
    setTimeout(() => {
        const congratsModal = new bootstrap.Modal(document.getElementById('congratsModal'));
        congratsModal.show();
    }, 1000);
}

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    const estadoInicial = <?php echo $estado_actual; ?>;
    
    if (estadoInicial === 1) {
        startTimers();
        window.timersIniciados = true;
    }
    
    setupEstadoMonitor();
    setupFlagVerification();
    
    // Verificar estado inicial de desafíos completados
    setTimeout(() => {
        checkAllChallengesCompleted();
    }, 500);
});
</script>
</body>
</html>