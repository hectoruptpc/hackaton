<?php
/**
 * ============================================================
 * DESAFÍO #9: ROBO BANCO Y MANIPULACIÓN CSRF
 * Unidad de Ciencia y Tecnología — UPTPC 2026
 * ============================================================
 * 
 * 🧠 CONOCIMIENTOS REQUERIDOS:
 * - Manipulación de parámetros HTTP GET/POST y vulnerabilidades de lógica de negocio.
 * - Riesgos de falsificación de peticiones en sitios cruzados (CSRF).
 * 
 * 🛠️ SOLUCIÓN OFICIAL:
 * 1. Iniciar sesión con la cuenta hacker / hack123.
 * 2. Iniciar una transferencia hacia Mr. Beast.
 * 3. En el modal de confirmación, inspeccionar y modificar la URL de GET:
 *    Cambiar origen=hacker&destino=mrbeast por origen=mrbeast&destino=hacker.
 * 4. Presionar Enter en la barra de direcciones para invertir la transferencia.
 * 
 * 🔀 ALTERNATIVAS DE RESOLUCIÓN:
 * - Método A: Modificación manual de la URL en la barra de direcciones del navegador.
 * - Método B: Intercepción de peticiones con herramientas de proxy como Burp Suite.
 * ============================================================
 */

require_once 'conf/functions.php';
session_start();

if (!isset($_SESSION['banco'])) {
    $_SESSION['banco'] = [
        'usuarios' => [
            'mrbeast' => [
                'password' => 'mrbeast123',
                'saldo' => 1000000,
                'nombre' => 'Mr. Beast',
                'avatar' => '🦁'
            ],
            'hacker' => [
                'password' => 'hack123',
                'saldo' => 1000000,
                'nombre' => 'Hacker',
                'avatar' => '💻'
            ]
        ],
        'transferencias'   => [],
        'penalizaciones'   => [],
        'flag_revelada'    => false,
        'total_vulnerado'  => 0,
        'transfer_pending' => null
    ];
}

$usuario_actual = $_SESSION['banco_usuario'] ?? null;
$mensaje = '';
$mensaje_clase = 'success';
$mostrar_modal = false;
$destino_modal = '';
$monto_modal = 0;

if ($usuario_actual && isset($_SESSION['banco']['usuarios'][$usuario_actual])) {
    $saldo_actual = $_SESSION['banco']['usuarios'][$usuario_actual]['saldo'];
    $nombre_usuario = $_SESSION['banco']['usuarios'][$usuario_actual]['nombre'];
    $avatar_usuario = $_SESSION['banco']['usuarios'][$usuario_actual]['avatar'];
} else {
    $saldo_actual = 0;
    $nombre_usuario = 'Invitado';
    $avatar_usuario = '👤';
}

// ── Verificar penalización global persistente (a través de reinicios y recargas) ──
$penalizado = false;
$tiempo_penalizacion = 0;

if (isset($_SESSION['penalizacion_global_banco'])) {
    $tiempo_restante_global = $_SESSION['penalizacion_global_banco'] - time();
    if ($tiempo_restante_global > 0) {
        $penalizado = true;
        $tiempo_penalizacion = $tiempo_restante_global;
    } else {
        unset($_SESSION['penalizacion_global_banco']);
    }
}

if ($usuario_actual && isset($_SESSION['banco']['penalizaciones'][$usuario_actual])) {
    $tiempo_restante_user = $_SESSION['banco']['penalizaciones'][$usuario_actual] - time();
    if ($tiempo_restante_user > 0) {
        $penalizado = true;
        $tiempo_penalizacion = max($tiempo_penalizacion, $tiempo_restante_user);
    } else {
        unset($_SESSION['banco']['penalizaciones'][$usuario_actual]);
    }
}

if (isset($_POST['reset_banco']) || isset($_GET['reset_banco'])) {
    $pen_glob = $_SESSION['penalizacion_global_banco'] ?? null;
    unset($_SESSION['banco'], $_SESSION['banco_usuario']);
    if ($pen_glob && ($pen_glob - time() > 0)) {
        $_SESSION['penalizacion_global_banco'] = $pen_glob;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['logout'])) {
    unset($_SESSION['banco_usuario']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_SESSION['banco_msg'])) {
    $mensaje = $_SESSION['banco_msg'];
    $mensaje_clase = $_SESSION['banco_msg_clase'] ?? 'info';
    unset($_SESSION['banco_msg'], $_SESSION['banco_msg_clase']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = strtolower(trim($_POST['usuario'] ?? ''));
    $pass = trim($_POST['password'] ?? '');

    if (isset($_SESSION['banco']['usuarios'][$user]) && $_SESSION['banco']['usuarios'][$user]['password'] === $pass) {
        $_SESSION['banco_usuario'] = $user;
        $usuario_actual = $user;
        $saldo_actual = $_SESSION['banco']['usuarios'][$user]['saldo'];
        $nombre_usuario = $_SESSION['banco']['usuarios'][$user]['nombre'];
        $avatar_usuario = $_SESSION['banco']['usuarios'][$user]['avatar'];
        unset($_SESSION['banco']['penalizaciones'][$user]);
        $mensaje = '✅ Sesión iniciada correctamente como ' . $nombre_usuario;
        $mensaje_clase = 'success';
    } else {
        $mensaje = '❌ Usuario o contraseña incorrectos';
        $mensaje_clase = 'error';
    }
}

// 1. FORMULARIO INICIAL POST: SIEMPRE ABRE EL MODAL DE CONFIRMACIÓN (NUNCA PENALIZA AQUÍ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transferir'])) {
    if (!$usuario_actual) {
        $mensaje = '⚠️ Debes iniciar sesión primero';
        $mensaje_clase = 'warning';
    } elseif ($penalizado) {
        $mensaje = "⛔ Penalizado. Espera {$tiempo_penalizacion} segundos.";
        $mensaje_clase = 'warning';
    } else {
        $destino = trim($_POST['destino'] ?? '');
        $monto   = floatval($_POST['monto'] ?? 0);

        if ($destino && $monto >= 1) {
            if (!isset($_SESSION['banco']['usuarios'][$destino])) {
                $mensaje = '❌ Destinatario no existe';
                $mensaje_clase = 'error';
            } else {
                // SIEMPRE abrir modal sin importar el monto
                $_SESSION['banco']['transfer_pending'] = [
                    'destino' => $destino,
                    'monto'   => $monto
                ];
                $mostrar_modal  = true;
                $destino_modal  = $destino;
                $monto_modal    = $monto;
            }
        } else {
            $mensaje = '❌ Complete todos los campos. El monto mínimo es $1';
            $mensaje_clase = 'error';
        }
    }
}

