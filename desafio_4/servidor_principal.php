<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🏢 Servidor Principal</title>
    <style>
        body {
            background: #1a0505;
            text-align: center;
            padding-top: 100px;
            font-family: monospace;
            color: #a44;
        }
        .caja {
            border: 2px solid #a44;
            display: inline-block;
            padding: 40px;
            background: #2a0a0a;
            border-radius: 20px;
        }
    </style>
</head>
<body>
<div class="caja">
    <h2>💀 ACCESO DENEGADO 💀</h2>
    <p>Has completado la ruta de autenticación...<br>Pero este <strong>no es el sistema correcto</strong>.</p>
    <p>Perdiste tiempo en un camino secundario.</p>
    <p>🔁 <a href="inicio.php" style="color:#f66;">Reiniciar desde cero</a></p>
</div>
</body>
</html>