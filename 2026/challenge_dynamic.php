<?php
// ============================================================
// challenge_dynamic.php - Desafío #10: Código Dinámico
// ============================================================

session_start();

// Configuración del desafío
define('TIEMPO_CAMBIO', 60);

function generarCodigo() {
    return bin2hex(random_bytes(8));
}

if (!isset($_SESSION['dynamic_challenge'])) {
    $_SESSION['dynamic_challenge'] = [
        'codigo_actual' => generarCodigo(),
        'codigo_anterior' => null,
        'timestamp' => time(),
        'intentos' => 0,
        'flag_obtenida' => false,
        'historial_codigos' => [],
        'pista_actual' => 0
    ];
}

function codigoHaCambiado() {
    if (!isset($_SESSION['dynamic_challenge'])) {
        return false;
    }
    
    $tiempo_actual = time();
    $tiempo_transcurrido = $tiempo_actual - $_SESSION['dynamic_challenge']['timestamp'];
    
    if ($tiempo_transcurrido >= TIEMPO_CAMBIO) {
        $_SESSION['dynamic_challenge']['codigo_anterior'] = $_SESSION['dynamic_challenge']['codigo_actual'];
        $_SESSION['dynamic_challenge']['codigo_actual'] = generarCodigo();
        $_SESSION['dynamic_challenge']['timestamp'] = $tiempo_actual;
        $_SESSION['dynamic_challenge']['pista_actual'] = 0;
        
        if ($_SESSION['dynamic_challenge']['codigo_anterior']) {
            $_SESSION['dynamic_challenge']['historial_codigos'][] = [
                'codigo' => $_SESSION['dynamic_challenge']['codigo_anterior'],
                'timestamp' => date('H:i:s', $tiempo_actual - TIEMPO_CAMBIO)
            ];
        }
        return true;
    }
    return false;
}

$cambio_reciente = codigoHaCambiado();

$mensaje = '';
$tipo_mensaje = 'info';
$codigo_actual = $_SESSION['dynamic_challenge']['codigo_actual'];
$tiempo_restante = max(0, TIEMPO_CAMBIO - (time() - $_SESSION['dynamic_challenge']['timestamp']));

function obtenerPista($nivel) {
    $pistas = [
        1 => "🌊 El código viaja en el flujo de la comunicación, pero no está en el cuerpo de la página",
        2 => "📦 Los encabezados HTTP guardan secretos que no ves a simple vista",
        3 => "🍪 Hay una cookie que guarda el código, pero no se muestra en la página",
        4 => "🔑 Prueba agregando ?api=code a la URL, tal vez encuentres algo",
        5 => "🕵️ El User-Agent 'Debugger/1.0' abre puertas ocultas"
    ];
    return $pistas[$nivel] ?? "La respuesta está en el viento";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['verificar_codigo'])) {
        $codigo_ingresado = trim($_POST['codigo'] ?? '');
        
        if ($codigo_ingresado === $codigo_actual) {
            $_SESSION['dynamic_challenge']['flag_obtenida'] = true;
            $mensaje = '🎉 ¡CÓDIGO ENCONTRADO! FLAG: FLAG{DINAMIC_WEB_HACKER_2026}';
            $tipo_mensaje = 'success';
        } else {
            $_SESSION['dynamic_challenge']['intentos']++;
            $mensaje = "❌ Código incorrecto. Intentos: " . $_SESSION['dynamic_challenge']['intentos'] . "/3";
            $tipo_mensaje = 'error';
            
            if ($_SESSION['dynamic_challenge']['intentos'] >= 3) {
                $_SESSION['dynamic_challenge']['codigo_actual'] = generarCodigo();
                $_SESSION['dynamic_challenge']['timestamp'] = time();
                $_SESSION['dynamic_challenge']['intentos'] = 0;
                $mensaje .= ' 🔄 El código ha sido reiniciado';
                $tipo_mensaje = 'warning';
            }
        }
    }
    
    if (isset($_POST['obtener_pista'])) {
        if ($_SESSION['dynamic_challenge']['pista_actual'] < 5) {
            $_SESSION['dynamic_challenge']['pista_actual']++;
            $pista = obtenerPista($_SESSION['dynamic_challenge']['pista_actual']);
            $mensaje = "🔍 " . $pista;
            $tipo_mensaje = 'info';
        } else {
            $mensaje = "⛔ Ya no hay más pistas, confía en tu intuición";
            $tipo_mensaje = 'warning';
        }
    }
}

