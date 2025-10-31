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
        mostrarAlerta('El nombre del equipo es obligatorio.');
    }
    
    // Validar que haya al menos 3 miembros
    $miembros_minimos = 0;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty(trim($_POST["nombre_$i"])) && !empty(trim($_POST["cedula_$i"]))) {
            $miembros_minimos++;
        }
    }
    
    if ($miembros_minimos < 3) {
        mostrarAlerta('Debes registrar al menos 3 miembros para el equipo.');
    }
    
    // Registrar el equipo
    $equipo_id = registrarEquipo($nombre_equipo);
    if (!$equipo_id) {
        mostrarAlerta('Error al crear el equipo. El nombre puede estar en uso.');
    }
    
    // Registrar los miembros
    $miembros_registrados = 0;
    for ($i = 1; $i <= 4; $i++) {
        $nombre = trim($_POST["nombre_$i"]);
        $cedula = trim($_POST["cedula_$i"]);
        
        if (!empty($nombre) && !empty($cedula)) {
            if (!validarCedula($cedula)) {
                mostrarAlerta("La cédula del miembro $i solo debe contener números.");
            }
            
            if (usuarioExiste($cedula)) {
                mostrarAlerta("La cédula $cedula ya está registrada en otro equipo.");
            }
            
            if (!registrarParticipante($nombre, $cedula, $equipo_id)) {
                mostrarAlerta("Error al registrar el miembro $i.");
            }
            
            $miembros_registrados++;
        }
    }
    
    // Iniciar sesión con el primer miembro registrado
    $primer_miembro = usuarioExiste(trim($_POST["cedula_1"]));
    if ($primer_miembro) {
        iniciarSesion($primer_miembro);
        header("Location: index.php");
        exit;
    } else {
        mostrarAlerta('Error al iniciar sesión.');
    }

// 3. Si viene del formulario de acceso individual
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cedula_acceso'])) {
    $cedula = trim($_POST['cedula_acceso']);
    
    if (!validarCedula($cedula)) {
        mostrarAlerta('La cédula solo debe contener números.');
    }
    
    // Verificar si el usuario existe
    $participante = usuarioExiste($cedula);
    if (!$participante) {
        mostrarAlerta('No se encontró un equipo registrado con esta cédula.');
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
        mostrarAlerta('Código administrativo incorrecto.');
    }

// 5. Si no hay sesión, mostrar formulario de inicio
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
        <div class="text-center mb-3">
            <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
            <h1>Hackathon UPTPC</h1>
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
                            
                            <!-- Tutor (Obligatorio) -->
                            <div class="member-form">
                                <h6 class="text-primary">Tutor <span class="text-danger">*</span></h6>
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

    <script>
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
        
        for (let i = 1; i <= 4; i++) {
            const nombre = document.querySelector(`input[name="nombre_${i}"]`).value.trim();
            const cedula = document.querySelector(`input[name="cedula_${i}"]`).value.trim();
            
            if (nombre !== '' && cedula !== '') {
                miembrosCompletos++;
            } else if (nombre !== '' && cedula === '') {
                alert(`El miembro ${i} tiene nombre pero falta la cédula.`);
                e.preventDefault();
                return;
            } else if (nombre === '' && cedula !== '') {
                alert(`El miembro ${i} tiene cédula pero falta el nombre.`);
                e.preventDefault();
                return;
            }
        }
        
        if (miembrosCompletos < 3) {
            alert('Debes registrar al menos 3 miembros completos para el equipo.');
            e.preventDefault();
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
            alert('Por favor ingresa el código de administrador.');
            e.preventDefault();
        }
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
                            <h6 class="card-subtitle mb-2 text-muted">Web Hacking (1 🪙)</h6>
                            <p class="card-text">Encuentra una vulnerabilidad en este formulario de inicio de sesión.</p>
                            
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
                            <h6 class="card-subtitle mb-2 text-muted">Análisis de Binarios (1 🪙)</h6>
                            <p class="card-text">Descarga el archivo binario y realiza ingeniería inversa para obtener la contraseña oculta.</p>
                            <p class="fw-bold">Archivo: <a href="reverse_challenge.zip">reverse_challenge.zip</a></p>
                            
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
                            <h6 class="card-subtitle mb-2 text-muted">Descifrado de Mensajes (1 🪙)</h6>
                            <p class="card-text">Descifra el mensaje oculto. haz lo posible para identificar que cifrado es y desencriptarlo.</p>
                            <p class="fw-bold">Cifrado: RkxBR3tFTF9ERVNFTkNSSVBUQURPUl9NQVNURVJ9</p>
                            
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
                            <h6 class="card-subtitle mb-2 text-muted">Ataque de Diccionario (1 🪙)</h6>
                            <p class="card-text">Descarga el archivo ZIP protegido con contraseña y descifra la clave o intenta otro metodo con tal de sacar la bandera del Zip.</p>
                            <p class="fw-bold">Archivo: <a href="secret_files.zip">secret_files.zip</a></p>
                            
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
                            <h6 class="card-subtitle mb-2 text-muted">Metadatos EXIF (1 🪙)</h6>
                            <p class="card-text">Descarga la imagen y analiza sus metadatos EXIF para encontrar la bandera oculta.</p>
                            <p class="fw-bold">Imagen: <a href="mystery_image.jpeg">mystery_image.jpeg</a></p>
                            
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
const challengeDurations = {};
const desafios = ['ctf', 're', 'crypto', 'zip', 'meta'];
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
    
    if (estadoInicial === 1) {
        startTimers();
        window.timersIniciados = true;
    }
    
    setupEstadoMonitor();
    setupFlagVerification();
});
</script>
</body>
</html>