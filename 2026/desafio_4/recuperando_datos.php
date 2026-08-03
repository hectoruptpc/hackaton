<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>💾 Recuperando datos</title>
<style>body{background:#0a1a2a;color:#8cf;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>💾 RECUPERANDO DATOS</h2>
<p>Archivo encontrado: "flag_encrypted.bin"<br>Contraseña: ****</p>
<p>Adivina la contraseña (4 letras, animal doméstico)</p>
<input type="text" id="pass">
<button onclick="validar()">🔓</button>
<p id="msg"></p>
<script>
    function validar() {
        let p = document.getElementById("pass").value.trim().toLowerCase();
        if(p === "gato" || p === "perro") {
            window.location.href = "checksum_ok.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Contraseña incorrecta.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>