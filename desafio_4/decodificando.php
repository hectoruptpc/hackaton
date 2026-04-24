<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>📟 Decodificando</title>
<style>body{background:#1a1a0a;color:#ff9;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>📟 DECODIFICANDO BASE64</h2>
<p>Texto codificado: <strong>Y2FzaSBsbyBsb2dyYXM=</strong></p>
<p>Decodifica a texto plano:</p>
<input type="text" id="b64">
<button onclick="validar()">🔓</button>
<p id="msg"></p>
<script>
    function validar() {
        let b = document.getElementById("b64").value.trim().toLowerCase();
        if(b === "casi lo logras") {
            window.location.href = "llave_privada.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Decodificación fallida.";
        }
    }
</script>
</body>
</html>