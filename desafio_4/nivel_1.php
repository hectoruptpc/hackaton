<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>📁 Nivel 1 - Criptograma</title>
    <style>
        body {
            background: #0e121c;
            font-family: monospace;
            color: #c0e0ff;
            text-align: center;
            padding-top: 60px;
        }
        .enigama {
            background: #0a0f18;
            border: 1px solid #2a7f6f;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
        }
        .cifrado {
            font-size: 2rem;
            letter-spacing: 5px;
            background: #000;
            display: inline-block;
            padding: 15px;
            margin: 20px;
            font-weight: bold;
        }
        input, button {
            background: #0a0f18;
            border: 1px solid #2a8f8f;
            padding: 10px;
            color: #0ff;
        }
        .advertencia {
            margin-top: 20px;
            font-size: 11px;
            color: #6a5a5a;
        }
        .pista {
            font-size: 11px;
            color: #4a6a6a;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="enigama">
    <h2>🔐 Nivel 1: El mensaje oculto</h2>
    <p>Has encontrado un pergamino con esto escrito:</p>
    <div class="cifrado">15 4 14 21 13</div>
    <p>A=1, B=2, C=3... ¿Qué palabra es?</p>
    <input type="text" id="respuesta" placeholder="palabra en minúsculas">
    <button onclick="comprobar()">🔓 DESCIFRAR</button>
    <p id="error" style="color:#f66;"></p>
    
    <div class="pista">
        💡 Pista: A veces lo que ves no es lo que parece...
    </div>
</div>

<script>
    function comprobar() {
        let val = document.getElementById("respuesta").value.trim().toLowerCase();
        
        // TRAMPA: la traducción directa
        if(val === "odnum") {
            window.location.href = "acceso_concedido.php";
        } 
        // RESPUESTA CORRECTA: la palabra al revés
        else if(val === "mundo") {
            window.location.href = "caja_fuerte.php";
        } 
        else {
            document.getElementById("error").innerHTML = "❌ No es correcto. Sigue intentando.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>