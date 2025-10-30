<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();
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
    
    // ELIMINADO: No forzar inicio automático del tiempo
    
    // Calcular tiempo transcurrido específico del equipo (solo si el hackathon está activo y el equipo tiene tiempo iniciado)
    if ($hackathon_activo && $info_equipo['tiempo_inicio']) {
        $segundos_transcurridos = calcularTiempoTranscurrido($info_equipo['tiempo_inicio']);
    } else {
        $segundos_transcurridos = 0;
    }
    
    // Calcular tiempo restante global
    $tiempo_restante_global = calcularTiempoRestanteGlobal();

// 2. Si viene del formulario de crear equipo (nombre del equipo)
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_equipo'])) {
    $nombre_equipo = trim($_POST['nombre_equipo']);
    
    if (empty($nombre_equipo)) {
        mostrarAlerta('El nombre del equipo es obligatorio.');
    }
    
    // Registrar el equipo
    $equipo_id = registrarEquipo($nombre_equipo);
    if (!$equipo_id) {
        mostrarAlerta('Error al crear el equipo. El nombre puede estar en uso.');
    }
    
    // Guardar el equipo_id en sesión temporal para el registro de miembros
    $_SESSION['equipo_temporal'] = $equipo_id;
    $_SESSION['nombre_equipo_temporal'] = $nombre_equipo;
    
    // Redirigir al formulario de registro del primer miembro
    header("Location: index.php?accion=registrar_miembro");
    exit;

