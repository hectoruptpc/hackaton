<?php
// challenge_buffer_overflow.php - Desafío Buffer Overflow estilo CTF

session_start();
$flag = "FLAG{BUFFER_OVERFLOW_EXPLOIT}";
$mensaje = "";
$show_input = true;

// Procesar el input del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['input_data'])) {
    $input = $_POST['input_data'];
    
    // SIMULACIÓN DE BUFFER OVERFLOW
    // En C, un buffer de 64 bytes se desborda con más de 64 caracteres
    $buffer_size = 64;
    $input_length = strlen($input);
    
    if ($input_length > $buffer_size) {
        // Se ha desbordado! Verificar si contiene el "payload" para ejecutar flag_secreta
        $overflow_bytes = $input_length - $buffer_size;
        
        // Buscar la dirección simulada de flag_secreta en los bytes extra
        // En un exploit real, los últimos 4 bytes sobrescriben EIP con la dirección de flag_secreta
        $flag_hex = "f1e2d3c4"; // Dirección hexadecimal simulada
        $flag_pattern = "FLAG_SECRETA"; // Palabra mágica que activa la flag
        
        if (strpos($input, $flag_pattern) !== false || substr($input, -8) === $flag_hex) {
            $mensaje = '<div class="alert alert-success">🎉 ¡EXPLOIT EXITOSO! Has sobrescrito el registro de retorno.<br>
                        <strong>' . $flag . '</strong></div>';
            $show_input = false;
            // Marcar como completado en sesión
            $_SESSION['buffer_overflow_completed'] = true;
        } else {
            $mensaje = '<div class="alert alert-warning">⚠️ Desbordamiento detectado! Se han escrito ' . $overflow_bytes . 
                       ' bytes extra. Pero no lograste ejecutar flag_secreta(). Sigue intentando.</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-info">📝 Datos ingresados (' . $input_length . '/' . $buffer_size . 
                   ' bytes): ' . htmlspecialchars($input) . '<br>El programa terminó normalmente. No hubo overflow.</div>';
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
        input {
            background: #001100;
            border: 1px solid #0f0;
            color: #0f0;
            font-family: 'Courier New', monospace;
        }
        input:focus {
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
                            <li>Payload ejemplo: <code class="text-info">A"x68 + FLAG_SECRETA</code></li>
                        </ul>
                    </div>

                    <div class="mt-3 text-center">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary">← Volver al Hackathon</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>