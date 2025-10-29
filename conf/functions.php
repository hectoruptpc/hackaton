<?php
// filepath: conf/functions.php

// Incluir la configuración de la base de datos
require_once __DIR__ . '/db.php';

// Crear variable global $db (alias de $pdo para mayor claridad)
$db = $pdo;

/**
 * Validar que una cédula contenga solo números
 */
function validarCedula($cedula) {
    return preg_match('/^\d+$/', trim($cedula));
}

/**
 * Generar código único para equipo
 */
function generarCodigoEquipo() {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < 6; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $codigo;
}

/**
 * Verificar si un usuario existe en la base de datos
 */
function usuarioExiste($cedula) {
    global $db;
    $stmt = $db->prepare("SELECT p.*, e.nombre_equipo, e.codigo_equipo, e.puntuacion_total, e.tiempo_inicio 
                         FROM participantes p 
                         LEFT JOIN equipos e ON p.equipo_id = e.id 
                         WHERE p.cedula = ?");
    $stmt->execute([$cedula]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Verificar si un equipo existe por código
 */
function equipoExiste($codigo_equipo) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM equipos WHERE codigo_equipo = ?");
    $stmt->execute([$codigo_equipo]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Contar miembros en un equipo
 */
function contarMiembrosEquipo($equipo_id) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM participantes WHERE equipo_id = ?");
    $stmt->execute([$equipo_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

/**
 * Registrar un nuevo equipo
 */
function registrarEquipo($nombre_equipo) {
    global $db;
    $codigo_equipo = generarCodigoEquipo();
    
    // Verificar que el código no exista (aunque es muy improbable)
    while (equipoExiste($codigo_equipo)) {
        $codigo_equipo = generarCodigoEquipo();
    }
    
    $stmt = $db->prepare("INSERT INTO equipos (nombre_equipo, codigo_equipo) VALUES (?, ?)");
    if ($stmt->execute([$nombre_equipo, $codigo_equipo])) {
        return $db->lastInsertId();
    }
    return false;
}

/**
 * Registrar un nuevo participante y asignar a equipo
 */
function registrarParticipante($nombre, $cedula, $equipo_id) {
    global $db;
    $stmt = $db->prepare("INSERT INTO participantes (nombre, cedula, equipo_id) VALUES (?, ?, ?)");
    return $stmt->execute([$nombre, $cedula, $equipo_id]);
}

/**
 * Obtener información del equipo
 */
function obtenerInfoEquipo($equipo_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM equipos WHERE id = ?");
    $stmt->execute([$equipo_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtener miembros del equipo
 */
function obtenerMiembrosEquipo($equipo_id) {
    global $db;
    $stmt = $db->prepare("SELECT nombre, cedula FROM participantes WHERE equipo_id = ? ORDER BY creado_en");
    $stmt->execute([$equipo_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Verificar si un desafío ya fue completado por el equipo
 */
function desafioCompletado($equipo_id, $desafio_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM desafios_completados WHERE equipo_id = ? AND desafio_id = ?");
    $stmt->execute([$equipo_id, $desafio_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

/**
 * Marcar desafío como completado y sumar puntos
 */
function completarDesafio($equipo_id, $desafio_id, $puntos) {
    global $db;
    
    // Verificar si ya está completado
    if (desafioCompletado($equipo_id, $desafio_id)) {
        return false;
    }
    
    // Registrar completado
    $stmt = $db->prepare("INSERT INTO desafios_completados (equipo_id, desafio_id) VALUES (?, ?)");
    $stmt->execute([$equipo_id, $desafio_id]);
    
    // Sumar puntos al equipo
    $stmt = $db->prepare("UPDATE equipos SET puntuacion_total = puntuacion_total + ? WHERE id = ?");
    return $stmt->execute([$puntos, $equipo_id]);
}

/**
 * Iniciar sesión del usuario
 */
function iniciarSesion($participante) {
    $_SESSION['nombre'] = $participante['nombre'];
    $_SESSION['cedula'] = $participante['cedula'];
    $_SESSION['equipo_id'] = $participante['equipo_id'];
    $_SESSION['nombre_equipo'] = $participante['nombre_equipo'];
    $_SESSION['codigo_equipo'] = $participante['codigo_equipo'];
    $_SESSION['puntuacion_equipo'] = $participante['puntuacion_total'];
    $_SESSION['tiempo_inicio'] = $participante['tiempo_inicio'];
}

/**
 * Iniciar tiempo del equipo (SOLO cuando el hackathon esté activo)
 */
function iniciarTiempoEquipo($equipo_id) {
    global $db;
    
    // Verificar si el hackathon está activo
    $config = obtenerConfiguracionHackathon();
    if (!$config || !$config['hackathon_iniciado']) {
        return false; // No iniciar tiempo si el hackathon no ha comenzado
    }
    
    $tiempo_inicio = date('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE equipos SET tiempo_inicio = ? WHERE id = ?");
    return $stmt->execute([$tiempo_inicio, $equipo_id]);
}

/**
 * Validar sesión activa
 */
function validarSesion() {
    if (!isset($_SESSION['cedula'])) {
        return false;
    }
    
    global $db;
    $stmt = $db->prepare("SELECT p.*, e.nombre_equipo, e.codigo_equipo, e.puntuacion_total, e.tiempo_inicio 
                         FROM participantes p 
                         LEFT JOIN equipos e ON p.equipo_id = e.id 
                         WHERE p.cedula = ?");
    $stmt->execute([$_SESSION['cedula']]);
    $participante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$participante) {
        session_unset();
        session_destroy();
        return false;
    }
    
    return $participante;
}

/**
 * Calcular segundos transcurridos desde el inicio
 */
function calcularTiempoTranscurrido($tiempo_inicio) {
    if (!$tiempo_inicio) return 0;
    $tiempo_inicio = strtotime($tiempo_inicio);
    $ahora = time();
    return $ahora - $tiempo_inicio;
}

/**
 * Mostrar alerta con JavaScript
 */
function mostrarAlerta($mensaje) {
    echo "<script>alert('" . addslashes($mensaje) . "');window.location='index.php';</script>";
    exit;
}

/**
 * Verificar bandera (para los desafíos)
 */
function verificarBandera($bandera_usuario, $bandera_correcta) {
    return trim($bandera_usuario) === $bandera_correcta;
}

/**
 * Obtener configuración de desafíos
 */
function obtenerConfiguracionDesafios() {
    return [
        'ctf' => [
            'flag' => 'FLAG{SQL_INYECCION_EXITOSA}',
            'puntos' => 200,
            'tiempo' => 15 * 60
        ],
        're' => [
            'flag' => 'FLAG{REVERSE_IS_FUN}',
            'puntos' => 300,
            'tiempo' => 15 * 60
        ],
        'crypto' => [
            'flag' => 'FLAG{EL_DESENCRIPTADOR_MASTER}',
            'puntos' => 250,
            'tiempo' => 15 * 60
        ],
        'zip' => [
            'flag' => 'FLAG{Z1P_CR4CK3R_W1N}',
            'puntos' => 275,
            'tiempo' => 15 * 60
        ],
        'meta' => [
            'flag' => 'FLAG{SOY_EINSTEIN_SIUUU}',
            'puntos' => 225,
            'tiempo' => 15 * 60
        ]
    ];
}

/**
 * Obtener configuración del hackathon
 */
function obtenerConfiguracionHackathon() {
    global $db;
    $stmt = $db->prepare("SELECT * FROM configuracion_hackathon ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Iniciar hackathon globalmente
 */
function iniciarHackathonGlobal() {
    global $db;
    $tiempo_inicio = date('Y-m-d H:i:s');
    
    $stmt = $db->prepare("UPDATE configuracion_hackathon SET hackathon_iniciado = TRUE, tiempo_inicio_global = ?");
    return $stmt->execute([$tiempo_inicio]);
}

/**
 * Reiniciar hackathon (para testing)
 */
function reiniciarHackathon() {
    global $db;
    
    $stmt = $db->prepare("UPDATE configuracion_hackathon SET hackathon_iniciado = FALSE, tiempo_inicio_global = NULL");
    $stmt->execute();
    
    // Reiniciar puntuaciones y desafíos completados
    $stmt = $db->prepare("UPDATE equipos SET puntuacion_total = 0, tiempo_inicio = NULL, inicio_tardio = FALSE");
    $stmt->execute();
    
    $stmt = $db->prepare("DELETE FROM desafios_completados");
    return $stmt->execute();
}

/**
 * Calcular tiempo transcurrido desde inicio global
 */
function calcularTiempoTranscurridoGlobal() {
    $config = obtenerConfiguracionHackathon();
    if (!$config || !$config['tiempo_inicio_global']) {
        return 0;
    }
    
    $tiempo_inicio = strtotime($config['tiempo_inicio_global']);
    $ahora = time();
    return $ahora - $tiempo_inicio;
}

/**
 * Calcular tiempo restante global
 */
function calcularTiempoRestanteGlobal() {
    $config = obtenerConfiguracionHackathon();
    if (!$config || !$config['tiempo_inicio_global']) {
        return $config ? $config['duracion_minutos'] * 60 : 90 * 60;
    }
    
    $transcurrido = calcularTiempoTranscurridoGlobal();
    $total_segundos = $config['duracion_minutos'] * 60;
    $restante = $total_segundos - $transcurrido;
    
    return max(0, $restante);
}

/**
 * Verificar si el hackathon está activo
 */
function hackathonEstaActivo() {
    $config = obtenerConfiguracionHackathon();
    if (!$config || !$config['hackathon_iniciado']) {
        return false;
    }
    
    $tiempo_restante = calcularTiempoRestanteGlobal();
    return $tiempo_restante > 0;
}

/**
 * Obtener tiempo de inicio para un equipo específico
 */
function obtenerTiempoInicioEquipo($equipo_id) {
    global $db;
    $stmt = $db->prepare("SELECT tiempo_inicio, inicio_tardio FROM equipos WHERE id = ?");
    $stmt->execute([$equipo_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Iniciar tiempo para equipo que se une tarde (SOLO cuando el hackathon esté activo)
 */
function iniciarTiempoEquipoTardio($equipo_id) {
    global $db;
    
    // Verificar si el hackathon está activo
    $config = obtenerConfiguracionHackathon();
    if (!$config || !$config['hackathon_iniciado']) {
        return false; // No iniciar tiempo si el hackathon no ha comenzado
    }
    
    $tiempo_inicio = date('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE equipos SET tiempo_inicio = ?, inicio_tardio = TRUE WHERE id = ?");
    return $stmt->execute([$tiempo_inicio, $equipo_id]);
}

/**
 * Forzar inicio de tiempo para equipo cuando accede después del inicio del hackathon
 */
function forzarInicioTiempoEquipo($equipo_id) {
    global $db;
    
    $config = obtenerConfiguracionHackathon();
    if (!$config || !$config['hackathon_iniciado']) {
        return false;
    }
    
    // Verificar si el equipo ya tiene tiempo iniciado
    $info_equipo = obtenerTiempoInicioEquipo($equipo_id);
    if ($info_equipo['tiempo_inicio']) {
        return true; // Ya tiene tiempo iniciado
    }
    
    // Iniciar tiempo marcando como tardío
    $tiempo_inicio = date('Y-m-d H:i:s');
    $stmt = $db->prepare("UPDATE equipos SET tiempo_inicio = ?, inicio_tardio = TRUE WHERE id = ?");
    return $stmt->execute([$tiempo_inicio, $equipo_id]);
}

/**
 * Obtener el último equipo creado
 */
function obtenerUltimoEquipo() {
    global $db;
    $stmt = $db->prepare("SELECT * FROM equipos ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>