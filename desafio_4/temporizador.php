<?php
session_start();
if(!isset($_SESSION['time_count'])) {
    $_SESSION['time_count'] = 10;
}
if($_SESSION['time_count'] <= 0) {
    session_destroy();
    header("Location: particion_maestra.php");
    exit;
}
$_SESSION['time_count']--;
?>
<!DOCTYPE html>
<html>
<head><title>⏰ Temporizador</title>
<meta http-equiv="refresh" content="1;url=temporizador.php">
<style>body{background:#000;color:#ff0;text-align:center;padding-top:100px;font-size:3rem;}</style>
</head>
<body>
<?php echo $_SESSION['time_count']; ?>
<p style="font-size:1rem;">Esperando validación...</p>
<div style="text-align:center;margin:20px 0;"><a href="../index.php" style="background:#4a5568;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">⬅ Volver al Inicio</a></div>
</body>
</html>