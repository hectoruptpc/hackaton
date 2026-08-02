<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>🔓 Desencriptando</title>
<style>body{background:#1a1a2a;color:#aaf;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>🔓 DESENCRIPTANDO ARCHIVO</h2>
<p>Archivo: flag.enc<br>Algoritmo: AES-256-CBC<br>Clave: 7B3A5F2C8D1E4F6A</p>
<p>Ingresa la clave de desencriptación:</p>
<input type="text" id="clave">
<button onclick="validar()">🔐</button>
<p id="msg"></p>
<script>
    function validar() {
        let c = document.getElementById("clave").value.trim().toLowerCase();
        if(c === "7B3A5F2C8D1E4F6A".toLowerCase()) {
            window.location.href = "bitcoin_miner.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Clave incorrecta.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>