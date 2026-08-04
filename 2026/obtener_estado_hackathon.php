<?php
// obtener_estado_hackathon.php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');

try {
    $config = obtenerConfiguracionHackathon();
    
    echo json_encode([
        'success' => true,
        'estado' => [
            'hackathon_iniciado' => (bool)($config['hackathon_iniciado'] ?? false),
            'tiempo_inicio_global' => $config['tiempo_inicio_global'] ?? null,
            'duracion_minutos' => $config['duracion_minutos'] ?? 90,
            'timestamp' => time()
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>