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
 * Verificar si un usuario existe en la base de datos
 */
function usuarioExiste($cedula) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM participantes WHERE cedula = ?");
    $stmt->execute([$cedula]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Registrar un nuevo participante
 */
function registrarParticipante($nombre, $cedula) {
    global $db;
    $tiempo_inicio = date('Y-m-d H:i:s');
    $stmt = $db->prepare("INSERT INTO participantes (nombre, cedula, tiempo_inicio) VALUES (?, ?, ?)");
    return $stmt->execute([$nombre, $cedula, $tiempo_inicio]);
}

/**
 * Iniciar sesión del usuario
 */
function iniciarSesion($participante) {
    $_SESSION['nombre'] = $participante['nombre'];
    $_SESSION['cedula'] = $participante['cedula'];
    $_SESSION['tiempo_inicio'] = $participante['tiempo_inicio'];
}

/**
 * Validar sesión activa
 */
function validarSesion() {
    if (!isset($_SESSION['cedula'])) {
        return false;
    }
    
    global $db;
    $stmt = $db->prepare("SELECT * FROM participantes WHERE cedula = ?");
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
?>