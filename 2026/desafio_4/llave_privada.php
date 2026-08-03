<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>🔑 Llave privada</title>
<style>body{background:#0a1a1a;color:#8f8;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>🔑 LLAVE PRIVADA RSA</h2>
<p>-----BEGIN RSA PRIVATE KEY-----<br>MIICXAIBAAKBgQCqGKukO1De7zhZj6...<br>-----END RSA PRIVATE KEY-----</p>
<p>Frase de paso: <strong>open_sesame_123</strong></p>
<input type="text" id="frase">
<button onclick="validar()">🔓</button>
<p id="msg"></p>
<script>
    function validar() {
        let f = document.getElementById("frase").value.trim().toLowerCase();
        if(f === "open_sesame_123") {
            window.location.href = "desencriptando.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Frase incorrecta.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>