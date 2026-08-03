<?php
session_start();
$_SESSION['equipo_id'] = 33;
$_SESSION['cedula'] = '10101010';
$_SESSION['nombre'] = 'jose';
$_SESSION['nombre_equipo'] = 'ANGELES DE INFORMATICA';
$_SESSION['codigo_equipo'] = 'WY4UEQ';
$_SESSION['puntuacion_equipo'] = 0;

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['desafio'] = 'ctf';
$_POST['bandera'] = 'FLAG{SQL_INYECCION_EXITOSA}';

require __DIR__ . '/verificar_bandera.php';
