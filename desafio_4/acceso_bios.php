<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>⚙️ Acceso a BIOS</title>
    <style>
        body { background: #1a0f1a; font-family: monospace; color: #f9f; text-align: center; padding-top: 80px; }
    </style>
</head>
<body>
<h2>⚙️ ACCESO A BIOS DEL SISTEMA</h2>
<p>"El que habla sin parar, pero nunca dice nada"</p>
<input type="text" id="acertijo">
<button onclick="validar()">🔓</button>
<p id="msg"></p>

<script>
    function validar() {
        let r = document.getElementById("acertijo").value.trim().toLowerCase();
        if(r === "radio" || r === "la radio" || r === "television") {
            window.location.href = "validando_identidad.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Error de sistema.";
        }
    }
</script>
</body>
</html>