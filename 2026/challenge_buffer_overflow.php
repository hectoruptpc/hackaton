<?php
/**
 * ============================================================
 * DESAFÍO #3: BUFFER OVERFLOW SIMULADO
 * Unidad de Ciencia y Tecnología — UPTPC 2026
 * ============================================================
 * 
 * 🧠 CONOCIMIENTOS REQUERIDOS:
 * - Arquitectura de computadoras (Memoria Stack, Registros EBP, EIP).
 * - Conceptos de desbordamiento de buffer y cálculo de offsets.
 * 
 * 🛠️ SOLUCIÓN OFICIAL:
 * 1. El buffer es de 64 bytes + 4 bytes del registro EBP (Total 68 bytes).
 * 2. Enviar 68 bytes basura + la palabra clave FLAG_SECRETA (o la dirección f1e2d3c4).
 * 
 * 🔀 ALTERNATIVAS DE RESOLUCIÓN:
 * - Método A: Payload manual de 68 caracteres 'A' seguidos de FLAG_SECRETA.
 * - Método B: Script en Python con la librería pwntools / requests.
 * ============================================================
 */

session_start();
require_once 'conf/functions.php';

$mensaje = "";
$show_input = true;

// Procesar el input del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['input_data'])) {
    $input = $_POST['input_data'];
    
    // Procesar usando la función del backend
    $resultado = procesarBufferOverflow($input);
    $mensaje = $resultado['mensaje'];
    $show_input = $resultado['show_input'];
    
    // Marcar como completado en sesión
    if (strpos($mensaje, 'EXPLOIT EXITOSO') !== false) {
        $_SESSION['buffer_overflow_completed'] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💀 Buffer Overflow Challenge | Hackathon 💀</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0a0e1a 0%, #0a0e1a 100%);
            font-family: 'Courier New', monospace;
            color: #0f0;
        }
        .terminal {
            background: #000000dd;
            border: 2px solid #0f0;
            border-radius: 10px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            box-shadow: 0 0 20px rgba(0,255,0,0.3);
        }
        .terminal-header {
            background: #0f0;
            color: #000;
            padding: 5px 10px;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
            margin: -20px -20px 20px -20px;
        }
        .register {
            background: #001100;
            border: 1px solid #0f0;
            border-radius: 5px;
            padding: 10px;
            font-size: 12px;
        }
        textarea {
            background: #001100;
            border: 1px solid #0f0;
            color: #0f0;
            font-family: 'Courier New', monospace;
        }
        textarea:focus {
            background: #002200;
            color: #fff;
            border-color: #0f0;
            box-shadow: 0 0 10px #0f0;
        }
        .btn-exploit {
            background: #0f0;
            color: #000;
            font-weight: bold;
            border: none;
        }
        .btn-exploit:hover {
            background: #0a0;
            color: #000;
            box-shadow: 0 0 15px #0f0;
        }
        .stack-visual {
            background: #000;
            border: 1px solid #333;
            padding: 15px;
            border-radius: 8px;
        }
        .text-muted {
            color: #0a0 !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="terminal">
                    <div class="terminal-header">
                        💀 vuln.exe - SISTEMA VULNERABLE A BUFFER OVERFLOW 💀
                    </div>
                    
                    <div class="mb-4">
                        <pre style="background:#000; color:#0f0; border:none; padding:10px;">
========================================
   🔐 SISTEMA SEGURO - PROTOTIPO 🔐
========================================
Solo personal autorizado puede ingresar.
(este programa tiene una vulnerabilidad crítica)</pre>
                    </div>

                    <?php if ($show_input): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">📡 Ingresa datos para procesar:</label>
                            <textarea class="form-control" name="input_data" rows="3" 
                                      style="background:#001100; color:#0f0; border-color:#0f0;" 
                                      placeholder="Ingresa hasta 64 bytes... o más para desbordar"></textarea>
                            <small class="text-muted">🔍 Buffer size: 64 bytes | Target: flag_secreta()</small>
                        </div>
                        <button type="submit" class="btn btn-exploit w-100">💀 EJECUTAR PROGRAMA 💀</button>
                    </form>
                    <?php endif; ?>

                    <?php echo $mensaje; ?>

                    <!-- Visualización del stack (educativo) -->
                    <div class="stack-visual mt-4">
                        <h6 class="text-center">📦 MEMORIA (STACK) - Visualización</h6>
                        <div class="row g-1">
                            <div class="col-3 register">buffer[0-15]</div>
                            <div class="col-3 register">buffer[16-31]</div>
                            <div class="col-3 register">buffer[32-47]</div>
                            <div class="col-3 register">buffer[48-63]</div>
                            <div class="col-3 register mt-1 text-danger">EBP (4 bytes)</div>
                            <div class="col-3 register mt-1 text-warning">EIP (RETURN ADDR)</div>
                            <div class="col-6 register mt-1 text-success">flag_secreta() [0xf1e2d3c4]</div>
                        </div>
                        <small class="d-block mt-2 text-center">💡 Para ganar: Sobrescribe EIP con la dirección de flag_secreta()</small>
                    </div>

                    <div class="mt-4 p-3" style="background:#001100; border-radius:8px;">
                        <h6>📖 PISTAS:</h6>
                        <ul class="small">
                            <li>Offset hasta EIP: 64 bytes (buffer) + 4 bytes (EBP) = <strong class="text-warning">68 bytes</strong></li>
                            <li>Necesitas enviar 68 bytes basura + la dirección de flag_secreta()</li>
                            <li>Dirección simulada de flag_secreta: <code class="text-success">f1e2d3c4</code> o escribe la palabra mágica <code class="text-success">FLAG_SECRETA</code></li>
                            <li>Payload ejemplo: <code class="text-info">"A" x 68 + FLAG_SECRETA</code></li>
                        </ul>
                    </div>

                    <div class="mt-3 text-center">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary">← Volver al Hackathon</a>
                    </div>
                </div>
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
║   │   😂 ¿CREÍSTE QUE AQUÍ HABÍA ALGUNA PISTA? 😂           │   ║
║   │                                                          │   ║
║   │   JAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJA   │   ║
║   │   Aquí NO hay pistas. Ve a quemarte las pestañas.        │   ║
║   │   Sigue intentando, CAMPEÓN.                             │   ║
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
console.log('%c🚫 AQUÍ NO HAY PISTAS, PERDISTE TU TIEMPO ABRIENDO LA CONSOLA 🚫', 'color: #ff0000; font-size: 16px; font-weight: bold; background: #1a0000; padding: 8px;');
console.log('%c🤣 ¿EN SERIO PENSABAS QUE IBAS A ENCONTRAR ALGO FÁCIL? 🤣', 'color: #ffff00; font-size: 16px; font-weight: bold;');
</script>
</body>
</html>