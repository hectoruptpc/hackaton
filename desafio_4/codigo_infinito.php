<?php
session_start();
$iter = isset($_GET['i']) ? (int)$_GET['i'] : 0;
if($iter > 100) {
    header("Location: consola_maestra.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>🌀 Decodificando...</title>
<meta http-equiv="refresh" content="0.1;url=codigo_infinito.php?i=<?php echo $iter+1; ?>">
<style>body{background:#000;color:#0f0;text-align:center;padding-top:100px;}</style>
</head>
<body>
<h2>🌀 DECODIFICANDO BITSTREAM...</h2>
<p>Iteración: <?php echo $iter; ?>/100</p>
<p>Por favor espera...</p>
</body>
</html>