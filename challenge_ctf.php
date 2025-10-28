<?php
// La bandera que deben obtener e ingresar en la página principal es: FLAG{SQL_INYECCION_EXITOSA}
$flag_oculta = "FLAG{SQL_INYECCION_EXITOSA}";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // ----------------------------------------------------------------------
    // VULNERABILIDAD SIMULADA: Se asume que este string se pasaría
    // directamente a una consulta SQL sin sanitización (vulnerable a SQLi).
    // Si la entrada es, por ejemplo: ' OR '1'='1 --
    // La consulta simulada sería: "SELECT * FROM usuarios WHERE user='...' AND pass='...'
    // ----------------------------------------------------------------------

    // SIMULACIÓN DE CONSULTA VULNERABLE
    // Si la entrada del usuario es: ' OR '1'='1' --
    // La condición resultante será verdadera, simulando un login exitoso sin credenciales.

    // 1. Verificar el ataque de Inyección SQL (Evasión de login)
    // El ataque más común es: ' OR 1=1 --

    // NOTA: Para este ejercicio, solo necesitamos verificar si el usuario ingresó el payload de inyección.
    // Esto simula la explotación exitosa en un entorno real.
    $payload_evasion_simple = "' OR '1'='1' --";
    $payload_evasion_doble = "' or 1=1 --";
    $payload_evasion_comilla = "' OR '1'='1"; // Algunos ataques no usan el comentario


    if (
        strpos($usuario, "OR '1'='1'") !== false ||
        strpos($usuario, "or 1=1") !== false ||
        strpos($contrasena, "OR '1'='1'") !== false ||
        strpos($contrasena, "or 1=1") !== false
    ) {
        // Ataque exitoso, muestra la bandera.
        $mensaje = "<div class='alert alert-success mt-4'><strong>¡ACCESO CONCEDIDO!</strong> Has demostrado una vulnerabilidad crítica. La bandera es: <code>" . $flag_oculta . "</code></div>";
    } elseif ($usuario == "admin" && $contrasena == "passwordsegura") {
        // Credenciales legítimas (opcional, para despistar)
        $mensaje = "<div class='alert alert-info mt-4'>Inicio de sesión normal. Acceso al panel de administración. ¡Pero la bandera no está aquí! Intenta encontrar la vulnerabilidad.</div>";
    } else {
        // Fallo de login normal
        $mensaje = "<div class='alert alert-danger mt-4'>Error: Credenciales inválidas.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Desafío CTF: Inicio de Sesión</title>
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
<div class="container">
<div class="login-container">
<h2 class="text-center mb-4 text-primary">Sistema de Acceso de Empleados</h2>
<p class="text-center"><em>Hemos tenido problemas de seguridad. ¿Puedes ingresar sin credenciales válidas?</em></p>

<form action="challenge_ctf.php" method="POST">
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

<?php echo $mensaje; // Muestra el resultado de la simulación ?>

<div class="mt-4 text-center">
<a href="index.php" class="btn btn-sm btn-outline-secondary">Volver al Dashboard</a>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