// 3. Si viene del formulario de registro de miembro
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['cedula'])) {
    $nombre = trim($_POST['nombre']);
    $cedula = trim($_POST['cedula']);
    
    if (!validarCedula($cedula)) {
        mostrarAlerta('La cédula solo debe contener números.');
    }
    
    // Verificar que no exista ya la cédula
    if (usuarioExiste($cedula)) {
        mostrarAlerta('La cédula ya está registrada en otro equipo.');
    }
    
    if (!isset($_SESSION['equipo_temporal'])) {
        mostrarAlerta('Error: No hay equipo seleccionado.');
    }
    
    // Registrar participante
    if (registrarParticipante($nombre, $cedula, $_SESSION['equipo_temporal'])) {
        // IMPORTANTE: NO iniciar tiempo automáticamente al registrar el primer miembro
        // El tiempo solo se iniciará cuando el administrador active el hackathon
        // y los equipos accedan después de eso
        
        // Iniciar sesión del usuario
        $participante = usuarioExiste($cedula);
        iniciarSesion($participante);
        
        // Limpiar sesión temporal
        unset($_SESSION['equipo_temporal']);
        unset($_SESSION['nombre_equipo_temporal']);
        
        header("Location: index.php");
        exit;
    } else {
        mostrarAlerta('Error al registrar participante.');
    }

// 4. Si se solicita registrar miembro
} else if (isset($_GET['accion']) && $_GET['accion'] === 'registrar_miembro') {
    if (!isset($_SESSION['equipo_temporal'])) {
        header("Location: index.php");
        exit;
    }
    
    $equipo = obtenerInfoEquipo($_SESSION['equipo_temporal']);
    if (!$equipo) {
        mostrarAlerta('Equipo no encontrado.');
    }
    
    $miembros = obtenerMiembrosEquipo($_SESSION['equipo_temporal']);
    $cantidad_miembros = count($miembros);
    
    // Mostrar formulario de registro de miembro
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Registro de Miembro - Hackathon</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .hidden{display:none!important;}
            .member-badge { background-color: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        </style>
    </head>
    <body>
    <div class="container mt-5">
        <div class="text-center mb-3">
            <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
            <h1>Hackathon UPTPC</h1>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">Registro de Miembro del Equipo</h2>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Información del Equipo</h5>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($equipo['nombre_equipo']); ?></p>
                        <p><strong>Código:</strong> <code><?php echo htmlspecialchars($equipo['codigo_equipo']); ?></code></p>
                        <p><strong>Miembros registrados:</strong> <?php echo $cantidad_miembros; ?>/4</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Miembros del Equipo</h5>
                        <?php if ($cantidad_miembros > 0): ?>
                            <?php foreach ($miembros as $miembro): ?>
                                <div class="member-badge">
                                    <strong><?php echo htmlspecialchars($miembro['nombre']); ?></strong><br>
                                    <small>Cédula: <?php echo htmlspecialchars($miembro['cedula']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Aún no hay miembros registrados</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                
                <h5 class="mb-3"><?php echo ($cantidad_miembros === 0) ? 'Registrar Primer Miembro' : 'Agregar Otro Miembro'; ?></h5>
                
                <form method="post" class="w-75 mx-auto" id="registration-form">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Número de cédula</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" required pattern="\d+" maxlength="20" inputmode="numeric" title="Solo números">
                        <div id="validation-tip" class="text-danger small hidden">Solo se permiten números</div>
                    </div>
                    <div id="alert-container" class="mb-2"></div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success">Registrar Miembro</button>
                        
                        <?php if ($cantidad_miembros >= 1): ?>
                            <a href="index.php?accion=finalizar_equipo" class="btn btn-primary">Finalizar Equipo</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    // Validación solo números para cédula
    document.getElementById('cedula').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    </script>
    </body>
    </html>
    <?php
    exit;

// 5. Si se solicita finalizar equipo
} else if (isset($_GET['accion']) && $_GET['accion'] === 'finalizar_equipo') {
    if (!isset($_SESSION['equipo_temporal'])) {
        header("Location: index.php");
        exit;
    }
    
    // Obtener información del equipo
    $equipo = obtenerInfoEquipo($_SESSION['equipo_temporal']);
    $miembros = obtenerMiembrosEquipo($_SESSION['equipo_temporal']);
    
    // Limpiar sesión temporal
    unset($_SESSION['equipo_temporal']);
    unset($_SESSION['nombre_equipo_temporal']);
    
    // Mostrar mensaje de éxito
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Equipo Finalizado - Hackathon</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
    <div class="container mt-5">
        <div class="text-center mb-3">
            <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
            <h1>Hackathon UPTPC</h1>
        </div>
        
        <div class="alert alert-success text-center">
            <h2>¡Equipo Registrado Exitosamente!</h2>
            <p class="fs-5">El equipo <strong><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></strong> ha sido registrado.</p>
            <p class="fs-5">Código del equipo: <code class="fs-4"><?php echo htmlspecialchars($equipo['codigo_equipo']); ?></code></p>
            <p>Guarda este código para que otros miembros se unan más tarde.</p>
        </div>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary btn-lg">Crear Otro Equipo</a>
            <a href="equipos.php" class="btn btn-secondary btn-lg">Ver Ranking de Equipos</a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;

// 6. Si no hay sesión ni acciones específicas, mostrar formulario de inicio
} else {
    // Si hay sesión temporal, limpiarla
    if (isset($_SESSION['equipo_temporal'])) {
        unset($_SESSION['equipo_temporal']);
        unset($_SESSION['nombre_equipo_temporal']);
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Inicio Hackathon</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 0; border-radius: 15px; }
        </style>
    </head>
    <body>
    <div class="container mt-4">
        <div class="text-center mb-3">
            <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
            <h1>Hackathon UPTPC</h1>
        </div>
        
        <div class="hero-section text-center mb-5">
            <h2 class="display-4 mb-3">Desafío de Seguridad Informática</h2>
            <p class="lead mb-4">¡Forma tu equipo y compite por el primer lugar!</p>
            <p class="mb-4">Equipos de 1 a 4 personas - Tiempo limitado - Múltiples desafíos</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h3 class="mb-0">Crear Nuevo Equipo</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" id="team-form">
                            <div class="mb-3">
                                <label for="nombre_equipo" class="form-label fs-5">Nombre del Equipo</label>
                                <input type="text" class="form-control form-control-lg" id="nombre_equipo" name="nombre_equipo" required placeholder="Ingresa el nombre de tu equipo">
                                <div class="form-text">Este será el nombre oficial de tu equipo en la competencia.</div>
                            </div>
                            <div id="alert-container" class="mb-3"></div>
                            <button type="submit" class="btn btn-success btn-lg w-100">Crear Equipo y Registrar Primer Miembro</button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-3">¿Ya tienes un equipo?</p>
                            <a href="unirse_equipo.php" class="btn btn-outline-primary btn-lg">Unirse a Equipo Existente</a>
                            <a href="equipos.php" class="btn btn-outline-secondary btn-lg ms-2">Ver Ranking</a>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header bg-info text-dark">
                            <h4 class="mb-0">Instrucciones</h4>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li>Crea un equipo con un nombre único</li>
                                <li>Registra al primer miembro del equipo</li>
                                <li>Agrega hasta 3 miembros más (opcional)</li>
                                <li>Finaliza el registro del equipo cuando estés listo</li>
                                <li>¡Comienza a resolver los desafíos!</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
</style>
</head>
<body>
<div class="container mt-4">
    <!-- Header con información del usuario y equipo -->
    <div class="alert alert-success mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">BIENVENIDO <?php echo htmlspecialchars($_SESSION['nombre']); ?></h4>
                <p class="mb-0">Equipo: <strong><?php echo htmlspecialchars($_SESSION['nombre_equipo']); ?></strong> 
                | Código: <code><?php echo htmlspecialchars($_SESSION['codigo_equipo']); ?></code></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="equipos.php" class="btn btn-outline-primary btn-sm">Ver Ranking</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="text-center mb-3">
        <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
        <h1>Hackathon UPTPC</h1>
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
                        <p class="text-success small">Tiempo iniciado: <?php echo date('H:i:s', strtotime($info_equipo['tiempo_inicio'])); ?></p>
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
            <h3>⏳ Esperando inicio del Hackathon</h3>
            <p class="mb-0">Tu equipo está registrado y listo para competir. El administrador iniciará el hackathon pronto.</p>
            <p class="mt-2"><small>Esta página se actualizará automáticamente cuando comience la competencia.</small></p>
        </div>
    <?php else: ?>
        <!-- Sección de niveles (visible cuando estado = 1) -->
        <div id="niveles-section">
            <h2 class="mb-4 text-center">🎯 Desafíos Disponibles</h2>
            <div class="row">

                <!-- Desafío 1: Aplicación Web CTF -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow">
                        <div class="card-body">
                            <h5 class="card-title text-primary">1. Aplicación Web CTF</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Web Hacking (200 Pts)</h6>
                            <p class="card-text">Encuentra una vulnerabilidad en este formulario de inicio de sesión.</p>
                            <p class="fw-bold">Tiempo restante: <span class="text-danger" id="timer-ctf">15:00</span></p>
                            <a href="challenge_ctf.php" class="btn btn-primary">Acceder al Desafío</a>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-ctf" placeholder="Ingresa la bandera">
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="ctf">Verificar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 2: Ingeniería Inversa -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow">
                        <div class="card-body">
                            <h5 class="card-title text-primary">2. Ingeniería Inversa</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Análisis de Binarios (300 Pts)</h6>
                            <p class="card-text">Descarga el archivo binario y realiza ingeniería inversa para obtener la contraseña oculta.</p>
                            <p class="fw-bold">Archivo: <a href="reverse_challenge.zip">reverse_challenge.zip</a></p>
                            <p class="fw-bold">Tiempo restante: <span class="text-danger" id="timer-re">15:00</span></p>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-re" placeholder="Ingresa la bandera">
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="re">Verificar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 3: Criptografía -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow">
                        <div class="card-body">
                            <h5 class="card-title text-primary">3. Criptografía</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Descifrado de Mensajes (250 Pts)</h6>
                            <p class="card-text">Descifra el mensaje oculto. haz lo posible para identificar que cifrado es y desencriptarlo.</p>
                            <p class="fw-bold">Cifrado: RkxBR3tFTF9ERVNFTkNSSVBUQURPUl9NQVNURVJ9</p>
                            <p class="fw-bold">Tiempo restante: <span class="text-danger" id="timer-crypto">15:00</span></p>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-crypto" placeholder="Ingresa la bandera">
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="crypto">Verificar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 4: Fuerza Bruta ZIP -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow">
                        <div class="card-body">
                            <h5 class="card-title text-primary">4. Fuerza Bruta ZIP</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Ataque de Diccionario (275 Pts)</h6>
                            <p class="card-text">Descarga el archivo ZIP protegido con contraseña y utiliza fuerza bruta para encontrar la clave u otro metodo con tal de sacar la bandera del Zip.</p>
                            <p class="fw-bold">Archivo: <a href="secret_files.zip">secret_files.zip</a></p>
                            <p class="fw-bold">Tiempo restante: <span class="text-danger" id="timer-zip">15:00</span></p>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-zip" placeholder="Ingresa la bandera">
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="zip">Verificar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desafío 5: Metadatos de Imagen -->
                <div class="col-md-4 mb-4">
                    <div class="card card-challenge shadow">
                        <div class="card-body">
                            <h5 class="card-title text-primary">5. Análisis Forense</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Metadatos EXIF (225 Pts)</h6>
                            <p class="card-text">Descarga la imagen y analiza sus metadatos EXIF para encontrar la bandera oculta.</p>
                            <p class="fw-bold">Imagen: <a href="mystery_image.jpg">mystery_image.jpg</a></p>
                            <p class="fw-bold">Tiempo restante: <span class="text-danger" id="timer-meta">15:00</span></p>
                            <div class="mt-3">
                                <input type="text" class="form-control" id="flag-meta" placeholder="Ingresa la bandera">
                                <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="meta">Verificar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// ===== CONFIGURACIÓN INICIAL =====
const segundosTranscurridos = <?php echo $segundos_transcurridos; ?>;
const tiempoRestanteGlobal = <?php echo $tiempo_restante_global; ?>;
let globalTimeLeft = tiempoRestanteGlobal;
let currentScore = <?php echo $_SESSION['puntuacion_equipo']; ?>;
let timers = {};
let completedChallenges = {};

// Calcular tiempo por desafío basado en el tiempo global restante
// Cada desafío tiene 15 minutos, pero no puede exceder el tiempo global
const challengeDurations = {};
const desafios = ['ctf', 're', 'crypto', 'zip', 'meta'];
desafios.forEach(desafio => {
    // El tiempo para cada desafío es el mínimo entre 15 minutos y el tiempo global restante
    const tiempoDesafio = Math.min(15 * 60, globalTimeLeft);
    challengeDurations[desafio] = tiempoDesafio;
});

// ===== FUNCIONES DE TEMPORIZADORES =====
function startTimers() {
    // Solo iniciar temporizadores si el hackathon está activo
    if (globalTimeLeft <= 0) {
        endHackathon();
        return;
    }

    // Temporizador global (ACTUALIZADO - ahora sí funciona en tiempo real)
    startGlobalTimer();

    // Temporizadores individuales por desafío
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
    document.getElementById(`timer-${challenge}`).textContent = 
        `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function clearChallengeTimer(challenge) {
    if (timers[challenge]) {
        clearInterval(timers[challenge]);
    }
    document.getElementById(`timer-${challenge}`).textContent = 'Tiempo agotado';
    document.getElementById(`flag-${challenge}`).disabled = true;
    const button = document.querySelector(`button[data-challenge="${challenge}"]`);
    if (button) {
        button.disabled = true;
    }
}

function startGlobalTimer() {
    // Verificar si el hackathon está activo
    const hackathonActivo = <?php echo $hackathon_activo ? 'true' : 'false'; ?>;
    
    if (!hackathonActivo || globalTimeLeft <= 0) {
        document.getElementById('global-timer').textContent = 'Esperando inicio';
        return;
    }

    // Actualizar el temporizador global cada segundo
    const globalTimer = setInterval(() => {
        if (globalTimeLeft > 0) {
            globalTimeLeft--;
            updateGlobalTimer();
            
            // También actualizar los tiempos de los desafíos
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
    
    // Detener todos los temporizadores individuales
    for (const challenge in timers) {
        clearChallengeTimer(challenge);
    }
    
    // Deshabilitar todos los inputs de banderas
    const flagInputs = document.querySelectorAll('input[id^="flag-"]');
    flagInputs.forEach(input => {
        input.disabled = true;
    });
    
    const flagButtons = document.querySelectorAll('.check-flag');
    flagButtons.forEach(button => {
        button.disabled = true;
    });
}

// Deshabilita inputs si el tiempo está agotado al cargar la página
function disableExpiredChallenges() {
    for (const challenge in challengeDurations) {
        if (challengeDurations[challenge] <= 0) {
            document.getElementById(`timer-${challenge}`).textContent = 'Tiempo agotado';
            document.getElementById(`flag-${challenge}`).disabled = true;
            const button = document.querySelector(`button[data-challenge="${challenge}"]`);
            if (button) {
                button.disabled = true;
            }
        }
    }
    
    if (globalTimeLeft <= 0) {
        document.getElementById('global-timer').textContent = '¡HACKATHON FINALIZADO!';
    }
}

// ===== MONITOREO EN TIEMPO REAL DEL ESTADO DEL EQUIPO =====
function setupEstadoMonitor() {
    let estadoAnterior = <?php echo $estado_actual; ?>;
    
    // Función para verificar el estado del equipo
    function verificarEstadoEquipo() {
        fetch('obtener_estado_equipo.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const estadoActual = data.estado;
                    
                    // Solo recargar si el estado cambió
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
    
    // Verificar estado cada 3 segundos
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
}

function verifyFlag(challenge) {
    if (completedChallenges[challenge]) {
        alert('Este desafío ya fue completado por tu equipo.');
        return;
    }

    const userInput = document.getElementById(`flag-${challenge}`).value.trim();
    
    if (!userInput) {
        alert('Por favor ingresa una bandera.');
        return;
    }
    
    // Llamada AJAX para verificar bandera
    fetch('verificar_bandera.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `desafio=${challenge}&bandera=${encodeURIComponent(userInput)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            handleCorrectFlag(challenge, data.puntos);
        } else {
            alert(data.message || 'Bandera Incorrecta. Sigue buscando.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al verificar la bandera.');
    });
}

function handleCorrectFlag(challenge, puntos) {
    alert(`¡Bandera Correcta! Tu equipo ha ganado ${puntos} puntos.`);
    
    currentScore += puntos;
    document.getElementById('score').textContent = `${currentScore} Puntos`;
    
    completedChallenges[challenge] = true;
    clearChallengeTimer(challenge);
    document.getElementById(`timer-${challenge}`).textContent = 'COMPLETADO';
    document.getElementById(`flag-${challenge}`).disabled = true;
    
    const button = document.querySelector(`button[data-challenge="${challenge}"]`);
    if (button) {
        button.disabled = true;
    }
}

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    const estadoInicial = <?php echo $estado_actual; ?>;
    
    // Solo iniciar temporizadores si el estado es 1 (compitiendo)
    if (estadoInicial === 1) {
        startTimers();
        window.timersIniciados = true;
    }
    
    // Configurar monitoreo en tiempo real
    setupEstadoMonitor();
    
    // Configurar verificación de banderas
    setupFlagVerification();
    
    // Deshabilitar desafíos expirados
    disableExpiredChallenges();
});
</script>
</body>
</html>