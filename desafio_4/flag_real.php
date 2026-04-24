<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🏆 FLAG ENCONTRADA</title>
    <style>
        body {
            background: radial-gradient(#0a3a2a, #021010);
            text-align: center;
            padding-top: 100px;
            font-family: monospace;
            color: gold;
        }
        .flag {
            background: #00000099;
            padding: 2rem;
            border-radius: 2rem;
            border: 2px solid gold;
            display: inline-block;
            font-size: 1.5rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255,215,0,0.4); }
            70% { box-shadow: 0 0 0 20px rgba(255,215,0,0); }
            100% { box-shadow: 0 0 0 0 rgba(255,215,0,0); }
        }
        .solutions {
            margin-top: 30px;
            font-size: 12px;
            color: #6a6;
            text-align: left;
            display: inline-block;
            background: #0a1a0aaa;
            padding: 15px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="flag">
    🏆 FLAG { 3l_c4m1n0_v3rd4d3r0_tuv0_qu3_p3ns4r } 🏆
</div>
<p>✅ ¡FELICIDADES! Has encontrado la ruta REAL.</p>
<p>Los demás caminos eran señuelos para perder tiempo.</p>
<p><a href="inicio.php" style="color:#0ff;">🔄 Volver a empezar</a></p>

<div class="solutions">
    <strong>📋 RESPUESTAS CORRECTAS (para ti, organizador):</strong><br>
    Nivel 1: mundo<br>
    Nivel 2: 1989<br>
    Nivel 3: silencio<br>
    Nivel 4: e<br>
    Nivel 5: caso / caso404
</div>
</body>
</html>