// 2. CONFIRMACIÓN VÍA GET (CSRF): AQUÍ ES DONDE SE EVALÚA Y OCURRE LA PENALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['confirmar'])) {
    $pending     = $_SESSION['banco']['transfer_pending'] ?? null;
    $monto       = floatval($_GET['monto'] ?? ($pending['monto'] ?? 0));
    $origen_url  = strtolower(trim($_GET['origen'] ?? ''));
    $destino_url = strtolower(trim($_GET['destino'] ?? ''));
    $token       = trim($_GET['token'] ?? '');

    unset($_SESSION['banco']['transfer_pending']);

    if ($monto > 160000) {
        // ❌ MONTO DEMASIADO ALTO → PENALIZACIÓN PERSISTENTE DE 15 SEGUNDOS
        $_SESSION['penalizacion_global_banco'] = time() + 15;
        if ($usuario_actual) {
            $_SESSION['banco']['penalizaciones'][$usuario_actual] = time() + 15;
        }
        $_SESSION['banco']['transferencias'][] = [
            'origen'  => $origen_url ?: 'desconocido',
            'destino' => $destino_url ?: 'desconocido',
            'monto'   => $monto,
            'fecha'   => date('Y-m-d H:i:s'),
            'estado'  => 'PENALIZADO'
        ];
        $_SESSION['banco_msg']       = '⛔ ¡PENALIZADO! Transacción sospechosa detectada. Su transferencia es sospechosa por monto muy alto. 15 segundos de bloqueo.';
        $_SESSION['banco_msg_clase'] = 'warning';
    } elseif ($token !== 'csrf_attack') {
        $_SESSION['banco_msg']      = '❌ Token CSRF inválido';
        $_SESSION['banco_msg_clase'] = 'error';

    } elseif ($origen_url === 'hacker' && $destino_url === 'mrbeast') {
        // ❌ NO MODIFICÓ LA URL (SE QUEDÓ TRANSFIRIENDO A MR BEAST) → PENALIZACIÓN PERSISTENTE
        $_SESSION['penalizacion_global_banco'] = time() + 15;
        if ($usuario_actual) {
            $_SESSION['banco']['penalizaciones'][$usuario_actual] = time() + 15;
            if ($monto >= 1 && $monto <= $_SESSION['banco']['usuarios']['hacker']['saldo']) {
                $_SESSION['banco']['usuarios']['hacker']['saldo']   -= $monto;
                $_SESSION['banco']['usuarios']['mrbeast']['saldo']  += $monto;
            }
        }
        $_SESSION['banco']['transferencias'][] = [
            'origen'  => $origen_url,
            'destino' => $destino_url,
            'monto'   => $monto,
            'fecha'   => date('Y-m-d H:i:s'),
            'estado'  => 'PENALIZADO'
        ];
        $_SESSION['banco_msg']      = '⛔ ¡PENALIZADO! La idea es robarle a Mr. Beast, no darle dinero. 15 segundos de bloqueo.';
        $_SESSION['banco_msg_clase'] = 'warning';

    } elseif ($origen_url === 'mrbeast' && $destino_url === 'hacker') {
        // ✅ MODIFICÓ LA URL CORRECTAMENTE → VULNERABILIDAD EJECUTADA
        if ($monto >= 1 && $monto <= $_SESSION['banco']['usuarios']['mrbeast']['saldo']) {
            $_SESSION['banco']['usuarios']['mrbeast']['saldo'] -= $monto;
            $_SESSION['banco']['usuarios']['hacker']['saldo']  += $monto;
            $_SESSION['banco']['total_vulnerado']              += $monto;
            $_SESSION['banco']['transferencias'][] = [
                'origen'  => $origen_url,
                'destino' => $destino_url,
                'monto'   => $monto,
                'fecha'   => date('Y-m-d H:i:s'),
                'vulnerado' => true
            ];
            $tv = $_SESSION['banco']['total_vulnerado'];
            if ($tv >= 800000) {
                $_SESSION['banco']['flag_revelada'] = true;
            }
            // Calcular cuántas partes se revelaron (5 etapas de 160,000)
            $partes_antes = min(5, (int)floor(($tv - $monto) / 160000));
            $partes_ahora = min(5, (int)floor($tv / 160000));
            if ($partes_ahora > $partes_antes) {
                $msg_extra = ' ⚡ ¡Nueva parte de la bandera desbloqueada! (' . $partes_ahora . '/5)';
            } else {
                $msg_extra = ' Sigue vulnerando hasta alcanzar $800,000 para la bandera completa.';
            }
            if ($tv >= 800000) {
                $msg_extra = ' 🏆 ¡BANDERA COMPLETA DESBLOQUEADA!';
            }
            $_SESSION['banco_msg']      = '⚡ ¡VULNERABILIDAD EJECUTADA! Has transferido $' . number_format($monto, 2) . ' desde la cuenta de Mr. Beast.' . $msg_extra;
            $_SESSION['banco_msg_clase'] = 'robo';
        } elseif ($monto > $_SESSION['banco']['usuarios']['mrbeast']['saldo']) {
            $_SESSION['banco_msg']       = '❌ Saldo insuficiente de Mr. Beast. Máximo: $' . number_format($_SESSION['banco']['usuarios']['mrbeast']['saldo'], 2);
            $_SESSION['banco_msg_clase'] = 'error';
        } else {
            $_SESSION['banco_msg']       = '❌ Monto inválido. Debe ser mayor a $0.';
            $_SESSION['banco_msg_clase'] = 'error';
        }
    } else {
        // ❌ OTRA COMBINACIÓN O URL MAL FORMADA → PENALIZACIÓN PERSISTENTE
        $_SESSION['penalizacion_global_banco'] = time() + 15;
        if ($usuario_actual) {
            $_SESSION['banco']['penalizaciones'][$usuario_actual] = time() + 15;
        }
        $_SESSION['banco']['transferencias'][] = [
            'origen'  => $origen_url ?: 'desconocido',
            'destino' => $destino_url ?: 'desconocido',
            'monto'   => $monto,
            'fecha'   => date('Y-m-d H:i:s'),
            'estado'  => 'PENALIZADO'
        ];
        $_SESSION['banco_msg']       = '⛔ ¡PENALIZADO! URL incorrecta. Debes cambiarla a origen=mrbeast&destino=hacker. 15 segundos de bloqueo.';
        $_SESSION['banco_msg_clase'] = 'warning';
    }

    // Forzar escritura de sesión antes de redirigir
    session_write_close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$historial        = $_SESSION['banco']['transferencias'];
