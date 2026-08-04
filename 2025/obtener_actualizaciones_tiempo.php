<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');

try {
    $db = $pdo ?? null;
    if (!$db) {
        throw new Exception('No hay conexión a la base de datos disponible.');
    }

    $stmt = $db->prepare(
        "SELECT id, nombre_equipo, codigo_equipo, tiempo_acumulado, completado, puntuacion_total, estado FROM equipos ORDER BY id"
    );
    $stmt->execute();
    $equipos_con_tiempo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($equipos_con_tiempo as &$equipo) {
        $equipo['tiempo_acumulado'] = (int) ($equipo['tiempo_acumulado'] ?? 0);
        $equipo['puntuacion_total'] = (int) ($equipo['puntuacion_total'] ?? 0);
        $equipo['completado'] = (bool) ($equipo['completado'] ?? 0);
        $equipo['estado'] = (int) ($equipo['estado'] ?? 0);
    }
    unset($equipo);

    echo json_encode([
        'success' => true,
        'equipos_actualizados' => $equipos_con_tiempo,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'equipos_actualizados' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>