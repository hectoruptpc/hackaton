<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>⚡ Ejecutando comando</title>
<style>body{background:#1a1a2a;color:#aaf;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>⚡ EJECUTANDO COMANDO</h2>
<p>Permisos elevados obtenidos.<br>Clave de root: ************</p>
<p>Ingresa la clave: <strong>root_s3cr3t_2024</strong></p>
<input type="text" id="clave">
<button onclick="validar()">🔑</button>
<p id="msg"></p>
<script>
    function validar() {
        let k = document.getElementById("clave").value.trim().toLowerCase();
        if(k === "root_s3cr3t_2024") {
            window.location.href = "permisos_otorgados.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Clave incorrecta.";
        }
    }
</script>
</body>
</html>