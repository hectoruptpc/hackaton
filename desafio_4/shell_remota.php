<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>💻 Shell remota</title>
<style>body{background:#0a0f0f;color:#0f0;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>💻 SHELL REMOTA</h2>
<p>Conexión establecida con 10.0.0.45<br>Escribe un comando:</p>
<input type="text" id="cmd" placeholder="ls -la">
<button onclick="validar()">$></button>
<p id="msg"></p>
<script>
    function validar() {
        let c = document.getElementById("cmd").value.trim().toLowerCase();
        if(c === "sudo su" || c === "su root") {
            window.location.href = "ejecutando_comando.php";
        } else {
            document.getElementById("msg").innerHTML = "Comando no reconocido.";
        }
    }
</script>
</body>
</html>