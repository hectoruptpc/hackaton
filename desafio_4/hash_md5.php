<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>🔐 Hash MD5</title>
<style>body{background:#0a0a2a;color:#aaf;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>🔐 VERIFICANDO HASH MD5</h2>
<p>Hash: 5f4dcc3b5aa765d61d8327deb882cf99</p>
<p>¿Qué palabra genera este hash? (minúsculas)</p>
<input type="text" id="hash">
<button onclick="validar()">🔓</button>
<p id="msg"></p>
<script>
    function validar() {
        let h = document.getElementById("hash").value.trim().toLowerCase();
        if(h === "password") {
            window.location.href = "decodificando.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Hash incorrecto.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>