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
    <link rel="stylesheet" href="conf/ia_avatar.css?v=2026_v14">
    <script src="conf/ia_avatar.js?v=2026_v14" defer></script>
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

                    <!-- Visualización del stack (con Popovers interactivos opcionales) -->
                    <div class="stack-visual mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">📦 MEMORIA (STACK) - Visualización Interactiva</h6>
                            <small class="text-info" style="font-size:0.75rem;">ℹ️ Toca las secciones para inspección teórica</small>
                        </div>

                        <div class="row g-1">
                            <div class="col-3 register distractor-badge" 
                                 data-bs-toggle="popover" 
                                 data-bs-placement="top"
                                 title="💾 Buffer Segment Alpha (Bytes 0x00 - 0x0F)" 
                                 data-bs-content="Bloque inicial de la memoria local. En arquitecturas Intel IA-32, los primeros 16 bytes almacenan caracteres codificados en ASCII/UTF-8. Alineación de memoria a 16 bytes según estándar System V ABI.">
                                buffer[0-15] ℹ️
                            </div>
                            <div class="col-3 register distractor-badge" 
                                 data-bs-toggle="popover" 
                                 data-bs-placement="top"
                                 title="💾 Buffer Segment Beta (Bytes 0x10 - 0x1F)" 
                                 data-bs-content="Módulo secundario del arreglo local. Si se ingresan caracteres como 'A' (0x41), la representación en hexadecimal mostrará 41414141 en los registros de depuración GDB.">
                                buffer[16-31] ℹ️
                            </div>
                            <div class="col-3 register distractor-badge" 
                                 data-bs-toggle="popover" 
                                 data-bs-placement="top"
                                 title="💾 Buffer Segment Gamma (Bytes 0x20 - 0x2F)" 
                                 data-bs-content="Segmento intermedio del arreglo en la pila. La optimización del compilador GCC puede agregar 4 u 8 bytes de padding en versiones superiores a Linux Kernel 4.15.">
                                buffer[32-47] ℹ️
                            </div>
                            <div class="col-3 register distractor-badge" 
                                 data-bs-toggle="popover" 
                                 data-bs-placement="top"
                                 title="💾 Buffer Segment Delta (Bytes 0x30 - 0x3F)" 
                                 data-bs-content="Límite máximo del buffer asignado (64 bytes). Superar este byte específico (byte 64) comenzará la corrupción de la estructura del Frame Pointer de la función invocadora.">
                                buffer[48-63] ℹ️
                            </div>
                            <div class="col-3 register mt-1 text-danger distractor-badge" 
                                 data-bs-toggle="popover" 
                                 data-bs-placement="bottom"
                                 title="📌 Registro EBP (Base Pointer - 4 Bytes)" 
                                 data-bs-content="El Base Pointer conserva la dirección base del marco de pila de la función anterior. Ocupa exactamente 4 bytes (del byte 64 al 67). Debe sobrescribirse antes de alcanzar EIP.">
                                EBP (4 bytes) ℹ️
                            </div>
                            <div class="col-3 register mt-1 text-warning distractor-badge" 
                                 data-bs-toggle="popover" 
                                 data-bs-placement="bottom"
                                 title="⚡ Registro EIP (Instruction Pointer)" 
                                 data-bs-content="El Instruction Pointer o Return Address apunta a la siguiente instrucción de CPU a ejecutar. Sobrescribir EIP permite redirigir el flujo de control hacia cualquier función en memoria.">
                                EIP (RETURN) ℹ️
                            </div>
                            <div class="col-6 register mt-1 text-success distractor-badge" 
                                 data-bs-toggle="popover" 
                                 data-bs-placement="bottom"
                                 title="🏆 Función flag_secreta() [0xf1e2d3c4]" 
                                 data-bs-content="Dirección de la función objetivo cargada en la sección .text del ejecutable ELF. Redirigir el registro EIP a esta función imprime la clave secreta del servidor.">
                                flag_secreta() [0xf1e2d3c4] ℹ️
                            </div>
                        </div>

                        <!-- Sección de Registros Teóricos de Distracción Opcionales -->
                        <div class="mt-3 pt-2 border-top border-secondary text-center">
                            <small class="text-muted d-block mb-2">🔍 Inspección Teórica de Registros x86 (Opcional):</small>
                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                <span class="badge bg-dark border border-cyan text-info distractor-badge" 
                                      data-bs-toggle="popover" 
                                      title="⚙️ Registro EAX (Acumulador)" 
                                      data-bs-content="Utilizado en operaciones aritméticas y para almacenar el valor de retorno de las funciones en C. En este programa devuelve 0x0 en ejecución normal.">EAX (0x00)</span>

                                <span class="badge bg-dark border border-cyan text-info distractor-badge" 
                                      data-bs-toggle="popover" 
                                      title="⚙️ Registro ESP (Stack Pointer)" 
                                      data-bs-content="Apunta al tope actual de la pila. Crece hacia direcciones de memoria más bajas (hacia abajo en arquitectura x86 standard).">ESP (0x7fffffff)</span>

                                <span class="badge bg-dark border border-cyan text-info distractor-badge" 
                                      data-bs-toggle="popover" 
                                      title="⚙️ NOP Sled (0x90 Execution)" 
                                      data-bs-content="Secuencia de instrucciones NOP (No Operation / 0x90). Se utiliza en exploits clásicos para deslizar la ejecución hasta el shellcode cuando la dirección exacta varía.">NOP Sled (0x90)</span>

                                <span class="badge bg-dark border border-cyan text-info distractor-badge" 
                                      data-bs-toggle="popover" 
                                      title="🛡️ Estado de ASLR (Address Space Layout Randomization)" 
                                      data-bs-content="Técnica de seguridad que aleatoriza las posiciones del mapa de memoria. En este desafío se encuentra DESACTIVADA para permitir direccionamiento estático.">ASLR: Disabled</span>

                                <span class="badge bg-dark border border-cyan text-info distractor-badge" 
                                      data-bs-toggle="popover" 
                                      title="🐥 Stack Canaries (Protección de Canario)" 
                                      data-bs-content="Valor entero aleatorio colocado antes del EBP para detectar desbordamientos. En este binario la bandera -fno-stack-protector ha removido el canario.">Canary: None</span>
                            </div>
                        </div>

                        <small class="d-block mt-2 text-center text-light">💡 Para ganar: Sobrescribe EIP con la dirección de flag_secreta()</small>
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
    <footer>
         <div style="text-align:center; margin-top:10px;">
            <img src="../img/cyt.png" alt="Logo Unidad de Ciencia y Tecnología" style="width:90px; height:auto; opacity:0.85;">
        </div>
        </footer>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            trigger: 'hover focus click',
            html: true
        });
    });
});
</script>
</body>
</html>