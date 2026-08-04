<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

// Log para debugging
error_log("Verificar bandera - Inicio: " . date('Y-m-d H:i:s'));

if (!isset($_SESSION['equipo_id'])) {
    error_log("Verificar bandera - Error: No hay sesión activa");
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['desafio'], $_POST['bandera'])) {
    error_log("Verificar bandera - Error: Datos inválidos");
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Verificar si el hackathon está activo
if (!hackathonEstaActivo()) {
    error_log("Verificar bandera - Error: Hackathon no activo");
    echo json_encode(['success' => false, 'message' => 'El hackathon no ha iniciado. Espera a que el administrador lo active.']);
    exit;
}

// Verificar si el equipo ha iniciado tiempo
$info_equipo = obtenerTiempoInicioEquipo($_SESSION['equipo_id']);
if (!$info_equipo['tiempo_inicio']) {
    error_log("Verificar bandera - Error: Equipo no ha iniciado tiempo");
    echo json_encode(['success' => false, 'message' => 'Tu equipo no ha iniciado el hackathon. Vuelve a acceder al dashboard.']);
    exit;
}

// Verificar tiempo restante global
$tiempo_restante = calcularTiempoRestanteGlobal();
if ($tiempo_restante <= 0) {
    error_log("Verificar bandera - Error: Tiempo agotado");
    echo json_encode(['success' => false, 'message' => 'El tiempo del hackathon ha terminado']);
    exit;
}

$desafio = $_POST['desafio'];
$bandera_usuario = trim($_POST['bandera']);
$equipo_id = $_SESSION['equipo_id'];

error_log("Verificar bandera - Equipo: $equipo_id, Desafío: $desafio");

$configuracion = obtenerConfiguracionDesafios();

if (!isset($configuracion[$desafio])) {
    error_log("Verificar bandera - Error: Desafío no válido: $desafio");
    echo json_encode(['success' => false, 'message' => 'Desafío no válido']);
    exit;
}

// Verificar si ya fue completado
if (desafioCompletado($equipo_id, $desafio)) {
    error_log("Verificar bandera - Error: Desafío ya completado");
    echo json_encode(['success' => false, 'message' => 'Este desafío ya fue completado por tu equipo']);
    exit;
}

// Verificar bandera
if (verificarBandera($bandera_usuario, $configuracion[$desafio]['flag'])) {
    error_log("Verificar bandera - Bandera correcta para equipo $equipo_id");
    
    // Registrar completado y sumar puntos
    if (completarDesafio($equipo_id, $desafio, $configuracion[$desafio]['puntos'])) {
        // Obtener la puntuación ACTUALIZADA del equipo
        $equipo_actualizado = obtenerInfoEquipo($equipo_id);
        $_SESSION['puntuacion_equipo'] = $equipo_actualizado['puntuacion_total'];
        
        error_log("Verificar bandera - Éxito: Desafío registrado, puntuación: " . $_SESSION['puntuacion_equipo']);
        
        echo json_encode([
            'success' => true, 
            'message' => '¡Bandera correcta!', 
            'puntos' => $configuracion[$desafio]['puntos'],
            'puntuacion_total' => $_SESSION['puntuacion_equipo']
        ]);
    } else {
        error_log("Verificar bandera - Error: No se pudo registrar el desafío");
        echo json_encode(['success' => false, 'message' => 'Error al registrar el desafío']);
    }
} else {
    error_log("Verificar bandera - Bandera incorrecta: $bandera_usuario");
    echo json_encode(['success' => false, 'message' => 'Bandera incorrecta']);
}
?>