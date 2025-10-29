<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/conf/functions.php';

// 1. Si ya está en sesión, calcula el tiempo y muestra dashboard
if (isset($_SESSION['cedula'])) {
    $participante = validarSesion();
    if (!$participante) {
        header("Location: index.php");
        exit;
    }
    $segundos_transcurridos = calcularTiempoTranscurrido($_SESSION['tiempo_inicio']);

// 2. Si viene del formulario de login (solo cédula)
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cedula']) && !isset($_POST['nombre'])) {
    $cedula = trim($_POST['cedula']);
    
    if (!validarCedula($cedula)) {
        mostrarAlerta('La cédula solo debe contener números.');
    }
    
    $participante = usuarioExiste($cedula);

    if ($participante) {
        // Usuario existe, inicia sesión
        iniciarSesion($participante);
        header("Location: index.php");
        exit;
    } else {
        // Usuario no existe, mostrar formulario de registro con cédula bloqueada
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Registro Hackathon</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>.hidden{display:none!important;}</style>
        </head>
        <body>
        <div class="container mt-5">
            <div class="text-center mb-3">
                <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
            <h1>Hackathon UPTPC</h1>
        </div>
            <h2 class="mb-4">Registro de Participante</h2>
            <form method="post" class="w-50 mx-auto" id="registration-form">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="cedula" class="form-label">Número de cédula</label>
                    <input type="text" class="form-control" id="cedula" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>" readonly>
                    <div id="validation-tip" class="text-danger small hidden">Solo se permiten números</div>
                </div>
                <div id="alert-container" class="mb-2"></div>
                <button type="submit" class="btn btn-primary">Registrar</button>
            </form>
        </div>
        <script>
        // Puedes omitir la validación JS aquí porque el campo está readonly
        </script>
        </body>
        </html>
        <?php
        exit;
    }

// 3. Si viene del formulario de registro (nombre y cédula)
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['cedula'])) {
    $nombre = trim($_POST['nombre']);
    $cedula = trim($_POST['cedula']);
    
    if (!validarCedula($cedula)) {
        mostrarAlerta('La cédula solo debe contener números.');
    }
    
    // Verifica que no exista ya la cédula (por si acaso)
    if (usuarioExiste($cedula)) {
        mostrarAlerta('La cédula ya está registrada.');
    }
    
    if (registrarParticipante($nombre, $cedula)) {
        $participante = usuarioExiste($cedula);
        iniciarSesion($participante);
        header("Location: index.php");
        exit;
    } else {
        mostrarAlerta('Error al registrar participante.');
    }

// 4. Si no hay sesión ni POST, mostrar formulario de login (solo cédula)
} else {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ingreso Hackathon</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>.hidden{display:none!important;}</style>
    </head>
    <body>
    <div class="container mt-5">
        <div class="text-center mb-3">
            <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
       <h1>Hackathon UPTPC</h1>
        </div>
        <h2 class="mb-4">Ingresa tu número de cédula</h2>
        <form method="post" class="w-50 mx-auto" id="login-form">
            <div class="mb-3">
                <label for="cedula" class="form-label">Número de cédula</label>
                <input type="text" class="form-control" id="cedula" name="cedula" required pattern="\d+" maxlength="20" inputmode="numeric" title="Solo números">
                <div id="validation-tip" class="text-danger small hidden">Solo se permiten números</div>
            </div>
            <div id="alert-container" class="mb-2"></div>
            <button type="submit" class="btn btn-primary">Ingresar</button>
        </form>
    </div>
    <script>
    // Validación solo números para login
    document.getElementById('cedula').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    </script>
    </body>
    </html>
    <?php
    exit;
}
?>
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
</style>
</head>
<body>
<div class="container mt-5">
    <?php if (isset($_SESSION['nombre'])): ?>
        <div class="alert alert-success text-center fs-4 mb-4">
            BIENVENIDO <?php echo htmlspecialchars($_SESSION['nombre']); ?>
        </div>
    <?php endif; ?>
    <div class="text-center mb-3">
        <img src="img/img.jpg" alt="Logo Hackathon" style="max-width:800px;">
    <h1>Hackathon UPTPC</h1>
