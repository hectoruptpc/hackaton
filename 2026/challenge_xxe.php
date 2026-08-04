<?php
session_start();
require_once __DIR__ . '/conf/functions.php';

if (!isset($_SESSION['equipo_id'])) {
    header('Location: index.php');
    exit;
}

if (!hackathonEstaActivo()) {
    $_SESSION['modal_message'] = 'El hackathon aún no ha iniciado.';
    $_SESSION['modal_type'] = 'warning';
    header('Location: index.php');
    exit;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bandera'])) {
    $resultado = verificarBanderaDesafio($_SESSION['equipo_id'], 'xxe', trim($_POST['bandera']));
    $mensaje = $resultado['success']
        ? '<div class="alert alert-success">' . htmlspecialchars($resultado['message']) . '</div>'
        : '<div class="alert alert-danger">' . htmlspecialchars($resultado['message']) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Desafío XXE - Hackathon 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0">9. XXE - Inyección de Entidades Externas</h3>
        </div>
        <div class="card-body">
            <p class="lead">Esta aplicación procesa XML sin validación. El objetivo es encontrar la bandera ocultada en la respuesta del parser.</p>
            <p>Prueba con un payload simple y revisa la salida del servidor.</p>
            <form method="post" class="mt-4">
                <label for="bandera" class="form-label">Bandera</label>
                <input type="text" class="form-control" id="bandera" name="bandera" placeholder="FLAG{...}" autocomplete="off" required>
                <button type="submit" class="btn btn-primary mt-3">Verificar</button>
            </form>
            <?php if ($mensaje): ?>
                <div class="mt-4"><?php echo $mensaje; ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
