<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>⚡ Nivel 3 - Enigma</title>
    <style>
        body{background:#0a0f1c;font-family:monospace;color:#ddeeff;text-align:center;padding-top:60px;}
        .acertijo{background:#1a1f2e;padding:25px;border-radius:20px;width:600px;margin:auto;}
    </style>
</head>
<body>
<div class="acertijo">
    <h2>🧩 El Acertijo</h2>
    <p>"Cuanto más tienes, menos se ve.<br>
    Cuanto menos tienes, más se aprecia.<br>
    ¿Qué es?"</p>
    <input type="text" id="resp">
    <button onclick="checar()">✨ RESPONDER</button>
    <p id="resmsg"></p>
</div>
<script>
    function checar() {
        let r = document.getElementById("resp").value.trim().toLowerCase();
        if(r === "oscuridad" || r === "tinieblas") {
            window.location.href = "kernel_panic.php";
        }
        else if(r === "silencio") {
            window.location.href = "oraculo.php";
        }
        else {
            document.getElementById("resmsg").innerHTML = "❌ No es correcto.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>