</div>
    <h1 class="text-center mb-4">Bienvenido al Hackathon: Desafío de Seguridad Informática</h1>
    <p class="text-center lead">¡Encuentra las banderas y acumula la mayor cantidad de puntos!</p>

    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Puntuación Total</h5>
                    <p class="card-text fs-3" id="score">0 Puntos</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-info text-dark">
                <div class="card-body">
                    <h5 class="card-title">Tiempo Restante (Global)</h5>
                    <p class="card-text fs-3" id="global-timer">00:45:00</p>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mb-4">Desafíos Disponibles</h2>
    <div class="row">

        <div class="col-md-4 mb-4">
            <div class="card card-challenge shadow">
                <div class="card-body">
                    <h5 class="card-title text-primary">1. Aplicación Web CTF</h5>
                    <h6 class="card-subtitle mb-2 text-muted">Web Hacking (200 Pts)</h6>
                    <p class="card-text">Encuentra una vulnerabilidad en este formulario de inicio de sesión. La bandera está oculta en la base de datos.</p>
                    <p class="fw-bold">Tiempo restante: <span class="text-danger" id="timer-ctf">15:00</span></p>
                    <a href="challenge_ctf.php" class="btn btn-primary" >Acceder al Desafío</a>
                    <div class="mt-3">
                        <input type="text" class="form-control" id="flag-ctf" placeholder="Ingresa la bandera">
                        <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="ctf">Verificar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card card-challenge shadow">
                <div class="card-body">
                    <h5 class="card-title text-primary">2. Ingeniería Inversa</h5>
                    <h6 class="card-subtitle mb-2 text-muted">Análisis de Binarios (300 Pts)</h6>
                    <p class="card-text">Descarga el archivo binario y realiza ingeniería inversa para obtener la contraseña oculta.</p>
                    <p class="fw-bold">Archivo: <a href="reverse_challenge.zip">`reverse_challenge.zip`</a></p>
                    <p class="fw-bold">Tiempo restante: <span class="text-danger" id="timer-re">15:00</span></p>
                    <div class="mt-3">
                        <input type="text" class="form-control" id="flag-re" placeholder="Ingresa la bandera">
                        <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="re">Verificar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card card-challenge shadow">
                <div class="card-body">
                    <h5 class="card-title text-primary">3. Criptografía</h5>
                    <h6 class="card-subtitle mb-2 text-muted">Descifrado de Mensajes (250 Pts)</h6>
                    <p class="card-text">Descifra el mensaje oculto. La clave es el nombre del famoso inventor de la máquina enigma.</p>
                    <p class="fw-bold">Cifrado: `Vqj wpgs qd yjg jcems`</p>
                    <p class="fw-bold">Tiempo restante: <span class="text-danger" id="timer-crypto">15:00</span></p>
                    <div class="mt-3">
                        <input type="text" class="form-control" id="flag-crypto" placeholder="Ingresa la bandera">
                        <button class="btn btn-sm btn-outline-success mt-2 check-flag" data-challenge="crypto">Verificar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['nombre'])): ?>
        <div class="alert alert-success text-center fs-4 mb-4">
            BIENVENIDO <?php echo htmlspecialchars($_SESSION['nombre']); ?>
        </div>
    <?php endif; ?>
</div>

<script>
// ===== CONFIGURACIÓN INICIAL =====
const segundosTranscurridos = <?php echo $segundos_transcurridos; ?>;
let globalTimeLeft = Math.max(0, (15 * 60) * 3 - segundosTranscurridos);
let currentScore = 0;
let timers = {};
let completedChallenges = {};

const challengeDurations = {
    'ctf': Math.max(0, 15 * 60 - segundosTranscurridos),
    're': Math.max(0, 15 * 60 - segundosTranscurridos),
    'crypto': Math.max(0, 15 * 60 - segundosTranscurridos)
};

const flags = {
    'ctf': 'FLAG{SQL_INYECCION_EXITOSA}',
    're': 'FLAG{REVERSE_IS_FUN}',
    'crypto': 'FLAG{EL_GENIO_ALAN}'
};

const scores = {
    'ctf': 200,
    're': 300,
    'crypto': 250
};

// ===== FUNCIONES DE TEMPORIZADORES =====
function startTimers() {
    // Temporizadores individuales por desafío
    for (const challenge in challengeDurations) {
        let timeLeft = challengeDurations[challenge];
        timers[challenge] = setInterval(() => {
            if (timeLeft > 0) {
                timeLeft--;
                updateChallengeTimer(challenge, timeLeft);
            } else {
                clearChallengeTimer(challenge);
            }
        }, 1000);
    }

    // Temporizador global
    startGlobalTimer();
}

