<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>🤝 Handshake SSL</title>
<style>body{background:#1a1a2a;color:#aaf;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>🤝 HANDSHAKE SSL/TLS</h2>
<p>Cifrado: AES-256-GCM<br>Clave de sesión: ********</p>
<p>Verifica tu identidad: ¿Capital de Argentina? (minúsculas)</p>
<input type="text" id="cap">
<button onclick="validar()">🔑</button>
<p id="msg"></p>
<script>
    function validar() {
        let c = document.getElementById("cap").value.trim().toLowerCase();
        if(c === "buenos aires") {
            window.location.href = "tunel_encriptado.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Certificado inválido.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>