<?php
session_start();
$_SESSION['ruta_activa'] = 'secundaria1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🔓 Acceso concedido</title>
    <style>
        body { background: #0a1a0a; font-family: monospace; color: #8f8; text-align: center; padding-top: 80px; }
        .caja { background: #0a1f0a; display: inline-block; padding: 30px; border: 1px solid #2f8; border-radius: 10px; }
        .progreso { font-size: 12px; color: #6a6; margin-top: 20px; }
    </style>
</head>
<body>
<div class="caja">
    <h2>🔓 ACCESO CONCEDIDO</h2>
    <p>Verificando credenciales...<br>Sistema validado correctamente.</p>
    <div class="progreso">[▓▓▓▓▓░░░░░] 50%</div>
    <a href="verificando_credenciales.php">➡️ Continuar →</a>
</div>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>