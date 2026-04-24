<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>📜 Certificado válido</title>
<style>body{background:#1a2a1a;color:#9f9;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>📜 CERTIFICADO SSL VÁLIDO</h2>
<p>Ingresa el token: <strong>SSL-CERT-2024-X9</strong></p>
<input type="text" id="token">
<button onclick="validar()">🔐</button>
<p id="msg"></p>
<script>
    function validar() {
        let t = document.getElementById("token").value.trim().toUpperCase();
        if(t === "SSL-CERT-2024-X9" || t === "SSLCERT2024X9") {
            window.location.href = "nodo_central.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Token inválido.";
        }
    }
</script>
</body>
</html>