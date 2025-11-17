<?php
// estado_hackathon.php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');

try {
    $accion = $_GET['accion'] ?? 'obtener';
    
    switch ($accion) {
        case 'obtener':
            $estado = obtenerEstadoHackathon();
            echo json_encode([
                'success' => true,
                'estado' => $estado,
                'hackathon_activo' => hackathonEstaActivo()
            ]);
            break;
            
        case 'verificar_cambios':
            $ultimo_timestamp = isset($_GET['ultimo_timestamp']) ? intval($_GET['ultimo_timestamp']) : 0;
            $estado_actual = obtenerEstadoHackathon();
            $cambio_detectado = $estado_actual['timestamp'] > $ultimo_timestamp;
            
            echo json_encode([
                'success' => true,
                'cambio_detectado' => $cambio_detectado,
                'estado_actual' => $estado_actual,
                'nuevo_timestamp' => $estado_actual['timestamp']
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function obtenerEstadoHackathon() {
    $config = obtenerConfiguracionHackathon();
    if (!$config) {
        return [
            'hackathon_iniciado' => false,
            'tiempo_inicio_global' => null,
            'duracion_minutos' => 90,
            'timestamp' => time()
        ];
    }
    
    return [
        'hackathon_iniciado' => (bool)$config['hackathon_iniciado'],
        'tiempo_inicio_global' => $config['tiempo_inicio_global'],
        'duracion_minutos' => $config['duracion_minutos'],
        'timestamp' => time(),
        'tiempo_restante' => calcularTiempoRestanteGlobal()
    ];
}
?>