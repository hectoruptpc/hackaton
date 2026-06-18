<?php
session_start();
require_once '../conf/functions.php';

header('Content-Type: application/json');
echo json_encode(obtenerEstadoBiometrico());
?>