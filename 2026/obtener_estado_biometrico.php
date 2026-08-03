<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

header('Content-Type: application/json');
echo json_encode(obtenerEstadoBiometrico());
?>