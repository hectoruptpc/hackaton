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
                'saldo' => 0,
                'nombre' => 'Hacker',
                'avatar' => '💻'
            ]
        ],
        'transferencias' => [],
        'penalizaciones' => [],
        'flag_revelada' => false,
        'transfer_pending' => null
    ];
}

$usuario_actual = $_SESSION['usuario'] ?? null;
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

$penalizado = false;
$tiempo_penalizacion = 0;
if ($usuario_actual && isset($_SESSION['banco']['penalizaciones'][$usuario_actual])) {
    $tiempo_restante = $_SESSION['banco']['penalizaciones'][$usuario_actual] - time();
    if ($tiempo_restante > 0) {
        $penalizado = true;
        $tiempo_penalizacion = $tiempo_restante;
    } else {
        unset($_SESSION['banco']['penalizaciones'][$usuario_actual]);
    }
}

if (isset($_POST['logout'])) {
    unset($_SESSION['banco']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = trim($_POST['usuario'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (isset($_SESSION['banco']['usuarios'][$user]) && $_SESSION['banco']['usuarios'][$user]['password'] === $pass) {
        $_SESSION['usuario'] = $user;
        $usuario_actual = $user;
        $saldo_actual = $_SESSION['banco']['usuarios'][$user]['saldo'];
        $nombre_usuario = $_SESSION['banco']['usuarios'][$user]['nombre'];
        $avatar_usuario = $_SESSION['banco']['usuarios'][$user]['avatar'];
        unset($_SESSION['banco']['penalizaciones'][$user]);
        $mensaje = '✅ Sesión iniciada correctamente';
        $mensaje_clase = 'success';
    } else {
        $mensaje = '❌ Usuario o contraseña incorrectos';
        $mensaje_clase = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transferir'])) {
    if (!$usuario_actual) {
        $mensaje = '⚠️ Debes iniciar sesión primero';
        $mensaje_clase = 'warning';
    } elseif ($penalizado) {
        $mensaje = "⛔ Penalizado. Espera {$tiempo_penalizacion} segundos.";
        $mensaje_clase = 'warning';
    } else {
        $destino = trim($_POST['destino'] ?? '');
        $monto = floatval($_POST['monto'] ?? 0);

        if ($destino && $monto > 0) {
            $_SESSION['banco']['transfer_pending'] = [
                'destino' => $destino,
                'monto' => $monto
            ];
            $mostrar_modal = true;
            $destino_modal = $destino;
            $monto_modal = $monto;
        } else {
            $mensaje = '❌ Complete todos los campos correctamente';
            $mensaje_clase = 'error';
        }
    }
}

// Procesar confirmación desde el modal vía GET (CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['confirmar'])) {
    $pending = $_SESSION['banco']['transfer_pending'] ?? null;
    $destino = $pending['destino'] ?? '';
    $monto = floatval($pending['monto'] ?? 0);
    
    // Obtener parámetros de la URL
    $origen_url = trim($_GET['origen'] ?? '');
    $destino_url = trim($_GET['destino'] ?? '');
    $token = trim($_GET['token'] ?? '');
    
    // Verificar si el usuario modificó la URL o la dejó como estaba
    if ($token !== 'csrf_attack') {
        $mensaje = '❌ Token CSRF inválido';
        $mensaje_clase = 'error';
        unset($_SESSION['banco']['transfer_pending']);
    } elseif ($origen_url === 'hacker' && $destino_url === 'mrbeast') {
        // ❌ El usuario NO modificó la URL - PENALIZACIÓN
        $_SESSION['banco']['penalizaciones'][$usuario_actual] = time() + 60;
        $_SESSION['banco']['transferencias'][] = [
            'origen' => $origen_url,
            'destino' => $destino_url,
            'monto' => $monto,
            'fecha' => date('Y-m-d H:i:s'),
            'estado' => 'PENALIZADO'
        ];
        $mensaje = '⛔ ¡PENALIZADO! La idea es robarle a Mr. Beast no darle dinero. 60 segundos de bloqueo.';
        $mensaje_clase = 'warning';
        unset($_SESSION['banco']['transfer_pending']);
    } elseif ($origen_url === 'mrbeast' && $destino_url === 'hacker') {
        // ✅ El usuario SÍ modificó la URL - ROBO EXITOSO
        if ($monto > 0 && $monto <= $_SESSION['banco']['usuarios']['mrbeast']['saldo']) {
            $_SESSION['banco']['usuarios']['mrbeast']['saldo'] -= $monto;
            $_SESSION['banco']['usuarios']['hacker']['saldo'] += $monto;
            $_SESSION['banco']['transferencias'][] = [
                'origen' => $origen_url,
                'destino' => $destino_url,
                'monto' => $monto,
                'fecha' => date('Y-m-d H:i:s'),
                'robo' => true
            ];
            if ($monto >= 1000000) {
                $_SESSION['banco']['flag_revelada'] = true;
                $mensaje = '🔥 ¡ROBO EXITOSO!';
                $mensaje_clase = 'robo';
            } else {
                $mensaje = "✅ Robo parcial: $$monto transferido de Mr. Beast al Hacker";
                $mensaje_clase = 'success';
            }
            $saldo_actual = $_SESSION['banco']['usuarios'][$usuario_actual]['saldo'];
            unset($_SESSION['banco']['transfer_pending']);
        } else {
            $mensaje = '❌ Monto inválido o saldo insuficiente de Mr. Beast';
            $mensaje_clase = 'error';
            unset($_SESSION['banco']['transfer_pending']);
        }
    } else {
        // ❌ Cualquier otra combinación también es penalización
        $_SESSION['banco']['penalizaciones'][$usuario_actual] = time() + 60;
        $_SESSION['banco']['transferencias'][] = [
            'origen' => $origen_url ?: 'desconocido',
            'destino' => $destino_url ?: 'desconocido',
            'monto' => $monto,
            'fecha' => date('Y-m-d H:i:s'),
            'estado' => 'PENALIZADO'
        ];
        $mensaje = '⛔ ¡PENALIZADO! URL incorrecta. Debes modificarla a origen=mrbeast&destino=hacker. 60 segundos de bloqueo.';
        $mensaje_clase = 'warning';
        unset($_SESSION['banco']['transfer_pending']);
    }
}

$historial = $_SESSION['banco']['transferencias'];
$flag_revelada = $_SESSION['banco']['flag_revelada'];
$usuarios = $_SESSION['banco']['usuarios'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏦 Banco de Venezuela - Desafío CSRF</title>
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
        .footer { text-align: center; font-size: 0.8rem; color: #666; margin-top: 20px; }
        .url-bar { background: #263238; color: #FFD700; padding: 10px 14px; border-radius: 10px; font-family: 'Courier New', monospace; font-size: 0.9rem; margin-bottom: 16px; word-break: break-all; border: 2px solid #FFD700; }
        .url-bar .label { color: #90a4ae; text-transform: uppercase; font-size: 0.7rem; }
        .url-bar .highlight { color: #ff5252; font-weight: 700; }
        .hackathon-btn-container { text-align: center; margin: 20px 0; padding: 15px; background: linear-gradient(135deg, #f3e5f5, #e1bee7); border-radius: 16px; border: 2px solid #9c27b0; }
        .hackathon-btn-container .btn-hackathon { font-size: 1.1rem; padding: 16px 40px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } .form-row { flex-direction: column; align-items: stretch; } }
    </style>
</head>
<body<?php echo $penalizado ? ' class="penalized"' : ''; ?>>
    <div class="container">
        <div class="header">
            <div class="brand">🏦 Banco de Venezuela <span>CSRF</span></div>
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

        <?php if ($flag_revelada): ?>
            <div class="message robo">🏆 FLAG desbloqueada: FLAG{ROBO_BANCO}. ¡Robo exitoso mediante CSRF!</div>
        <?php endif; ?>

        <?php if ($penalizado): ?>
            <div class="penalty">
                <div style="font-weight:700; font-size:1.2rem; margin-bottom: 8px; color:#c62828;">⛔ CUENTA BLOQUEADA TEMPORALMENTE</div>
                <div style="color:#5f6368; margin-bottom:10px;">No le Robaste a Mr. Beast</div>
                <div class="timer"><?php echo $tiempo_penalizacion; ?></div>
                <div style="font-size:1.1rem; color:#5f6368; margin-top:4px;">segundos restantes</div>
                <div class="barra">
                    <div class="progreso" style="width:100%;"></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="hint">
            <strong>🎯 Objetivo:</strong> Robar <strong>$1,000,000</strong> de Mr. Beast.
            <br>
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
                    <div style="color:#c62828; margin-top:8px; font-weight:700;">🎯 Objetivo del robo</div>
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
                            <input type="number" name="monto" placeholder="0.00" min="0.01" step="0.01" required />
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

        <!-- Botón para volver al hackathon -->
        <div class="hackathon-btn-container">
            <a href="index.php" class="btn btn-hackathon">
                🏆 Volver al Hackathon
            </a>
            
        </div>

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
                                <?php if (!empty($trans['robo'])): ?>
                                    <span class="robo-tag">🔥 ROBO</span>
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

        <div class="footer">Sistema vulnerable a CSRF - Desafío de seguridad diseñado para hackathon.</div>
    </div>

    <?php if ($penalizado): ?>
        <!-- OVERLAY DE BLOQUEO TOTAL -->
        <div class="penalty-overlay active" id="penaltyOverlay">
            <div class="panel">
                <h2>⛔ CUENTA BLOQUEADA</h2>
                <div style="font-size:1.4rem; color:#ff8a80; margin-bottom:15px;">No le robaste a Mr. Beast</div>
                <div class="count" id="overlayTimer"><?php echo $tiempo_penalizacion; ?></div>
                <p style="font-size:1.2rem; color:#ff8a80; margin-bottom:8px;">Segundos restantes</p>
                <div class="progress-bar">
                    <div class="progress-fill" id="overlayProgress" style="width:100%;"></div>
                </div>
                <p style="color:#aaa; font-size:0.9rem; margin-top:20px;">🔒 No puedes interactuar con el sistema hasta que termine el bloqueo</p>
                <p style="color:#666; font-size:0.8rem; margin-top:10px;">⏱️ Bloqueo de 60 segundos</p>
            </div>
        </div>
    <?php endif; ?>

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
            var params = new URLSearchParams(window.location.search);
            var origen = params.get('origen');
            var destino = params.get('destino');
            var token = params.get('token');
            var confirmar = params.get('confirmar');
            var monto = params.get('monto');
            
            if (origen === 'mrbeast' && destino === 'hacker' && token === 'csrf_attack' && confirmar === '1') {
                location.reload();
            } else if (origen === 'hacker' && destino === 'mrbeast') {
                location.reload();
            } else {
                location.reload();
            }
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
            var tiempo = <?php echo $tiempo_penalizacion; ?>;
            var tiempoInicial = tiempo;
            var timer = document.querySelector('.penalty .timer');
            var barra = document.querySelector('.penalty .progreso');
            var overlayTimer = document.getElementById('overlayTimer');
            var overlayProgress = document.getElementById('overlayProgress');
            var overlay = document.getElementById('penaltyOverlay');
            
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
</body>
</html>