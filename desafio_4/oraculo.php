<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>📜 Nivel 4 - El oráculo</title>
    <style>
        body{background:#1e1a0f;font-family:Georgia;text-align:center;padding-top:60px;}
        .oraculo{background:#fef6e0;color:#2a2418;width:550px;margin:auto;padding:30px;border-radius:12px;}
    </style>
</head>
<body>
<div class="oraculo">
    <h2>🔮 El Oráculo</h2>
    <p><i>"Soy el principio del fin,<br>
    y el fin del tiempo y el espacio.<br>
    Soy el final de cada carrera,<br>
    y siempre estoy al empezar."</i></p>
    <p>¿Qué letra soy?</p>
    <input type="text" id="letra" maxlength="1" style="width:50px;">
    <button onclick="validar()">🔮</button>
    <p id="msg"></p>
</div>
<script>
    function validar() {
        let l = document.getElementById("letra").value.trim().toLowerCase();
        if(l === "z" || l === "omega") {
            window.location.href = "shell_remota.php";
        }
        else if(l === "e") {
            window.location.href = "ultimo_nivel.php";
        }
        else {
            document.getElementById("msg").innerHTML = "❌ El oráculo niega con la cabeza.";
        }
    }
</script>
</body>
</html>