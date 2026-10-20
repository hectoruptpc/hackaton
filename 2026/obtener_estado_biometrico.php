<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');
$resp = obtenerEstadoBiometrico();
$resp['hackathon_activo'] = hackathonEstaActivo();
echo json_encode($resp);
?>