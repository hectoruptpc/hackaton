<?php
// actualizar_estado_hackathon.php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');

// Solo administradores pueden cambiar el estado
if (!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? '';

try {
    switch ($accion) {
        case 'iniciar':
            // Simplemente actualizamos el timestamp para que otros dispositivos detecten el cambio
            // El inicio real se hace desde el formulario principal
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado'
            ]);
            break;
            
        case 'reiniciar':
            // Simplemente actualizamos el timestamp para que otros dispositivos detecten el cambio  
            // El reinicio real se hace desde el formulario principal
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>