<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>🚇 Túnel encriptado</title>
<style>body{background:#0a0a2a;color:#8af;text-align:center;padding-top:80px;}</style>
</head>
<body>
<h2>🚇 TÚNEL ENCRIPTADO</h2>
<p>Paquete recibido: "XOR-7F-2A-4C"</p>
<p>Decodifica: 7F 2A 4C en ASCII (hex to text)</p>
<input type="text" id="hex">
<button onclick="validar()">🔓</button>
<p id="msg"></p>
<script>
    function validar() {
        let h = document.getElementById("hex").value.trim().toLowerCase();
        if(h === "del" || h === "") {
            window.location.href = "certificado_valido.php";
        } else {
            document.getElementById("msg").innerHTML = "❌ Paquete corrupto.";
        }
    }
</script>
</body>
</html>