function updateChallengeTimer(challenge, timeLeft) {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    document.getElementById(`timer-${challenge}`).textContent = 
        `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function clearChallengeTimer(challenge) {
    clearInterval(timers[challenge]);
    document.getElementById(`timer-${challenge}`).textContent = 'Tiempo agotado';
}

function startGlobalTimer() {
    const globalTimer = setInterval(() => {
        if (globalTimeLeft > 0) {
            globalTimeLeft--;
            updateGlobalTimer();
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
    clearInterval(timer);
    document.getElementById('global-timer').textContent = '¡HACKATHON FINALIZADO!';
    
    // Detener todos los temporizadores individuales
    for (const challenge in timers) {
        clearInterval(timers[challenge]);
    }
}

// Deshabilita inputs si el tiempo está agotado
for (const challenge in challengeDurations) {
    if (challengeDurations[challenge] <= 0) {
        document.getElementById(`timer-${challenge}`).textContent = 'Tiempo agotado';
        document.getElementById(`flag-${challenge}`).disabled = true;
        document.querySelector(`button[data-challenge="${challenge}"]`).disabled = true;
    }
}
if (globalTimeLeft <= 0) {
    document.getElementById('global-timer').textContent = '¡HACKATHON FINALIZADO!';
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
        alert('Este desafío ya fue completado.');
        return;
    }

    const userInput = document.getElementById(`flag-${challenge}`).value.trim();
    const expectedFlag = flags[challenge];

    if (userInput === expectedFlag) {
        handleCorrectFlag(challenge);
    } else {
        alert('Bandera Incorrecta. Sigue buscando.');
    }
}

function handleCorrectFlag(challenge) {
    alert(`¡Bandera Correcta! Has ganado ${scores[challenge]} puntos.`);
    
    currentScore += scores[challenge];
    document.getElementById('score').textContent = `${currentScore} Puntos`;
    
    completedChallenges[challenge] = true;
    clearInterval(timers[challenge]);
    document.getElementById(`timer-${challenge}`).textContent = 'COMPLETADO';
}

// ===== VALIDACIÓN DE CÉDULA =====
function setupCedulaValidation() {
    const cedulaInput = document.getElementById('cedula');
    const alertContainer = document.getElementById('alert-container');
    const validationTip = document.getElementById('validation-tip');

    // Prevenir entrada de caracteres no numéricos
    cedulaInput.addEventListener('keydown', (e) => handleKeyDown(e, validationTip));
    
    // Prevenir pegado de contenido no numérico
    cedulaInput.addEventListener('paste', (e) => handlePaste(e, alertContainer));
    
    // Limpiar caracteres no numéricos en tiempo real
    cedulaInput.addEventListener('input', () => cleanCedulaInput(cedulaInput));
    
    // Validación final del formulario
    document.getElementById('registration-form').addEventListener('submit', (e) => 
        validateForm(e, cedulaInput, alertContainer)
    );
}

function handleKeyDown(e, validationTip) {
    const allowedKeys = [
        'Backspace', 'Tab', 'Enter', 'Delete', 'ArrowLeft', 'ArrowRight', 
        'Home', 'End'
    ];
    
    if (allowedKeys.includes(e.key) || e.ctrlKey || e.metaKey) {
        return;
    }
    
    if (e.key.length === 1 && !/\d/.test(e.key)) {
        e.preventDefault();
        validationTip.classList.remove('hidden');
        setTimeout(() => validationTip.classList.add('hidden'), 1000);
    }
}

function handlePaste(e, alertContainer) {
    const pastedData = (e.clipboardData || window.clipboardData).getData('text');
    if (!/^\d*$/.test(pastedData)) {
        e.preventDefault();
        alertMessage(alertContainer, "⛔ El texto pegado contiene caracteres no numéricos. Solo se aceptan números.", 'error');
    }
}

function cleanCedulaInput(input) {
    input.value = input.value.replace(/\D/g, '');
}

function validateForm(e, cedulaInput, alertContainer) {
    const cedula = cedulaInput.value.trim();
    if (cedula === '' || !/^\d+$/.test(cedula)) {
        e.preventDefault();
        alertMessage(alertContainer, "⚠️ La cédula es obligatoria y debe contener solo números.", 'error');
    }
}

function alertMessage(container, message, type) {
    const baseClasses = "p-3 rounded-lg font-medium text-sm shadow-md mt-2";
    const classes = type === 'success'
        ? "bg-success bg-opacity-10 text-success border border-success"
        : "bg-danger bg-opacity-10 text-danger border border-danger";
    
    container.innerHTML = `
        <div class="${baseClasses} ${classes}" role="alert">
            ${message}
        </div>
    `;
    
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    startTimers();
    setupFlagVerification();
    setupCedulaValidation();
});
</script>
</body>
</html>