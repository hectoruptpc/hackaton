<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>📜 Nivel 4 - El oráculo</title>
    <style>
        body{background:#1e1a0f;font-family:Georgia;text-align:center;padding-top:60px;}
        .oraculo{background:#fef6e0;color:#2a2418;width:550px;margin:auto;padding:30px;border-radius:12px;}
    </style>
</head>
<body>
<div class="oraculo">
    <h2>🔮 El Oráculo</h2>
    <p><i>"Qué es lo que es tuyo,<br>
    pero otros lo usan más que tú?"</i></p>
    <input type="text" id="respuesta" placeholder="tu respuesta">
    <button onclick="validar()">🔮</button>
    <p id="msg"></p>
</div>
<script>
    function validar() {
        let r = document.getElementById("respuesta").value.trim().toLowerCase();
        
        // TRAMPA: respuestas comunes pero incorrectas
        if(r === "dinero" || r === "mi casa" || r === "casa" || r === "mi coche" || r === "coche") {
            window.location.href = "shell_remota.php";
        }
        // RESPUESTA CORRECTA
        else if(r === "nombre" || r === "mi nombre") {
            window.location.href = "ultimo_nivel.php";
        }
        else {
            document.getElementById("msg").innerHTML = "❌ El oráculo niega con la cabeza.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>