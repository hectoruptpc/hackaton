<?php
session_start();
if(!isset($_SESSION['loop_count'])) {
    $_SESSION['loop_count'] = 1;
} else {
    $_SESSION['loop_count']++;
}

if($_SESSION['loop_count'] > 3) {
    session_destroy();
    header("Location: servidor_principal.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>🌀 Cargando sistema...</title>
    <meta http-equiv="refresh" content="2;url=cargando_sistema.php">
    <style>body{background:#000;color:#0f0;text-align:center;padding-top:100px;}</style>
</head>
<body>
<h2>🌀 Cargando sistema...</h2>
<p>Intento <?php echo $_SESSION['loop_count']; ?>/3</p>
<p>Por favor espera...</p>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>