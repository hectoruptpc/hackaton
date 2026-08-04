<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>🔧 Reparando sector</title>
<style>body{background:#1a1a0a;color:#ff9;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>🔧 REPARANDO SECTOR DAÑADO</h2>
<p>Escaneo en curso...<br>Sectores dañados: 3</p>
<p>Ingresa el comando: <strong>chkdsk /f</strong></p>
<input type="text" id="cmd">
<button onclick="validar()">⚙️</button>
<p id="msg"></p>
<script>
    function validar() {
        let c = document.getElementById("cmd").value.trim().toLowerCase();
        if(c === "chkdsk /f" || c === "chkdsk") {
            window.location.href = "recuperando_datos.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Comando inválido.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>