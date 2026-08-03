<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🆔 Validando identidad</title>
    <style>
        body { background: #1a1a0a; font-family: monospace; color: #ff9; text-align: center; padding-top: 80px; }
    </style>
</head>
<body>
<h2>🆔 VALIDACIÓN FINAL</h2>
<p>Para confirmar tu identidad, ingresa el código: <strong>X9KL-7MNB</strong></p>
<input type="text" id="codigo">
<button onclick="validar()">🔐</button>
<p id="msg"></p>

<script>
    function validar() {
        let c = document.getElementById("codigo").value.trim().toUpperCase();
        if(c === "X9KL-7MNB" || c === "X9KL7MNB") {
            window.location.href = "servidor_principal.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Código incorrecto.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>