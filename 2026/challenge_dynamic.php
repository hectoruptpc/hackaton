<?php
// ============================================================
// challenge_dynamic.php - GRAN DESAFÍO FINAL 2026: Código Dinámico
// Unidad de Ciencia y Tecnología - UPTPC
// ============================================================

session_start();
require_once __DIR__ . '/conf/functions.php';

$hackathon_activo = hackathonEstaActivo();

define('TIEMPO_CAMBIO', 300); // 300 segundos (5 minutos) de validez

function generarCodigo() {
    return bin2hex(random_bytes(8)); // 16 caracteres hexadecimales
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

// ETAPA 1: Emisión de Cabecera HTTP de respuesta con la pasarela de enlace
header("X-Final-Challenge-Gateway: ?api_gateway=v2_token_endpoint&auth=UPTPC-2026-FINAL");
setcookie("UPTPC_SESSION_SCOPE", "sec_token_v2", time() + 3600, "/");

// ETAPA 2: Endpoint de la API del Gateway
if (isset($_GET['api_gateway']) && $_GET['api_gateway'] === 'v2_token_endpoint') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!$hackathon_activo) {
        http_response_code(503);
        echo json_encode([
            'status' => 'standby',
            'code' => 503,
            'message' => 'Servicio no disponible. El Hackathon se encuentra en modo STANDBY.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $headers = [];
    if (function_exists('getallheaders')) {
        $raw_headers = getallheaders();
        foreach ($raw_headers as $k => $v) {
            $headers[strtoupper($k)] = $v;
        }
    }
    
    $auth_get = $_GET['auth'] ?? '';
    $auth_header = $_SERVER['HTTP_X_HACKATHON_AUTH'] ?? $headers['X-HACKATHON-AUTH'] ?? '';
    
    $valid_auth = ($auth_get === 'UPTPC-2026-FINAL' || $auth_header === 'UPTPC-2026-FINAL');
    
    if ($valid_auth) {
        $raw_code = $_SESSION['dynamic_challenge']['codigo_actual'];
        $encoded_payload = base64_encode($raw_code);
        
        echo json_encode([
            'status' => 'authorized',
            'service' => 'UPTPC Core Token Gateway v2',
            'payload' => $encoded_payload,
            'expires_in' => max(0, TIEMPO_CAMBIO - (time() - $_SESSION['dynamic_challenge']['timestamp']))
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'code' => 403,
            'message' => 'Acceso denegado. Parámetros de autenticación de gateway ausentes o inválidos.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$mensaje = '';
$tipo_mensaje = 'info';
$codigo_actual = $_SESSION['dynamic_challenge']['codigo_actual'];
$tiempo_restante = max(0, TIEMPO_CAMBIO - (time() - $_SESSION['dynamic_challenge']['timestamp']));

function obtenerPista($nivel) {
    $pistas = [
        1 => "🌐 Superficie del Protocolo: El análisis de una aplicación web no se agota en el DOM ni en el código renderizado. Inspecciona la capa de red y las cabeceras de respuesta que el servidor intercambia en cada transacción HTTP.",
        2 => "📡 Pasarelas y Microservicios: En arquitecturas distribuidas, las aplicaciones backend a menudo anuncian servicios satélite comunicando rutas internas o pasarelas de enlace mediante cabeceras HTTP extendidas (X-...).",
        3 => "🔍 Negociación de Enlace: Localizada la pasarela interna y sus atributos de consulta, formula una solicitud directa hacia ese endpoint para evaluar el formato estructurado y las respuestas que entrega el servicio.",
        4 => "🔐 Formatos de Transferencia: Para garantizar la integridad en tránsito de secuencias de bytes y tokens, los servidores backend suelen emplear esquemas estándar de codificación binaria a texto (RFC 4648). Examina la estructura del payload obtenido.",
        5 => "⏱️ Sincronización de Sesión: La decodificación del bloque revela el token hexadecimal activo. Ten presente que los tokens son de corta vigencia; valídalo en el núcleo central antes de que termine la ventana temporal de rotación."
    ];
    return $pistas[$nivel] ?? "Analiza metódicamente las diferentes capas de la comunicación cliente-servidor.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$hackathon_activo) {
        $mensaje = '⚠️ HACKATHON EN ESPERA: No es posible interactuar con el desafío hasta el inicio oficial.';
        $tipo_mensaje = 'warning';
    } else {
        if (isset($_POST['verificar_codigo'])) {
            $codigo_ingresado = trim($_POST['codigo'] ?? '');
            
            if ($codigo_ingresado === $codigo_actual) {
                $_SESSION['dynamic_challenge']['flag_obtenida'] = true;
                $mensaje = '🔓 ¡CÓDIGO DE ACCESO CORRECTO! Has desbloqueado el núcleo central.<br>Tu bandera maestra ha sido revelada. Cópiala y pégala para validar el desafío y acreditar tus puntos.';
                $tipo_mensaje = 'success';
            } else {
                $_SESSION['dynamic_challenge']['intentos']++;
                $mensaje = "❌ Código incorrecto. Intentos: " . $_SESSION['dynamic_challenge']['intentos'] . "/3";
                $tipo_mensaje = 'error';
                
                if ($_SESSION['dynamic_challenge']['intentos'] >= 3) {
                    $_SESSION['dynamic_challenge']['codigo_actual'] = generarCodigo();
                    $_SESSION['dynamic_challenge']['timestamp'] = time();
                    $_SESSION['dynamic_challenge']['intentos'] = 0;
                    $mensaje .= ' 🔄 El sistema ha rotado el token dinámico.';
                    $tipo_mensaje = 'warning';
                }
            }
        }
        
        if (isset($_POST['validar_flag'])) {
            $flag_ingresada = trim($_POST['flag_input'] ?? '');
            $flag_upper = strtoupper($flag_ingresada);
            $flag_clean = preg_replace('/[^A-Z0-9]/i', '', $flag_upper);

            $es_flag_valida = ($flag_upper === 'FLAG{DINAMIC_WEB_HACKER_2026}' || 
                               $flag_clean === 'FLAGDINAMICWEBHACKER2026' || 
                               $flag_clean === 'DINAMICWEBHACKER2026');

            if ($es_flag_valida) {
                if (isset($_SESSION['equipo_id'])) {
                    if (function_exists('completarDesafio') && !desafioCompletado($_SESSION['equipo_id'], 'race_condition')) {
                        completarDesafio($_SESSION['equipo_id'], 'race_condition', 1);
                        $equipo_info = obtenerInfoEquipo($_SESSION['equipo_id']);
                        if ($equipo_info && isset($equipo_info['puntuacion_total'])) {
                            $_SESSION['puntuacion_equipo'] = $equipo_info['puntuacion_total'];
                        }
                    }
                }
                $_SESSION['dynamic_challenge']['flag_obtenida'] = true;
                $mensaje = '🎉 ¡BANDERA VALIDADA CON ÉXITO! Has completado el Desafío #10 con la bandera <strong>FLAG{DINAMIC_WEB_HACKER_2026}</strong>. ¡Puntos acreditados a tu equipo!';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = '❌ Bandera incorrecta. Debes copiar y pegar la bandera oficial exactamente como fue revelada: <code>FLAG{DINAMIC_WEB_HACKER_2026}</code>';
                $tipo_mensaje = 'error';
            }
        }
        
        if (isset($_POST['obtener_pista'])) {
            if ($_SESSION['dynamic_challenge']['pista_actual'] < 5) {
                $_SESSION['dynamic_challenge']['pista_actual']++;
                $pista = obtenerPista($_SESSION['dynamic_challenge']['pista_actual']);
                $mensaje = "💡 Pista #" . $_SESSION['dynamic_challenge']['pista_actual'] . ": " . $pista;
                $tipo_mensaje = 'info';
            } else {
                $mensaje = "⛔ Has consultado todas las pistas disponibles. Aplica análisis forense y de protocolos.";
                $tipo_mensaje = 'warning';
            }
        }
    }
}

if (isset($_GET['reset'])) {
    unset($_SESSION['dynamic_challenge']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$desafio_completado = false;
if (isset($_SESSION['equipo_id']) && function_exists('desafioCompletado')) {
    $desafio_completado = desafioCompletado($_SESSION['equipo_id'], 'race_condition');
}
if ($desafio_completado) {
    $_SESSION['dynamic_challenge']['flag_obtenida'] = true;
}

$intentos_actual = $_SESSION['dynamic_challenge']['intentos'];
$pista_actual = $_SESSION['dynamic_challenge']['pista_actual'];
$flag_obtenida = $_SESSION['dynamic_challenge']['flag_obtenida'];
$historial = $_SESSION['dynamic_challenge']['historial_codigos'];
// ── Cabecera modular ─────────────────────────────────────────────────────────
$page_title = '🔥 DESAFÍO FINAL 2026: Código Dinámico - UPTPC';
require_once __DIR__ . '/conf/header.php';
echo $header;
?>
<!-- [UPTPC-AUDIT-SERVICE]: Sistema de rotación criptográfica sincronizado con la pasarela central. -->
<!-- Estilos específicos del Desafío Dinámico Final -->
<!-- Google Fonts: Fira Code & Orbitron -->
<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: #08090d linear-gradient(135deg, #050608 0%, #0d1117 50%, #161b22 100%); 
            font-family: 'Fira Code', monospace; 
            min-height: 100vh; 
            padding: 20px;
            color: #00ff66;
            overflow-x: hidden;
        }
        
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: linear-gradient(rgba(0, 255, 102, 0.03) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(0, 255, 102, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
            pointer-events: none;
            z-index: 0;
        }

        .container { 
            position: relative;
            z-index: 1;
            max-width: 950px; 
            margin: 0 auto; 
            background: rgba(10, 14, 20, 0.92); 
            border-radius: 20px; 
            padding: 35px; 
            border: 1px solid rgba(0, 255, 102, 0.3);
            box-shadow: 0 0 50px rgba(0, 255, 102, 0.1), inset 0 0 15px rgba(0, 255, 102, 0.05);
            backdrop-filter: blur(10px);
        }

        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 2px solid rgba(0, 255, 102, 0.2); 
            padding-bottom: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .title { 
            font-family: 'Orbitron', sans-serif;
            font-size: 2.2rem; 
            font-weight: 900; 
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 20px rgba(0, 255, 102, 0.4);
            color: #ffffff;
        }
        .title span { color: #ff0055; text-shadow: 0 0 20px rgba(255, 0, 85, 0.6); }
        .subtitle {
            font-size: 0.8rem;
            color: #8b949e;
            margin-top: 5px;
            letter-spacing: 1px;
        }
        
        .stats { 
            display: flex; 
            gap: 20px; 
            font-size: 0.9rem; 
            background: rgba(0, 255, 102, 0.05); 
            padding: 12px 25px; 
            border-radius: 12px;
            border: 1px solid rgba(0, 255, 102, 0.2);
        }
        .stats .stat { text-align: center; }
        .stats .value { font-size: 1.6rem; font-weight: 700; color: #00ff66; text-shadow: 0 0 10px rgba(0, 255, 102, 0.5); }
        .stats .label { font-size: 0.65rem; color: #8b949e; text-transform: uppercase; letter-spacing: 1px; }

        .timer-container {
            background: rgba(15, 20, 30, 0.8);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(0, 255, 102, 0.2);
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
        }
        .timer {
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .timer .count {
            font-family: 'Orbitron', sans-serif;
            font-size: 4rem;
            font-weight: 900;
            min-width: 110px;
            text-align: center;
            color: #00ff66;
            text-shadow: 0 0 30px rgba(0, 255, 102, 0.5);
        }
        .timer .count.warning { color: #ffbb00; text-shadow: 0 0 30px rgba(255, 187, 0, 0.5); }
        .timer .count.danger { color: #ff0055; text-shadow: 0 0 30px rgba(255, 0, 85, 0.7); animation: pulse 0.5s infinite; }
        
        .progress-bar {
            flex: 1;
            height: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 102, 0.2);
        }
        .progress-fill {
            height: 100%;
            background: #00ff66;
            transition: width 0.5s linear;
            box-shadow: 0 0 20px rgba(0, 255, 102, 0.5);
        }
        .progress-fill.warning { background: #ffbb00; box-shadow: 0 0 20px rgba(255, 187, 0, 0.5); }
        .progress-fill.danger { background: #ff0055; box-shadow: 0 0 20px rgba(255, 0, 85, 0.7); animation: pulse 0.5s infinite; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .card {
            background: rgba(15, 22, 33, 0.6);
            border: 1px solid rgba(0, 255, 102, 0.15);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            transition: border-color 0.3s;
        }
        .card:hover {
            border-color: rgba(0, 255, 102, 0.3);
        }
        .card-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            margin-bottom: 20px;
            color: #00ff66;
            border-bottom: 1px solid rgba(0, 255, 102, 0.15);
            padding-bottom: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .code-encrypted-box {
            text-align: center;
            background: rgba(0, 0, 0, 0.6);
            padding: 25px;
            border-radius: 12px;
            border: 2px dashed rgba(255, 0, 85, 0.3);
            position: relative;
        }
        .code-encrypted-title {
            color: #ff0055;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .code-encrypted-text {
            font-size: 2.2rem;
            letter-spacing: 6px;
            color: #ff0055;
            text-shadow: 0 0 20px rgba(255, 0, 85, 0.4);
            font-weight: 700;
        }

        .input-group {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        .input-group input {
            flex: 1;
            padding: 16px 22px;
            background: rgba(5, 8, 12, 0.8);
            border: 1px solid rgba(0, 255, 102, 0.3);
            border-radius: 10px;
            color: #00ff66;
            font-family: 'Fira Code', monospace;
            font-size: 1.1rem;
            outline: none;
            transition: all 0.3s;
            min-width: 200px;
        }
        .input-group input:focus {
            border-color: #ff0055;
            box-shadow: 0 0 20px rgba(255, 0, 85, 0.3);
        }

        .btn {
            padding: 16px 30px;
            border: none;
            border-radius: 10px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 2px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, rgba(0, 255, 102, 0.2), rgba(0, 255, 102, 0.05));
            color: #00ff66;
            border: 1px solid rgba(0, 255, 102, 0.4);
        }
        .btn-primary:hover:not(:disabled) {
            background: rgba(0, 255, 102, 0.3);
            box-shadow: 0 0 30px rgba(0, 255, 102, 0.3);
            transform: translateY(-2px);
        }
        .btn-info {
            background: rgba(0, 204, 255, 0.08);
            color: #00ccff;
            border: 1px solid rgba(0, 204, 255, 0.3);
        }
        .btn-info:hover:not(:disabled) {
            background: rgba(0, 204, 255, 0.2);
            box-shadow: 0 0 30px rgba(0, 204, 255, 0.3);
            transform: translateY(-2px);
        }
        .btn-danger {
            background: rgba(255, 0, 85, 0.08);
            color: #ff0055;
            border: 1px solid rgba(255, 0, 85, 0.3);
        }
        .btn-danger:hover:not(:disabled) {
            background: rgba(255, 0, 85, 0.2);
            box-shadow: 0 0 30px rgba(255, 0, 85, 0.3);
            transform: translateY(-2px);
        }
        .btn-purple {
            background: rgba(187, 0, 255, 0.08);
            color: #bb00ff;
            border: 1px solid rgba(187, 0, 255, 0.3);
        }
        .btn-purple:hover:not(:disabled) {
            background: rgba(187, 0, 255, 0.2);
            box-shadow: 0 0 30px rgba(187, 0, 255, 0.3);
            transform: translateY(-2px);
        }
        .btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
            transform: none !important;
        }

        .message {
            padding: 18px 25px;
            border-radius: 12px;
            margin: 25px 0;
            font-size: 1rem;
            border-left: 4px solid transparent;
            backdrop-filter: blur(5px);
        }
        .message.success { background: rgba(0, 255, 102, 0.1); border-color: #00ff66; color: #00ff66; box-shadow: 0 0 30px rgba(0, 255, 102, 0.2); }
        .message.error { background: rgba(255, 0, 85, 0.1); border-color: #ff0055; color: #ff0055; box-shadow: 0 0 30px rgba(255, 0, 85, 0.2); }
        .message.info { background: rgba(0, 204, 255, 0.1); border-color: #00ccff; color: #00ccff; }
        .message.warning { background: rgba(255, 187, 0, 0.1); border-color: #ffbb00; color: #ffbb00; }

        .victory-modal {
            background: linear-gradient(135deg, rgba(255, 0, 85, 0.2), rgba(0, 255, 102, 0.2));
            padding: 35px;
            border-radius: 20px;
            text-align: center;
            margin: 25px 0;
            border: 2px solid #00ff66;
            box-shadow: 0 0 60px rgba(0, 255, 102, 0.3);
            animation: victoryGlow 1.5s infinite alternate;
        }
        @keyframes victoryGlow {
            0% { box-shadow: 0 0 30px rgba(0, 255, 102, 0.2); transform: scale(1); }
            100% { box-shadow: 0 0 80px rgba(0, 255, 102, 0.5); transform: scale(1.01); }
        }
        .flag-box {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: #ffffff;
            background: #000;
            padding: 15px 25px;
            border-radius: 10px;
            display: inline-block;
            margin: 20px 0;
            border: 1px solid #00ff66;
            letter-spacing: 2px;
            user-select: all;
        }

        .history {
            max-height: 160px;
            overflow-y: auto;
            margin-top: 15px;
        }
        .history-item {
            padding: 10px 15px;
            border-bottom: 1px solid rgba(0, 255, 102, 0.08);
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
        }
        .history-item .time { color: #8b949e; }
        .history-item .code { color: #ff0055; font-weight: bold; }

        .hint-box {
            background: rgba(255, 187, 0, 0.05);
            border: 1px solid rgba(255, 187, 0, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            color: #ffbb00;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .header { flex-direction: column; text-align: center; }
            .stats { width: 100%; justify-content: space-around; }
            .timer { flex-direction: column; }
            .title { font-size: 1.6rem; }
            .code-encrypted-text { font-size: 1.5rem; letter-spacing: 3px; }
        }
</style>


    <div class="container">
        <?php if (!$hackathon_activo): ?>
        <div class="alert alert-warning text-center p-3 mb-4 rounded-3 border border-warning" style="background: rgba(234, 179, 8, 0.12); color: #facc15; border-color: #eab308 !important;">
            <h5 class="fw-bold mb-1">⏳ HACKATHON EN ESPERA &mdash; DESAFÍO FINAL BLOQUEADO</h5>
            <p class="mb-0 small" style="color: #cbd5e1;">El evento se encuentra en modo STANDBY. El servicio de tokens dinámicos y la validación de acceso se habilitarán automáticamente cuando el administrador inicie la competencia.</p>
        </div>
        <?php endif; ?>

        <div class="header">
            <div>
                <div class="title">
                    🔥 <span>DESAFÍO FINAL</span> 2026
                </div>
                <div class="subtitle">UNIDAD DE CIENCIA Y TECNOLOGÍA — UPTPC</div>
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
            <div class="victory-modal">
                <h2 style="font-family:'Orbitron',sans-serif; color:#00ff66; margin-bottom:10px;">
                    🏆 ¡NÚCLEO CENTRAL DESBLOQUEADO! 🏆
                </h2>
                <p style="color:#ffffff; font-size:1.1rem; margin-bottom:15px;">
                    Has interceptado y decodificado exitosamente el token del sistema.
                </p>
                <div style="display:flex; justify-content:center; align-items:center; gap:12px; flex-wrap:wrap; margin:15px 0;">
                    <div class="flag-box" id="flagBox" style="margin:0;">
                        FLAG{DINAMIC_WEB_HACKER_2026}
                    </div>
                    <button type="button" class="btn btn-info" id="btnCopiarFlag" onclick="copiarBandera()" style="padding:14px 22px;">
                        📋 Copiar Bandera
                    </button>
                </div>

                <?php if ($desafio_completado): ?>
                    <div style="margin-top:20px; padding:15px 20px; background:rgba(0,255,102,0.15); border:1.5px solid #00ff66; border-radius:12px; color:#00ff66; font-weight:bold; font-size:1.05rem;">
                        ✅ ¡DESAFÍO COMPLETADO! Puntos acreditados a tu equipo.
                    </div>
                <?php else: ?>
                    <div style="max-width:620px; margin:20px auto 0 auto; background:rgba(0,0,0,0.55); padding:22px; border-radius:14px; border:1px solid rgba(0,255,102,0.35); text-align:center;">
                        <p style="color:#facc15; font-size:0.95rem; margin-bottom:15px; font-weight:600;">
                            ⚠️ El desafío NO se completa automáticamente. Copia la bandera y valídala aquí abajo o en el <a href="index.php" style="color:#00ff66; text-decoration:underline;">Dashboard principal</a>:
                        </p>
                        <form method="POST" style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                            <input type="text" name="flag_input" id="flagInputDirect" placeholder="Pega aquí: FLAG{...}" 
                                   style="flex:1; min-width:240px; padding:12px 18px; background:#05080c; border:1px solid rgba(0,255,102,0.4); border-radius:10px; color:#00ff66; font-family:'Fira Code', monospace; outline:none;" required>
                            <button type="submit" name="validar_flag" class="btn btn-primary" style="padding:12px 24px;">
                                🚩 Validar Bandera
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="timer-container">
            <div class="timer">
                <div class="count <?php echo $tiempo_restante <= 20 ? 'danger' : ($tiempo_restante <= 45 ? 'warning' : ''); ?>">
                    <?php 
                        $mins = floor($tiempo_restante / 60);
                        $secs = $tiempo_restante % 60;
                        echo sprintf('%02d:%02d', $mins, $secs);
                    ?>
                </div>
                <div style="flex:1;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:0.75rem; color:#8b949e; text-transform:uppercase; letter-spacing:1px;">
                            ⏰ Expiración del código (<?php echo floor(TIEMPO_CAMBIO / 60); ?> minuto(s))
                        </span>
                        <span style="font-size:0.75rem; color:#00ff66;">
                            <?php echo $cambio_reciente ? '🔄 ¡CÓDIGO RENOVADO!' : '⏳ Token Rotativo Activo'; ?>
                        </span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $tiempo_restante <= 20 ? 'danger' : ($tiempo_restante <= 45 ? 'warning' : ''); ?>" 
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
            <div class="card-title">🛡️ Estado del Núcleo Central</div>
            <div class="code-encrypted-box">
                <div class="code-encrypted-title">🔒 ACCESO PROTEGIDO — REQUIERE TOKEN DINÁMICO</div>
                <div class="code-encrypted-text">
                    ████-████-████-████
                </div>
                <p style="margin-top:15px; font-size:0.8rem; color:#8b949e;">
                    El núcleo sincroniza tokens criptográficos rotativos con un ciclo de <?php echo floor(TIEMPO_CAMBIO / 60); ?> minutos. Analiza el tráfico y protocolo del servicio para interceptar el handshake del sistema.
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-title">🔑 Ingresar Código Desencriptado</div>
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="codigo" placeholder="<?php echo $hackathon_activo ? 'Ingresa el código hexadecimal de 16 caracteres...' : 'Hackathon en espera — Entrada deshabilitada'; ?>" required 
                           autocomplete="off" <?php echo ($flag_obtenida || !$hackathon_activo) ? 'disabled' : ''; ?>>
                </div>
                <button type="submit" name="verificar_codigo" class="btn btn-primary" style="width:100%;"
                        <?php echo ($flag_obtenida || !$hackathon_activo) ? 'disabled' : ''; ?>>
                    🚀 Desbloquear Núcleo
                </button>
            </form>
            
            <div style="margin-top:15px;">
                <form method="POST" style="display:inline;">
                    <button type="submit" name="obtener_pista" class="btn btn-info" style="width:100%;"
                            <?php echo ($flag_obtenida || !$hackathon_activo || $pista_actual >= 5) ? 'disabled' : ''; ?>>
                        💡 Solicitar Pista Metodológica (<?php echo $pista_actual; ?>/5)
                    </button>
                </form>
            </div>
        </div>

        <?php if ($pista_actual > 0): ?>
            <div class="hint-box">
                <strong style="font-family:'Orbitron',sans-serif; font-size:1.05rem;">🔍 Pista Metodológica #<?php echo $pista_actual; ?>/5</strong><br>
                <div style="margin-top:8px;"><?php echo obtenerPista($pista_actual); ?></div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-title">📜 Registro de Códigos Expirados</div>
            <div class="history">
                <?php if (empty($historial)): ?>
                    <div style="color:#555; text-align:center; padding:15px;">
                        No hay códigos en el historial de sesión.
                    </div>
                <?php else: ?>
                    <?php foreach (array_reverse($historial) as $item): ?>
                        <div class="history-item">
                            <span class="time">[<?php echo $item['timestamp']; ?>]</span>
                            <span class="code"><?php echo $item['codigo']; ?></span>
                            <span style="color:#ff0055; font-size:0.75rem;">EXPIRADO</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex; gap:15px; margin-top:30px; justify-content:center; flex-wrap:wrap;">
            <a href="?reset=1" class="btn btn-danger">🔄 Reiniciar Sesión</a>
            <a href="index.php" class="btn btn-purple">🏠 Regresar al Dashboard</a>
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
                const segsTotal = Math.max(0, tiempoRestante);
                const mins = Math.floor(segsTotal / 60);
                const secs = segsTotal % 60;
                timerElement.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                
                timerElement.className = 'count';
                if (tiempoRestante <= 20) {
                    timerElement.classList.add('danger');
                } else if (tiempoRestante <= 45) {
                    timerElement.classList.add('warning');
                }
            }
            
            if (progressFill) {
                const porcentaje = Math.max(0, (tiempoRestante / totalTiempo) * 100);
                progressFill.style.width = porcentaje + '%';
                progressFill.className = 'progress-fill';
                if (tiempoRestante <= 20) {
                    progressFill.classList.add('danger');
                } else if (tiempoRestante <= 45) {
                    progressFill.classList.add('warning');
                }
            }
            
            if (tiempoRestante <= 0) {
                location.reload();
            }
        }
        
        setInterval(actualizarTimer, 1000);

        <?php if (!$hackathon_activo): ?>
        // Escuchar inicio del hackathon para auto-actualizar la interfaz
        setInterval(function() {
            fetch('estado_hackathon.php')
                .then(r => r.json())
                .then(data => {
                    if (data && data.hackathon_activo) {
                        location.reload();
                    }
                })
                .catch(() => {});
        }, 10000);
        <?php endif; ?>

        function copiarBandera() {
            const flag = 'FLAG{DINAMIC_WEB_HACKER_2026}';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(flag).then(() => {
                    const btn = document.getElementById('btnCopiarFlag');
                    if (btn) {
                        const originalText = btn.innerHTML;
                        btn.innerHTML = '✅ ¡Copiada!';
                        btn.style.borderColor = '#00ff66';
                        btn.style.color = '#00ff66';
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.style.borderColor = '';
                            btn.style.color = '';
                        }, 2500);
                    }
                    const inputDirect = document.getElementById('flagInputDirect');
                    if (inputDirect && !inputDirect.value) {
                        inputDirect.value = flag;
                    }
                }).catch(() => {
                    prompt('Copia la bandera manualmente:', flag);
                });
            } else {
                prompt('Copia la bandera manualmente:', flag);
            }
        }
    </script>
<script>
console.log(
    '%c[UPTPC SECURITY MONITOR] Entorno de evaluación seguro activo. Telemetría de red y protocolos en monitoreo continuo.',
    'color: #00ff66; font-family: monospace; font-size: 12px; font-weight: bold; background: #0a0e14; padding: 6px 12px; border: 1px solid #00ff66;'
);
</script>

 <footer>
<?php
require_once __DIR__ . '/conf/footer.php';
echo $footer;
?>
        </footer>
