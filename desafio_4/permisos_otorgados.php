<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>✅ Permisos otorgados</title>
<style>body{background:#1a2a1a;color:#9f9;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>✅ PERMISOS DE ADMINISTRADOR</h2>
<p>Usuario: root<br>UID: 0<br>Token final: <strong>ADMIN-ROOT-FLAG-GATE</strong></p>
<input type="text" id="token">
<button onclick="validar()">🚪</button>
<p id="msg"></p>
<script>
    function validar() {
        let t = document.getElementById("token").value.trim().toUpperCase();
        if(t === "ADMIN-ROOT-FLAG-GATE") {
            window.location.href = "consola_maestra.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Token inválido.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>