$flag_revelada    = $_SESSION['banco']['flag_revelada'];
$usuarios         = $_SESSION['banco']['usuarios'];
$total_vulnerado  = $_SESSION['banco']['total_vulnerado'] ?? 0;

// ── Bandera progresiva (5 etapas × $160,000 = $800,000 completo) ────────
function getFlagParcial(float $total): string {
    $flag   = 'FLAG{VULNERABILIDAD_OBTENIDA_BANCO_HACK}';
    $len    = strlen($flag);   // 40 chars
    $maximo = 800000;
    if ($total <= 0)       return str_repeat('?', $len);
    if ($total >= $maximo) return $flag;
    $parte        = min(4, (int)floor($total / 160000));  // 0-4
    $chars_reveal = $parte * 8;                           // 0,8,16,24,32
    return substr($flag, 0, $chars_reveal) . str_repeat('?', $len - $chars_reveal);
}
$flag_parcial      = getFlagParcial($total_vulnerado);
$partes_obtenidas  = min(5, (int)floor($total_vulnerado / 160000));
$pct_bandera       = min(100, round(($total_vulnerado / 800000) * 100));
// ── Cabecera modular ─────────────────────────────────────────────────────────
$page_title = '🏦 Banco HACK - Desafío CSRF | Hackathon UPTPC 2026';
// La clase del body es dinámica según si hay penalización activa
$body_attrs = $penalizado ? 'class="penalized"' : '';
require_once __DIR__ . '/conf/header.php';
echo $header;
?>
<!-- Estilos específicos del Desafío Banco CSRF -->
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #001a3a 0%, #002d5a 50%, #001a3a 100%); font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { width: 100%; max-width: 980px; background: #fff; border-radius: 18px; padding: 30px; box-shadow: 0 25px 70px rgba(0, 0, 0, 0.35); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 20px; background: linear-gradient(90deg, #002147, #003366); border-radius: 18px; color: #fff; }
        .brand { font-size: 1.8rem; font-weight: 700; }
        .brand span { color: #FFD700; }
        .user-box { text-align: right; font-size: 0.95rem; }
        .user-box .name { font-weight: 700; color: #FFD700; }
        .nav { display: flex; gap: 18px; margin-bottom: 20px; flex-wrap: wrap; }
        .nav a { color: #003366; text-decoration: none; padding: 12px 16px; background: #f0f2f5; border-radius: 12px; font-weight: 600; }
        .nav a.active { background: #002147; color: #fff; }
        .card { background: #f8f9fa; border-radius: 16px; border: 1px solid #e4e7eb; padding: 22px; margin-bottom: 20px; }
        .card-title { font-size: 1.2rem; font-weight: 700; color: #003366; margin-bottom: 16px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .small-card { background: #fff; border: 1px solid #e4e7eb; border-radius: 16px; padding: 18px; }
        .label { text-transform: uppercase; font-size: 0.75rem; color: #5f6368; font-weight: 700; letter-spacing: 1px; }
        .value { margin-top: 8px; font-size: 1.8rem; font-weight: 700; color: #003366; }
        .value .currency { font-size: 1rem; color: #5f6368; margin-right: 5px; }
        .hint { background: #fff8e1; border-left: 4px solid #FFD700; padding: 14px 16px; border-radius: 0 14px 14px 0; color: #5f6368; margin-bottom: 20px; }
        .hint strong { color: #003366; }
        .message { padding: 16px; border-radius: 14px; margin-bottom: 20px; font-size: 0.95rem; }
        .message.success { background: #e8f5e9; border: 1px solid #4caf50; color: #2e7d32; }
        .message.error { background: #ffebee; border: 1px solid #ef5350; color: #c62828; }
        .message.warning { background: #fff4e5; border: 1px solid #ff9800; color: #e65100; }
        .message.robo { background: linear-gradient(135deg, #fff9c4, #fff176); border: 2px solid #FFD700; color: #003366; font-weight: 700; }
        .penalty { background: #ffebee; border: 2px solid #ef5350; border-radius: 16px; padding: 20px; text-align: center; margin-bottom: 20px; }
        .penalty .timer { font-size: 3.5rem; font-weight: 900; color: #c62828; }
        .penalty .barra { width: 100%; height: 12px; background: #fdecea; border-radius: 999px; margin-top: 14px; overflow: hidden; border: 1px solid #f5c2c0; }
        .penalty .progreso { height: 100%; background: linear-gradient(90deg, #ff8800, #ff4444); width: 100%; transition: width 1s linear; }
        
        /* ESTILOS DE BLOQUEO TOTAL */
        .penalty-overlay { 
            display: none; 
            position: fixed; 
            inset: 0; 
            background: rgba(0,0,0,0.95); 
            color: #fff; 
            z-index: 9999;
            justify-content: center; 
            align-items: center; 
            text-align: center; 
            padding: 30px; 
            backdrop-filter: blur(10px);
        }
        .penalty-overlay.active { 
            display: flex; 
        }
        body.penalized { 
            overflow: hidden; 
        }
        body.penalized .container,
        body.penalized .modal,
        body.penalized .nav,
        body.penalized .card,
        body.penalized .footer,
        body.penalized .message,
        body.penalized .hint,
        body.penalized form,
        body.penalized .bdv-table { 
            pointer-events: none; 
            filter: blur(5px) brightness(0.5);
            user-select: none;
        }
        .penalty-overlay {
            pointer-events: auto !important;
            cursor: not-allowed;
        }
        .penalty-overlay .panel {
            pointer-events: auto;
            cursor: default;
        }
        .penalty-overlay .panel {
            width: 100%; 
            max-width: 620px; 
            padding: 50px; 
            border-radius: 24px; 
            border: 3px solid rgba(255, 82, 82, 0.8); 
            background: rgba(34, 34, 34, 0.98); 
            box-shadow: 0 0 60px rgba(255, 0, 0, 0.3);
        }
        .penalty-overlay h2 { 
            font-size: 3.2rem; 
            margin-bottom: 20px; 
            color: #ff8a80; 
            text-shadow: 0 0 30px rgba(255, 0, 0, 0.5); 
        }
        .penalty-overlay .count { 
            font-size: 7rem; 
            font-weight: 900; 
            color: #ffeb3b; 
            margin-bottom: 16px; 
            letter-spacing: -0.04em; 
            text-shadow: 0 0 40px rgba(255, 215, 0, 0.3); 
        }
        .penalty-overlay p { 
            font-size: 1.1rem; 
            color: #e0e0e0; 
            line-height: 1.6; 
        }
        .penalty-overlay .progress-bar {
            width: 100%; 
            height: 10px; 
            background: #333; 
            border-radius: 999px; 
            margin: 20px 0; 
            overflow: hidden;
            border: 1px solid #555;
        }
        .penalty-overlay .progress-fill {
            height: 100%; 
            background: linear-gradient(90deg, #ff8800, #ff4444); 
            width: 100%; 
            transition: width 0.5s linear;
        }
        
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .form-group { flex: 1; min-width: 180px; display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-weight: 700; color: #003366; }
        .form-group input, .form-group select { border-radius: 12px; border: 2px solid #d4d7dc; padding: 12px 14px; font-size: 0.95rem; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #FFD700; box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2); }
        .btn { border: none; border-radius: 12px; padding: 14px 24px; font-weight: 700; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .btn-primary { background: linear-gradient(90deg, #003366, #004080); color: #fff; }
        .btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15); }
        .btn-danger { background: linear-gradient(90deg, #8B0000, #A52A2A); color: #fff; }
        .btn-danger:hover:not(:disabled) { transform: translateY(-2px); }
        .btn-success { background: linear-gradient(90deg, #2e7d32, #388e3c); color: #fff; }
        .btn-success:hover:not(:disabled) { transform: translateY(-2px); }
        .btn-hackathon { background: linear-gradient(90deg, #6a1b9a, #8e24aa); color: #fff; }
        .btn-hackathon:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(106, 27, 154, 0.4); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .bdv-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .bdv-table th, .bdv-table td { padding: 12px 14px; border-bottom: 1px solid #e8eaed; }
        .bdv-table th { background: #f0f2f5; text-align: left; color: #003366; }
        .bdv-table tr:hover { background: #f8f9fa; }
        .bdv-table .amount { font-weight: 700; color: #003366; }
        .robo-tag { color: #c62828; font-weight: 700; }
        .modal { display: <?php echo $mostrar_modal ? 'flex' : 'none'; ?>; position: fixed; inset: 0; background: rgba(0,0,0,0.65); justify-content: center; align-items: center; z-index: 1000; padding: 20px; }
        .modal-content { width: 100%; max-width: 480px; background: #fff; border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 30px 80px rgba(0,0,0,0.25); }
        .modal-content h2 { font-size: 1.4rem; color: #003366; margin-bottom: 16px; }
        .modal-detail { background: #f8f9fa; padding: 18px; border-radius: 14px; margin: 18px 0; color: #5f6368; }
        .modal-detail .amount { font-size: 1.8rem; color: #003366; font-weight: 700; }
        .modal-detail .destino { font-size: 1rem; color: #c62828; font-weight: 700; }
        .modal-note { background: #fff3e0; border: 1px solid #ffcc80; border-radius: 14px; color: #e65100; padding: 14px; margin-bottom: 16px; text-align: left; }
        .modal-note strong { color: #bf360c; }
        .modal-url-box { background: #e8f5e9; border: 2px solid #66bb6a; border-radius: 14px; padding: 14px; margin-bottom: 16px; text-align: left; }
        .modal-url-box .url-label { font-weight: 700; color: #1b5e20; display: block; margin-bottom: 6px; }
        .modal-url-box .url-text { font-family: 'Courier New', monospace; font-size: 0.85rem; word-break: break-all; background: #c8e6c9; padding: 8px; border-radius: 8px; color: #1b5e20; }
        .modal-warning { background: #ffebee; border: 2px solid #ef5350; border-radius: 14px; padding: 14px; margin-bottom: 16px; text-align: left; }
        .modal-warning .url-label { font-weight: 700; color: #c62828; display: block; margin-bottom: 6px; }
        .modal-warning .url-text { font-family: 'Courier New', monospace; font-size: 0.85rem; word-break: break-all; background: #ffcdd2; padding: 8px; border-radius: 8px; color: #b71c1c; }
        .modal-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .footer { text-align: center; font-size: 0.8rem; color: #000000; margin-top: 20px; }
        .url-bar { background: #263238; color: #FFD700; padding: 10px 14px; border-radius: 10px; font-family: 'Courier New', monospace; font-size: 0.9rem; margin-bottom: 16px; word-break: break-all; border: 2px solid #FFD700; }
        .url-bar .label { color: #90a4ae; text-transform: uppercase; font-size: 0.7rem; }
        .url-bar .highlight { color: #ff5252; font-weight: 700; }
        .hackathon-btn-container { text-align: center; margin: 20px 0; padding: 15px; background: linear-gradient(135deg, #f3e5f5, #e1bee7); border-radius: 16px; border: 2px solid #9c27b0; }
        .hackathon-btn-container .btn-hackathon { font-size: 1.1rem; padding: 16px 40px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } .form-row { flex-direction: column; align-items: stretch; } }
</style>

    <div class="container">
        <div class="header">
            <div class="brand">🏦 Banco HACK <span>CSRF</span></div>
            <div class="user-box">
                <?php if ($usuario_actual): ?>
                    <div class="name"><?php echo $avatar_usuario; ?> <?php echo htmlspecialchars($nombre_usuario); ?></div>
                    <div>@<?php echo htmlspecialchars($usuario_actual); ?></div>
                <?php else: ?>
                    <div>Bienvenido</div>
                    <div>Inicia sesión para continuar</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="nav">
            <a href="#" class="active">💰 Cuentas</a>
            <a href="#">📊 Transferencias</a>
            <a href="#">📋 Movimientos</a>
            <a href="#">⚙️ Seguridad</a>
        </div>

        <?php if ($total_vulnerado > 0): ?>
        <div style="background:#1a1a2e;border:2px solid <?php echo $flag_revelada?'#FFD700':'#4a4a6a';?>;border-radius:16px;padding:20px 24px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <div style="font-weight:700;font-size:1.05rem;color:<?php echo $flag_revelada?'#FFD700':'#aaa';?>">
                    <?php echo $flag_revelada ? '🏆 BANDERA COMPLETA DESBLOQUEADA' : '🔓 Bandera descubierta parcialmente ('.$partes_obtenidas.'/5 etapas)'; ?>
                </div>
                <div style="font-size:0.85rem;color:#888;">Total vulnerado: <strong style="color:#22c55e;">$<?php echo number_format($total_vulnerado,2); ?></strong></div>
            </div>
            <!-- Barra de progreso -->
            <div style="background:#2d2d4e;border-radius:999px;height:10px;margin-bottom:14px;overflow:hidden;">
                <div style="height:100%;width:<?php echo $pct_bandera; ?>%;background:linear-gradient(90deg,#6366f1,#22c55e);border-radius:999px;transition:width 0.5s;"></div>
            </div>
            <!-- Banderas por etapa -->
            <div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
                <?php for($i=1;$i<=5;$i++): ?>
                <div style="flex:1;min-width:40px;text-align:center;padding:10px 4px;border-radius:8px;font-size:0.85rem;
                    background:<?php echo $partes_obtenidas>=$i?'rgba(34,197,94,0.15)':'rgba(255,255,255,0.04)'; ?>;
                    border:1px solid <?php echo $partes_obtenidas>=$i?'#22c55e':'#333'; ?>;
                    color:<?php echo $partes_obtenidas>=$i?'#4ade80':'#555'; ?>">
                    <?php echo $partes_obtenidas>=$i?'⚡':'🔒'; ?> Etapa <?php echo $i; ?>
                </div>
                <?php endfor; ?>
            </div>
            <!-- Bandera (parcial o completa) -->
            <div style="background:#0d0d1a;border:1px solid <?php echo $flag_revelada?'#FFD700':'#333'; ?>;
                border-radius:10px;padding:14px 18px;font-family:'Courier New',monospace;
                font-size:1.1rem;letter-spacing:0.04em;word-break:break-all;
                color:<?php echo $flag_revelada?'#FFD700':'#4ade80'; ?>;">
                <?php
                    $fp = $flag_parcial;
                    // Colorear parte revelada vs oculta
                    $revealed = htmlspecialchars(substr($fp, 0, $partes_obtenidas * 8));
                    $hidden   = str_repeat('?', max(0, 40 - $partes_obtenidas * 8));
                    echo '<span style="color:'.($flag_revelada?'#FFD700':'#4ade80').';font-weight:700;">'.$revealed.'</span>';
                    if ($hidden) echo '<span style="color:#444;">'.$hidden.'</span>';
                ?>
            </div>
            <?php if ($flag_revelada): ?>
            <div style="margin-top:12px;text-align:center;font-size:0.95rem;color:#FFD700;font-weight:700;">
                🎉 Copia la bandera completa y valídala en el formulario para ganar los puntos.
            </div>
            <?php else: ?>
            <div style="margin-top:10px;font-size:0.82rem;color:#666;text-align:center;">
                Continúa ejecutando la vulnerabilidad CSRF para desbloquear las etapas restantes.
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($penalizado): ?>
            <div class="penalty">
                <div style="font-weight:700; font-size:1.2rem; margin-bottom: 8px; color:#c62828;">⛔ CUENTA BLOQUEADA TEMPORALMENTE</div>
                <div style="color:#5f6368; margin-bottom:10px;">No ejecutaste correctamente la vulnerabilidad CSRF</div>
                <div class="timer"><?php echo $tiempo_penalizacion; ?></div>
                <div style="font-size:1.1rem; color:#5f6368; margin-top:4px;">segundos restantes</div>
                <div class="barra">
                    <div class="progreso" style="width:100%;"></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="hint">
            <strong>🎯 Objetivo:</strong> Ejecuta la vulnerabilidad <strong>CSRF</strong> para transferir fondos desde la cuenta de Mr. Beast hacia la tuya.<br>
            ⚠️ <strong>Políticas de Seguridad Banco Hack:</strong> Las transacciones excesivas o por montos demasiado altos activarán la alarma anti-fraude y bloquearán la cuenta por 15 segundos.<br>
            Desbloquea la bandera completa ejecutando transferencias progresivas hasta revelar la última etapa.
        </div>

        <?php if ($mensaje): ?>
            <div class="message <?php echo $mensaje_clase; ?>"><?php echo nl2br(htmlspecialchars($mensaje)); ?></div>
        <?php endif; ?>

        <?php if ($usuario_actual): ?>
            <div class="grid">
                <div class="small-card">
                    <div class="label">Tu saldo</div>
                    <div class="value"><span class="currency">$</span><?php echo number_format($saldo_actual, 2); ?></div>
                    <div style="color:#5f6368; margin-top:8px;">Cuenta: <?php echo htmlspecialchars($nombre_usuario); ?></div>
                </div>
                <div class="small-card">
                    <div class="label">Saldo Mr. Beast</div>
                    <div class="value" style="color:#c62828;"><span class="currency">$</span><?php echo number_format($usuarios['mrbeast']['saldo'], 2); ?></div>
                    <div style="color:#c62828; margin-top:8px; font-weight:700;">🎯 Objetivo de la vulnerabilidad</div>
                </div>
            </div>

            <div class="card">
                <div class="card-title">💸 Realizar Transferencia</div>
                <form method="POST" id="transferForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Destino</label>
                            <select name="destino" required>
                                <option value="">Seleccionar beneficiario</option>
                                <?php foreach ($usuarios as $user => $data): ?>
                                    <?php if ($user !== $usuario_actual): ?>
                                        <option value="<?php echo htmlspecialchars($user); ?>" <?php echo $user === 'mrbeast' ? 'style="color:#c62828;font-weight:700;"' : ''; ?> >
                                            <?php echo $data['avatar']; ?> <?php echo htmlspecialchars($data['nombre']); ?> (@<?php echo htmlspecialchars($user); ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Monto ($)</label>
                            <input type="number" name="monto" placeholder="Monto ($)" min="1" step="1" required />
                        </div>
                        <div class="form-group" style="min-width:170px;">
                            <label>&nbsp;</label>
                            <button type="submit" name="transferir" class="btn btn-primary" <?php echo $penalizado ? 'disabled' : ''; ?>>Enviar transferencia</button>
                        </div>
                    </div>
                </form>
            </div>

            <div style="text-align:right; margin-bottom:20px;">
                <form method="POST"><button type="submit" name="logout" class="btn btn-danger">Cerrar sesión</button></form>
            </div>
        <?php else: ?>
            <div class="card" style="max-width:400px; margin:0 auto;">
                <div class="card-title">🔐 Iniciar sesión</div>
                <form method="POST">
                    <div class="form-group"><label>Usuario</label><input type="text" name="usuario" required></div>
                    <div class="form-group"><label>Contraseña</label><input type="password" name="password" required></div>
                    <button type="submit" name="login" class="btn btn-primary" style="width:100%; margin-top:10px;">Ingresar</button>
                </form>
                <div style="margin-top:14px; font-size:0.9rem; color:#5f6368;">
                    Usuario: <strong>hacker</strong><br>
                    Contraseña: <strong>hack123</strong>
                </div>
            </div>
        <?php endif; ?>

        <!-- Botones inferiores -->
        <div class="hackathon-btn-container" style="display:flex; justify-content:center; gap:15px; flex-wrap:wrap;">
            <a href="index.php" class="btn btn-hackathon">
                🏆 Volver al Hackathon
            </a>
            <button type="button" class="btn btn-warning" onclick="abrirModalReset()">🔄 Reiniciar Desafío</button>
        </div>
        <!-- Formulario oculto para el reset real -->
        <form method="POST" id="formResetBanco" style="display:none;">
            <input type="hidden" name="reset_banco" value="1">
        </form>

        <div class="card">
            <div class="card-title">📋 Historial de transferencias</div>
            <?php if (empty($historial)): ?>
                <div style="color:#5f6368; text-align:center; padding:20px;">No hay transferencias registradas.</div>
            <?php else: ?>
                <div style="overflow-x:auto;"><table class="bdv-table"><thead><tr><th>Fecha</th><th>Origen</th><th>Destino</th><th style="text-align:right;">Monto</th><th>Estado</th></tr></thead><tbody>
                    <?php foreach (array_reverse($historial) as $trans): ?>
                        <tr>
                            <td style="font-size:0.85rem; color:#5f6368;"><?php echo htmlspecialchars($trans['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($trans['origen']); ?></td>
                            <td><?php echo htmlspecialchars($trans['destino']); ?></td>
                            <td style="text-align:right;" class="amount">$<?php echo number_format($trans['monto'], 2); ?></td>
                            <td>
                                <?php if (!empty($trans['vulnerado'])): ?>
                                    <span class="robo-tag">⚡ VULNERABILIDAD EJECUTADA</span>
                                <?php elseif (!empty($trans['estado']) && $trans['estado'] === 'PENALIZADO'): ?>
                                    <span style="color:#e65100; font-weight:700;">⛔ PENALIZADO</span>
                                <?php else: ?>
                                    <span style="color:#4caf50; font-weight:700;">✅ COMPLETADO</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>

        <div class="footer">

<hr>        
        Sistema vulnerable a CSRF - Desafío de seguridad diseñado para hackathon.</div>
  <br>
<?php
require_once __DIR__ . '/conf/footer.php';
echo $footer;
?>
  </div>

    <?php if ($penalizado): ?>
        <!-- OVERLAY DE BLOQUEO TOTAL -->
        <div class="penalty-overlay active" id="penaltyOverlay">
            <div class="panel">
                <h2>⛔ CUENTA BLOQUEADA</h2>
                <div style="font-size:1.4rem; color:#ff8a80; margin-bottom:15px;">Su transferencia es sospechosa por monto muy alto</div>
                <div class="count" id="overlayTimer"><?php echo $tiempo_penalizacion; ?></div>
                <p style="font-size:1.2rem; color:#ff8a80; margin-bottom:8px;">Segundos restantes</p>
                <div class="progress-bar">
                    <div class="progress-fill" id="overlayProgress" style="width:100%;"></div>
                </div>
                <p style="color:#aaa; font-size:0.9rem; margin-top:20px;">🔒 No puedes interactuar con el sistema hasta que termine el bloqueo</p>
                <p style="color:#666; font-size:0.8rem; margin-top:10px;">⏱️ Bloqueo de 15 segundos</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- ===== MODAL CONFIRMACION REINICIO ===== -->
    <div id="modalReset" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9000;
         justify-content:center;align-items:center;padding:20px;backdrop-filter:blur(6px);">
        <div style="width:100%;max-width:460px;background:#fff;border-radius:20px;padding:32px;text-align:center;
                    box-shadow:0 30px 80px rgba(0,0,0,0.35);">
            <div style="font-size:3rem;margin-bottom:12px;">⚠️</div>
            <h2 style="color:#003366;font-size:1.4rem;margin-bottom:12px;">¿Reiniciar el Desafío?</h2>
            <div style="background:#fff3e0;border:1px solid #ffcc80;border-radius:12px;padding:16px;margin-bottom:20px;color:#e65100;font-size:0.95rem;line-height:1.6;">
                <strong>⏱️ Atención:</strong> Reiniciar el desafío implica una
                <strong>penalización de 15 segundos</strong> durante los cuales
                el sistema estará completamente bloqueado.<br><br>
                Al finalizar, <strong>todos los saldos, el historial y el progreso
                de la bandera serán borrados</strong> y el desafío volverá al estado inicial.
            </div>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
                <button class="btn btn-danger" onclick="cerrarModalReset()">❌ Cancelar</button>
                <button class="btn btn-primary" onclick="confirmarReset()">✅ Aceptar y reiniciar</button>
            </div>
        </div>
    </div>

    <!-- ===== OVERLAY 15 SEGUNDOS DE REINICIO ===== -->
    <div id="resetOverlay" class="penalty-overlay" style="display:none;">
        <div class="panel">
            <h2>🔄 REINICIANDO DESAFÍO</h2>
            <div style="font-size:1.2rem;color:#90caf9;margin-bottom:18px;">El desafío se reiniciará al terminar la cuenta regresiva</div>
            <div class="count" id="resetTimer">15</div>
            <p style="font-size:1.2rem;color:#ff8a80;margin-bottom:8px;">Segundos restantes</p>
            <div class="progress-bar">
                <div class="progress-fill" id="resetProgress" style="width:100%;"></div>
            </div>
            <p style="color:#aaa;font-size:0.9rem;margin-top:20px;">🔒 No puedes interactuar con el sistema durante el reinicio</p>
            <p style="color:#666;font-size:0.8rem;margin-top:10px;">⏱️ Penalización de 15 segundos por reinicio</p>
        </div>
    </div>

    <div class="modal" id="modalConfirmacion">
        <div class="modal-content">
            <h2>⚠️ Confirmar transferencia</h2>
            <div class="modal-detail">
                <div>Va a transferir</div>
                <div class="amount">$<?php echo number_format($monto_modal, 2); ?></div>
                <div style="margin-top:10px;">a <span class="destino"><?php echo htmlspecialchars($destino_modal); ?></span></div>
            </div>
            
            <div class="modal-buttons">
                <button class="btn btn-danger" onclick="cerrarModal()">Cancelar</button>
                <button class="btn btn-success" onclick="confirmarTransferencia()">✅ Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        function cerrarModal() {
            document.getElementById('modalConfirmacion').style.display = 'none';
            window.history.replaceState(null, '', window.location.pathname);
        }
        
        function confirmarTransferencia() {
            // Usar location.href en lugar de location.reload() para garantizar GET con los params actuales
            var url = window.location.href;
            window.location.href = url;
        }

        <?php if ($mostrar_modal): ?>
            (function() {
                var monto = <?php echo $monto_modal; ?>;
                var urlConPayload = window.location.pathname + '?origen=hacker&destino=mrbeast&monto=' + monto + '&token=csrf_attack&confirmar=1';
                window.history.replaceState(null, '', urlConPayload);
                
                var urlOriginal = document.getElementById('urlOriginal');
                if (urlOriginal) {
                    urlOriginal.textContent = '?origen=hacker&destino=mrbeast&monto=' + monto + '&token=csrf_attack&confirmar=1';
                }
                
                var urlDisplay = document.getElementById('urlDisplay');
                if (urlDisplay) {
                    urlDisplay.textContent = urlConPayload;
                }
            })();
        <?php endif; ?>

        <?php if ($penalizado): ?>
            document.body.classList.add('penalized');
            var tiempo = <?php echo $tiempo_penalizacion; ?>;
            var tiempoInicial = tiempo;
            var timer = document.querySelector('.penalty .timer');
            var barra = document.querySelector('.penalty .progreso');
            var overlayTimer = document.getElementById('overlayTimer');
            var overlayProgress = document.getElementById('overlayProgress');
            var overlay = document.getElementById('penaltyOverlay');
            if (overlay) overlay.classList.add('active');
            
            var intervalo = setInterval(function() {
                tiempo--;
                if (timer) timer.textContent = tiempo;
                if (overlayTimer) overlayTimer.textContent = tiempo;
                
                var porcentaje = Math.max(0, (tiempo / tiempoInicial) * 100);
                if (barra) barra.style.width = porcentaje + '%';
                if (overlayProgress) overlayProgress.style.width = porcentaje + '%';
                
                if (tiempo <= 0) {
                    clearInterval(intervalo);
                    if (overlay) overlay.classList.remove('active');
                    document.body.classList.remove('penalized');
                    location.reload();
                }
            }, 1000);
        <?php endif; ?>

        // ── FUNCIONES MODAL REINICIO ──────────────────────────────────
        function abrirModalReset() {
            var m = document.getElementById('modalReset');
            m.style.display = 'flex';
        }
        function cerrarModalReset() {
            var m = document.getElementById('modalReset');
            m.style.display = 'none';
        }
        function confirmarReset() {
            // 1. Cerrar modal de confirmación
            cerrarModalReset();

            // 2. Bloquear toda la interfaz (igual que penalización)
            document.body.classList.add('penalized');

            // 3. Mostrar el overlay de 15 segundos
            var overlay = document.getElementById('resetOverlay');
            overlay.style.display = 'flex';
            overlay.classList.add('active');

            // 4. Iniciar cuenta regresiva
            var segundos = 15;
            var timerEl  = document.getElementById('resetTimer');
            var progEl   = document.getElementById('resetProgress');

            var intervaloReset = setInterval(function() {
                segundos--;
                if (timerEl)  timerEl.textContent = segundos;
                var pct = Math.max(0, (segundos / 15) * 100);
                if (progEl)   progEl.style.width = pct + '%';

                if (segundos <= 0) {
                    clearInterval(intervaloReset);
                    // 5. Enviar el formulario de reset al terminar
                    document.getElementById('formResetBanco').submit();
                }
            }, 1000);
        }
    </script>
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
║   │   😂 ¿ROBANDO A MR. BEAST DESDE LA CONSOLA? 😂          │   ║
║   │                                                          │   ║
║   │   JAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJA   │   ║
║   │   Aquí no hay dinero ni dinero ni banderas gratis.       │   ║
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
console.log('%c🚫 NO PIERDAS TU TIEMPO EN F12, AQUÍ NO HAY NADA 🚫', 'color: #ff0000; font-size: 16px; font-weight: bold; background: #1a0000; padding: 8px;');
console.log('%c🤣 TE QUEDASTE CON LAS MANOS VACÍAS 🤣', 'color: #ffff00; font-size: 16px; font-weight: bold;');
</script>

