<?php
/**
 * ============================================================
 * DESAFÍO #1: LOGIN INSEGURO
 * Unidad de Ciencia y Tecnología — UPTPC 2026
 * ============================================================
 * 
 * 🧠 CONOCIMIENTOS REQUERIDOS:
 * - Inspección básica de código fuente HTML en navegadores web (F12).
 * - Comprensión de comentarios HTML (<!-- -->) y riesgo de datos sensibles.
 * 
 * 🛠️ SOLUCIÓN OFICIAL:
 * 1. Abrir la página y presionar F12 (o Ctrl+U) para ver el código fuente.
 * 2. Buscar el comentario <!-- CREDENCIALES: admin / passwordsegura -->.
 * 3. Ingresar las credenciales en el formulario para autenticarse.
 * 
 * 🔀 ALTERNATIVAS DE RESOLUCIÓN:
 * - Método A: Inspeccionar con DevTools en la pestaña Elements.
 * - Método B: Ejecutar 'curl -s http://localhost/hackaton/2026/login_inseguro.php'
 * ============================================================
 */

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
<script>
console.log(
'%c' + 
`
╔══════════════════════════════════════════════════════════════════╗
║                                                                  ║
║   🎯  ¡B U E N   I N T E N T O ,   H A C K E R !  🎯           ║
║                                                                  ║
║   ┌──────────────────────────────────────────────────────────┐   ║
║   │                                                          │   ║
║   │   😂 ¿EN SERIO ABRESTES LA CONSOLA PARA ESTO? 😂        │   ║
║   │                                                          │   ║
║   │   JAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJA   │   ║
║   │   Aquí no vas a encontrar absolutamente NADA.           │   ║
║   │   Sigue perdiendo el tiempo, CAMPEÓN.                    │   ║
║   │                                                          │   ║
║   │   😘  Saludos desde el equipo de Ciencia y Tecnología  😘 │   ║
║   │                                                          │   ║
║   └──────────────────────────────────────────────────────────┘   ║
║                                                                  ║
║   💀  EL VERDADERO HACKER USA SU CEREBRO, NO LA CONSOLA  💀    ║
║                                                                  ║
╚══════════════════════════════════════════════════════════════════╝
`,
'color: #00ffcc; font-size: 13px; font-weight: bold; font-family: monospace; background: #090d16; padding: 10px; border: 2px solid #38bdf8;'
);
console.log('%c🚫 NO PERDAS TU TIEMPO AQUÍ, NO HAY NINGUNA PISTA 🚫', 'color: #ff0000; font-size: 16px; font-weight: bold; background: #1a0000; padding: 8px;');
console.log('%c🤣 TE CREÍSTE MUY LISTO ABRIENDO F12, ¿VERDAD? 🤣', 'color: #ffff00; font-size: 16px; font-weight: bold;');
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>