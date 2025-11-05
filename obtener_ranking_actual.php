<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');

try {
    $ranking = obtenerRankingEquiposConTiempo();
    
    echo json_encode([
        'success' => true,
        'ranking' => $ranking,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'ranking' => []
    ]);
}
?>