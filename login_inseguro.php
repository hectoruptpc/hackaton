<?php
// ============================================================
// login_inseguro.php - Desafío de Login
// ============================================================

// Incluir la lógica de funciones
require_once 'conf/functions.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    
    // Procesar el login usando la función del backend
    $mensaje = procesarLogin($usuario, $contrasena);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Desafío: Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.login-container {
    max-width: 400px;
    margin-top: 50px;
    padding: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    background-color: white;
    border-radius: 8px;
}
</style>
</head>
<body>
<!-- 
==============================================
CREDENCIALES PARA EL DESAFÍO
==============================================
Usuario: admin
Contraseña: passwordsegura
==============================================
-->

<div class="container">
<div class="login-container">
<h2 class="text-center mb-4 text-primary">Sistema de Acceso</h2>
<p class="text-center"><em>Encuentra las credenciales para acceder</em></p>

<form action="login_inseguro.php" method="POST">
<div class="mb-3">
<label for="usuario" class="form-label">Usuario</label>
<input type="text" class="form-control" id="usuario" name="usuario" required>
</div>
<div class="mb-3">
<label for="contrasena" class="form-label">Contraseña</label>
<input type="password" class="form-control" id="contrasena" name="contrasena" required>
</div>
<button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
</form>

<?php echo $mensaje; ?>

<div class="mt-4 text-center">
<a href="index.php" class="btn btn-sm btn-outline-secondary">Volver al Dashboard</a>
</div>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>