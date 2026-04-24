<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head><title>⏰ Timeout expirado</title>
<style>
body{background:#1a1a0a;color:#fa0;text-align:center;padding-top:100px;}
</style>
</head>
<body>
<h2>⏰ TIEMPO DE SESIÓN EXPIRADO</h2>
<p>Has estado demasiado tiempo en caminos incorrectos.<br>Reinicia el desafío.</p>
<a href="inicio.php">🔄 Reiniciar</a>
</body>
</html>