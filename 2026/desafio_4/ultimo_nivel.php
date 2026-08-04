<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>🔐 Último nivel</title>
    <style>
        body{background:#021a1a;color:#0ff;font-family:monospace;text-align:center;padding-top:80px;}
        .caja{border:1px solid #0ff;display:inline-block;padding:30px;border-radius:15px;}
    </style>
</head>
<body>
<div class="caja">
    <h2>⚡ NIVEL 5 - LA PUERTA FINAL</h2>
    <p>"La llave está en la primera palabra<br>
    que viste al entrar al sistema."</p>
    <p>Escribe esa palabra:</p>
    <input type="text" id="llave">
    <button onclick="finalizar()">🚪 ABRIR</button>
    <p id="res"></p>
    <div style="font-size:11px;">⚠️ (Pista: estaba en la pantalla de inicio, código 404)</div>
</div>
<script>
    function finalizar() {
        let k = document.getElementById("llave").value.trim().toLowerCase();
        if(k === "404" || k === "notfound" || k === "no_existe") {
            window.location.href = "hash_md5.php";
        }
        else if(k === "caso" || k === "caso404") {
            window.location.href = "flag_real.php";
        }
        else {
            document.getElementById("res").innerHTML = "❌ La puerta no se mueve.";
        }
    }
</script>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>