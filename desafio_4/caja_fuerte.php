<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🗝️ Caja Fuerte - Versión Espejo</title>
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
        .reloj {
            margin-top: 20px;
            font-size: 11px;
            color: #4a6a6a;
        }
    </style>
</head>
<body>
<div class="safe">
    <h2>🔒 CAJA FUERTE-Versión Espejo</h2>
    
    <p>El año en que cayó el muro de Berlín.</p>
    <p>🔢 Código: 4 dígitos</p>
    <input type="text" id="codigo" placeholder="????">
    <button onclick="abrir()">🔓 ABRIR</button>
    <p id="mensaje"></p>
    <div class="reloj">
        ⏰ Tienes 3 intentos.
    </div>
</div>

<script>
    let intentos = 0;
    
    function abrir() {
        let code = document.getElementById("codigo").value;
        
        // TRAMPA: el año correcto escrito normal (1989)
        if(code === "1989") {
            window.location.href = "puerto_seguro.php";
        }
        // RESPUESTA CORRECTA: el año al revés (9891)
        else if(code === "9891") {
            window.location.href = "enigma.php";
        }
        else {
            intentos++;
            document.getElementById("mensaje").innerHTML = "❌ Código incorrecto. Intento " + intentos + "/3";
            if(intentos >= 3) {
                document.getElementById("mensaje").innerHTML = "🔒 Caja bloqueada. Reinicia el desafío.";
                document.getElementById("codigo").disabled = true;
            }
        }
    }
</script>
</body>
</html>