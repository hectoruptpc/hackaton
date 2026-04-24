<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🗝️ La caja fuerte</title>
    <style>
        body {
            background: #0a1117;
            font-family: monospace;
            color: #bbddff;
            text-align: center;
            padding-top: 60px;
        }
        .safe {
            background: #2a2a2a;
            display: inline-block;
            padding: 30px;
            border-radius: 20px;
            border: 3px solid #8a8a5a;
        }
    </style>
</head>
<body>
<div class="safe">
    <h2>🔒 CÓDIGO DE LA CAFUERTE</h2>
    <p>"El año en que cayó el muro de Berlín."</p>
    <p>Pista: Un número de 4 dígitos.</p>
    <input type="text" id="codigo" placeholder="ej: 1991">
    <button onclick="abrir()">🔓 ABRIR</button>
    <p id="mensaje"></p>
    <div style="font-size:12px; margin-top:20px;">⚠️ Si pones 1995 irás a otro camino...</div>
</div>

<script>
    function abrir() {
        let code = document.getElementById("codigo").value;
        if(code === "1995") {
            window.location.href = "puerto_seguro.php";
        }
        else if(code === "1989") {
            window.location.href = "enigma.php";
        }
        else {
            document.getElementById("mensaje").innerHTML = "❌ Código incorrecto.";
        }
    }
</script>
</body>
</html>