if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['api']) && $_GET['api'] === 'code') {
    header('X-Code-Header: ' . $_SESSION['dynamic_challenge']['codigo_actual']);
    header('Content-Type: application/json');
    echo json_encode(['code' => $_SESSION['dynamic_challenge']['codigo_actual']]);
    exit;
}

if (isset($_GET['debug'])) {
    if ($_SERVER['HTTP_USER_AGENT'] === 'Debugger/1.0') {
        header('Content-Type: application/json');
        echo json_encode([
            'code' => $_SESSION['dynamic_challenge']['codigo_actual']
        ]);
        exit;
    }
}

$intentos_actual = $_SESSION['dynamic_challenge']['intentos'];
$pista_actual = $_SESSION['dynamic_challenge']['pista_actual'];
$flag_obtenida = $_SESSION['dynamic_challenge']['flag_obtenida'];
$historial = $_SESSION['dynamic_challenge']['historial_codigos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Código Dinámico</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0a2e 50%, #16213e 100%); 
            font-family: 'Courier New', monospace; 
            min-height: 100vh; 
            padding: 20px;
            color: #00ff41;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: rgba(0, 0, 0, 0.85); 
            border-radius: 20px; 
            padding: 30px; 
            border: 1px solid rgba(0, 255, 65, 0.2);
            box-shadow: 0 0 50px rgba(0, 255, 65, 0.05);
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid rgba(0, 255, 65, 0.1); 
            padding-bottom: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .title { 
            font-size: 2rem; 
            font-weight: 900; 
            text-shadow: 0 0 20px rgba(0, 255, 65, 0.2);
        }
        .title span { color: #ff00ff; }
        
        .stats { 
            display: flex; 
            gap: 30px; 
            font-size: 0.9rem; 
            background: rgba(0, 255, 65, 0.05); 
            padding: 10px 20px; 
            border-radius: 10px;
            border: 1px solid rgba(0, 255, 65, 0.1);
        }
        .stats .stat { text-align: center; }
        .stats .value { font-size: 1.5rem; font-weight: 700; color: #00ff41; }
        .stats .label { font-size: 0.6rem; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        
        .timer-container {
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(0, 255, 65, 0.1);
        }
        .timer {
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .timer .count {
            font-size: 4rem;
            font-weight: 900;
            min-width: 100px;
            text-align: center;
            color: #00ff41;
            text-shadow: 0 0 30px rgba(0, 255, 65, 0.2);
        }
        .timer .count.warning { color: #ffcc00; text-shadow: 0 0 30px rgba(255, 204, 0, 0.2); }
        .timer .count.danger { color: #ff0044; text-shadow: 0 0 30px rgba(255, 0, 68, 0.3); animation: pulse 0.5s infinite; }
        .progress-bar {
            flex: 1;
            height: 4px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #00ff41;
            transition: width 0.5s linear;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.2);
        }
        .progress-fill.warning { background: #ffcc00; box-shadow: 0 0 20px rgba(255, 204, 0, 0.2); }
        .progress-fill.danger { background: #ff0044; box-shadow: 0 0 20px rgba(255, 0, 68, 0.3); animation: pulse 0.5s infinite; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(0, 255, 65, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .card-title {
            font-size: 1rem;
            margin-bottom: 20px;
            color: #00ff41;
            border-bottom: 1px solid rgba(0, 255, 65, 0.1);
            padding-bottom: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .code-hidden {
            text-align: center;
            font-size: 2.5rem;
            letter-spacing: 8px;
            color: #00ff41;
            opacity: 0.3;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed rgba(0, 255, 65, 0.1);
            font-family: 'Courier New', monospace;
            user-select: none;
            text-shadow: 0 0 30px rgba(0, 255, 65, 0.1);
        }

        .input-group {
            display: flex;
            gap: 10px;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        .input-group input {
            flex: 1;
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(0, 255, 65, 0.2);
            border-radius: 8px;
            color: #00ff41;
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            outline: none;
            transition: all 0.3s;
            min-width: 150px;
        }
        .input-group input:focus {
            border-color: #ff00ff;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.1);
        }
        .input-group input::placeholder {
            color: #333;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 2px;
        }
        .btn-primary {
            background: rgba(0, 255, 65, 0.1);
            color: #00ff41;
            border: 1px solid rgba(0, 255, 65, 0.2);
        }
        .btn-primary:hover:not(:disabled) {
            background: rgba(0, 255, 65, 0.2);
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.1);
        }
        .btn-info {
            background: rgba(0, 204, 255, 0.05);
            color: #00ccff;
            border: 1px solid rgba(0, 204, 255, 0.2);
        }
        .btn-info:hover:not(:disabled) {
            background: rgba(0, 204, 255, 0.1);
            box-shadow: 0 0 30px rgba(0, 204, 255, 0.1);
        }
        .btn-danger {
            background: rgba(255, 0, 68, 0.05);
            color: #ff0044;
            border: 1px solid rgba(255, 0, 68, 0.2);
        }
        .btn-danger:hover:not(:disabled) {
            background: rgba(255, 0, 68, 0.1);
            box-shadow: 0 0 30px rgba(255, 0, 68, 0.1);
        }
        .btn-purple {
            background: rgba(255, 0, 255, 0.05);
            color: #ff00ff;
            border: 1px solid rgba(255, 0, 255, 0.2);
        }
        .btn-purple:hover:not(:disabled) {
            background: rgba(255, 0, 255, 0.1);
            box-shadow: 0 0 30px rgba(255, 0, 255, 0.1);
        }
        .btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
        }
        .message.success { background: rgba(0, 255, 65, 0.05); border-color: #00ff41; color: #00ff41; }
        .message.error { background: rgba(255, 0, 68, 0.05); border-color: #ff0044; color: #ff0044; }
        .message.info { background: rgba(0, 204, 255, 0.05); border-color: #00ccff; color: #00ccff; }
        .message.warning { background: rgba(255, 204, 0, 0.05); border-color: #ffcc00; color: #ffcc00; }

        .flag-display {
            background: linear-gradient(135deg, rgba(255, 0, 255, 0.1), rgba(0, 255, 65, 0.1));
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
            font-size: 1.3rem;
            font-weight: 900;
            color: #00ff41;
            border: 1px solid #00ff41;
            box-shadow: 0 0 50px rgba(0, 255, 65, 0.1);
            animation: glow 1s infinite alternate;
        }
        @keyframes glow {
            0% { box-shadow: 0 0 20px rgba(0, 255, 65, 0.05); }
            100% { box-shadow: 0 0 60px rgba(0, 255, 65, 0.15); }
        }

        .history {
            max-height: 150px;
            overflow-y: auto;
            margin-top: 15px;
        }
        .history-item {
            padding: 8px 12px;
            border-bottom: 1px solid rgba(0, 255, 65, 0.05);
            font-size: 0.8rem;
            color: #444;
        }
        .history-item .time { color: #00ff41; opacity: 0.5; }
        .history-item .code { color: #ff00ff; opacity: 0.5; }

        .hint-box {
            background: rgba(255, 204, 0, 0.03);
            border: 1px solid rgba(255, 204, 0, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            color: #ffcc00;
            font-size: 0.9rem;
        }

        .endpoint-hint {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
            color: #333;
            font-size: 0.7rem;
            font-family: 'Courier New', monospace;
            border: 1px dashed #1a1a1a;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 20px; }
            .stats { width: 100%; justify-content: space-around; }
            .timer { flex-direction: column; }
            .code-hidden { font-size: 1.8rem; letter-spacing: 4px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">
                🔐 <span>Código</span> Dinámico
            </div>
            <div class="stats">
                <div class="stat">
                    <div class="value"><?php echo $intentos_actual; ?>/3</div>
                    <div class="label">Intentos</div>
                </div>
                <div class="stat">
                    <div class="value"><?php echo $pista_actual; ?>/5</div>
                    <div class="label">Pistas</div>
                </div>
            </div>
        </div>

        <?php if ($flag_obtenida): ?>
            <div class="flag-display">
                🏆 FLAG: FLAG{DINAMIC_WEB_HACKER_2026}
            </div>
        <?php endif; ?>

        <div class="timer-container">
            <div class="timer">
                <div class="count <?php echo $tiempo_restante <= 10 ? 'danger' : ($tiempo_restante <= 20 ? 'warning' : ''); ?>">
                    <?php echo $tiempo_restante; ?>
                </div>
                <div style="flex:1;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:0.7rem; color:#444;">Tiempo restante</span>
                        <span style="font-size:0.7rem; color:#444;">
                            <?php echo $cambio_reciente ? '🔄 Nuevo código generado' : '⏳ Busca el código'; ?>
                        </span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $tiempo_restante <= 10 ? 'danger' : ($tiempo_restante <= 20 ? 'warning' : ''); ?>" 
                             style="width: <?php echo ($tiempo_restante / TIEMPO_CAMBIO) * 100; ?>%;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="message <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-title">🔍 Código Oculto</div>
            <div class="code-hidden">
                ████████████████
            </div>
            <p style="text-align:center; color:#333; margin-top:15px; font-size:0.8rem;">
                El código cambia cada 60 segundos
            </p>
        </div>

        <div class="card">
            <div class="card-title">🔑 Verificar Código</div>
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="codigo" placeholder="Ingresa el código que encontraste..." required 
                           <?php echo $flag_obtenida ? 'disabled' : ''; ?>>
                </div>
                <button type="submit" name="verificar_codigo" class="btn btn-primary" style="width:100%;"
                        <?php echo $flag_obtenida ? 'disabled' : ''; ?>>
                    Verificar
                </button>
            </form>
            
            <div style="margin-top:15px;">
                <form method="POST" style="display:inline;">
                    <button type="submit" name="obtener_pista" class="btn btn-info" style="width:100%;"
                            <?php echo $flag_obtenida || $pista_actual >= 5 ? 'disabled' : ''; ?>>
                        💡 Pedir Pista
                    </button>
                </form>
            </div>
        </div>

        <?php if ($pista_actual > 0): ?>
            <div class="hint-box">
                <strong>🔍 Pista <?php echo $pista_actual; ?>/5</strong><br>
                <?php echo obtenerPista($pista_actual); ?>
            </div>
        <?php endif; ?>

        <div class="endpoint-hint">
            ?api=code | ?debug
        </div>

        <div class="card">
            <div class="card-title">📜 Códigos Anteriores</div>
            <div class="history">
                <?php if (empty($historial)): ?>
                    <div style="color:#222; text-align:center; padding:20px;">
                        No hay registros
                    </div>
                <?php else: ?>
                    <?php foreach (array_reverse($historial) as $item): ?>
                        <div class="history-item">
                            <span class="time">[<?php echo $item['timestamp']; ?>]</span>
                            <span class="code"><?php echo $item['codigo']; ?></span>
                            <span style="color:#222; margin-left:10px;">expirado</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex; gap:15px; margin-top:30px; justify-content:center; flex-wrap:wrap;">
            <a href="?reset=1" class="btn btn-danger">🔄 Reiniciar</a>
            <a href="index.php" class="btn btn-purple">🏠 Volver</a>
        </div>

        <div style="margin-top:30px; padding-top:20px; border-top:1px solid rgba(0,255,65,0.05); text-align:center; font-size:0.7rem; color:#222;">
            El código se renueva cada 60 segundos
        </div>
    </div>

    <script>
        let tiempoRestante = <?php echo $tiempo_restante; ?>;
        const timerElement = document.querySelector('.timer .count');
        const progressFill = document.querySelector('.progress-fill');
        const totalTiempo = <?php echo TIEMPO_CAMBIO; ?>;
        
        function actualizarTimer() {
            tiempoRestante--;
            
            if (timerElement) {
                timerElement.textContent = Math.max(0, tiempoRestante);
                timerElement.className = 'count';
                if (tiempoRestante <= 10) {
                    timerElement.classList.add('danger');
                } else if (tiempoRestante <= 20) {
                    timerElement.classList.add('warning');
                }
            }
            
            if (progressFill) {
                const porcentaje = Math.max(0, (tiempoRestante / totalTiempo) * 100);
                progressFill.style.width = porcentaje + '%';
                progressFill.className = 'progress-fill';
                if (tiempoRestante <= 10) {
                    progressFill.classList.add('danger');
                } else if (tiempoRestante <= 20) {
                    progressFill.classList.add('warning');
                }
            }
            
            if (tiempoRestante <= 0) {
                location.reload();
            }
        }
        
        setInterval(actualizarTimer, 1000);

        <?php if ($cambio_reciente): ?>
            const notificacion = document.createElement('div');
            notificacion.className = 'message warning';
            notificacion.style.position = 'fixed';
            notificacion.style.top = '20px';
            notificacion.style.right = '20px';
            notificacion.style.maxWidth = '300px';
            notificacion.style.zIndex = '9999';
            notificacion.innerHTML = '🔄 El código ha cambiado';
            document.body.appendChild(notificacion);
            
            setTimeout(() => {
                notificacion.style.opacity = '0';
                setTimeout(() => notificacion.remove(), 500);
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>