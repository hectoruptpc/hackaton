<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['cedula'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa', 'logged_out' => true]);
    exit;
}

// Obtener estado del equipo y estado global del hackathon
$info_equipo = obtenerTiempoInicioEquipo($_SESSION['equipo_id']);
$estado = $info_equipo['estado'] ?? 0;
$hackathon_activo = hackathonEstaActivo();

echo json_encode([
    'success' => true,
    'estado' => (int)$estado,
    'hackathon_activo' => $hackathon_activo,
    'equipo_id' => $_SESSION['equipo_id'],
    'timestamp' => time()
]);
?>