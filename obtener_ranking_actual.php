<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');

try {
    $ranking = obtenerRankingEquipos();
    
    echo json_encode([
        'success' => true,
        'ranking' => $ranking
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>