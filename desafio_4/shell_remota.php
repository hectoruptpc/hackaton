<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>🔐 Clave de Seguridad</title>
    <style>
        body{background:#0a0a1a;color:#aaf;text-align:center;padding-top:80px;font-family:monospace;}
        .caja{background:#0a0f1a;display:inline-block;padding:40px;border-radius:15px;border:1px solid #55f;}
        input,button{background:#0a0a1a;border:1px solid #55f;color:#aaf;padding:10px;margin-top:15px;}
    </style>
</head>
<body>
<div class="caja">
    <h2>🔐 CLAVE DE SEGURIDAD</h2>
    <p>Ingresa la contraseña de administrador:</p>
    <input type="text" id="clave" placeholder="contraseña">
    <button onclick="validar()">🔓 VERIFICAR</button>
    <p id="msg"></p>
</div>
<script>
    function validar() {
        let c = document.getElementById("clave").value.trim().toLowerCase();
        
        // CUALQUIER contraseña que pongan es "correcta" (para avanzar en la trampa)
        if(c.length > 0) {
            window.location.href = "ejecutando_comando.php";
        }
        else {
            document.getElementById("msg").innerHTML = "❌ Ingresa algo.";
        }
    }
</script>
</body>
</html>