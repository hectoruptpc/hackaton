<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🔄 Verificando credenciales</title>
    <style>
        body { background: #0a1a1f; font-family: monospace; color: #8cf; text-align: center; padding-top: 80px; }
    </style>
</head>
<body>
<h2>🔄 Verificando credenciales...</h2>
<p>Autenticación biométrica requerida.</p>
<p>Responde: ¿Cuál es el río más largo del mundo? (minúsculas)</p>
<input type="text" id="rio">
<button onclick="validar()">✅</button>
<p id="msg"></p>

<script>
    function validar() {
        let r = document.getElementById("rio").value.trim().toLowerCase();
        if(r === "amazonas" || r === "nil" || r === "nilo") {
            window.location.href = "acceso_bios.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Credenciales inválidas.";
        }
    }
</script>
</body>
</html>