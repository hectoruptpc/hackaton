<?php
session_start();

// Limpiar todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borrar también la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión finalmente
session_destroy();

// Redirigir siempre a la página de login (index.php sin sesión activa)
header("Location: index.php");
exit;
?>