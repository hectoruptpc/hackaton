<?php
session_start();
require_once __DIR__ . '/../conf/functions.php';

$resultado_html = '';
$mostrar_resultado = false;
$estado = obtenerEstadoBiometrico();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patron'])) {
    $patron = $_POST['patron'];
    $resultado = verificarPatronBiometrico($patron);
    
    $_SESSION['biometrico_msg'] = $resultado['mensaje'];
    header('Location: biometrico.php');
    exit;
}

if (isset($_SESSION['biometrico_msg'])) {
    $resultado_html = $_SESSION['biometrico_msg'];
    $mostrar_resultado = true;
    unset($_SESSION['biometrico_msg']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Autenticación Biométrica</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: radial-gradient(circle at 20% 30%, #0a0a1a, #1a0a2a);
            font-family: 'Courier New', 'Consolas', monospace;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            width: 100%;
            background: rgba(10, 10, 15, 0.95);
            border: 2px solid #00ccff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 0 60px rgba(0, 204, 255, 0.15), inset 0 0 60px rgba(0, 204, 255, 0.05);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Patrón de fondo con código morse */
        .container::before {
            content: "..... ..--- .---- ....- ..... -.... ----. ---.. ..... ";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            font-family: 'Courier New', monospace;
            font-size: 18;
            color: rgba(251, 255, 0, 0.06);
            letter-spacing: 4px;
            word-spacing: 8px;
            line-height: 16px;
            white-space: pre-wrap;
            padding: 15px;
            pointer-events: none;
            z-index: 0;
            transform: rotate(-2deg) scale(1.1);
            user-select: none;
        }

        .header {
            border-bottom: 2px solid #00ccff;
            padding-bottom: 15px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .header h1 {
            color: #00ccff;
            font-size: 2rem;
            letter-spacing: 6px;
            text-transform: uppercase;
            animation: glitch 2s infinite;
        }

        @keyframes glitch {
            0% { text-shadow: -2px 0 #ff00aa, 2px 0 #00ccff; }
            25% { text-shadow: 2px 0 #ff00aa, -2px 0 #00ccff; }
            50% { text-shadow: -1px 0 #ff00aa, 1px 0 #00ccff; }
            75% { text-shadow: 1px 0 #ff00aa, -1px 0 #00ccff; }
            100% { text-shadow: -2px 0 #ff00aa, 2px 0 #00ccff; }
        }

        .header h1 span {
            color: #fff;
            background: #00ccff;
            padding: 2px 12px;
            border-radius: 4px;
            text-shadow: none;
        }

        .header p {
            color: #88aacc;
            margin-top: 10px;
            font-size: 0.85rem;
            letter-spacing: 1px;
            font-style: italic;
        }

        .top-secret {
            background: #000;
            border: 1px dashed #00ccff;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            position: relative;
            z-index: 1;
        }

        .top-secret span {
            color: #00ccff;
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 0.8rem;
        }

        /* PISTA DE ESPIONAJE */
        .pista-espia {
            background: rgba(0, 10, 20, 0.6);
            border-left: 3px solid #ff00aa;
            padding: 10px 15px;
            margin: 15px 0;
            text-align: left;
            font-size: 0.75rem;
            color: #4a6a7a;
            border-radius: 0 8px 8px 0;
            font-style: italic;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .pista-espia .clasificado {
            color: #ff00aa;
            font-weight: bold;
            font-style: normal;
        }

        .pista-espia .destacar {
            color: #88ccdd;
            font-weight: bold;
            font-style: normal;
        }

        /* Código Morse - visible pero con aspecto decorativo */
        .morse-decorativo {
            color: rgba(247, 247, 247, 0.94);
            font-size: 0.65rem;
            letter-spacing: 3px;
            text-align: center;
            padding: 8px 0;
            font-family: 'Courier New', monospace;
            border-top: 1px solid rgba(115, 255, 0, 0.94);
            border-bottom: 1px solid rgba(229, 255, 0, 0.94);
            margin: 10px 0;
            position: relative;
            z-index: 1;
            word-break: break-all;
            user-select: none;
            background: rgba(0, 204, 255, 0.03);
            border-radius: 4px;
        }

        .morse-decorativo .morse-char {
            display: inline-block;
            animation: morsePulse 3s ease-in-out infinite;
            margin: 0 1px;
        }

        .morse-decorativo .morse-char:nth-child(odd) {
            animation-delay: 0.5s;
        }

        .morse-decorativo .morse-space {
            display: inline-block;
            width: 8px;
        }

        @keyframes morsePulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.9; }
        }

        .patron-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            max-width: 280px;
            margin: 25px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            border: 1px solid #1a3a4a;
            box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }

        .punto {
            aspect-ratio: 1;
            border-radius: 50%;
            border: 3px solid #2a4a5a;
            background: rgba(10, 20, 30, 0.8);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: bold;
            color: #3a5a6a;
            user-select: none;
            position: relative;
        }

        .punto:hover:not(.seleccionado):not(.activo) {
            border-color: #00ccff;
            background: rgba(0, 204, 255, 0.1);
            transform: scale(1.08);
            box-shadow: 0 0 25px rgba(0, 204, 255, 0.2);
        }

        .punto.seleccionado {
            border-color: #00ccff;
            background: rgba(0, 204, 255, 0.2);
            box-shadow: 0 0 30px rgba(0, 204, 255, 0.3);
            color: #00ccff;
        }

        .punto.activo {
            border-color: #00ff88;
            background: rgba(0, 255, 136, 0.2);
            box-shadow: 0 0 40px rgba(0, 255, 136, 0.4);
            color: #00ff88;
            animation: pulse 0.8s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .punto .orden {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #00ccff;
            color: #000;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .secuencia {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #1a3a4a;
            border-radius: 10px;
            padding: 12px;
            margin: 15px 0;
            color: #00ff88;
            font-size: 1rem;
            min-height: 45px;
            font-family: monospace;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .secuencia .label {
            color: #4a7a8a;
            margin-right: 8px;
        }

        .secuencia .vacia {
            color: #4a5a6a;
        }

        .secuencia .numero {
            background: rgba(0, 204, 255, 0.15);
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid rgba(0, 204, 255, 0.2);
        }

        .secuencia .flecha {
            color: #2a5a6a;
        }

        .intentos-info {
            color: #6a8a9a;
            font-size: 0.9rem;
            margin: 10px 0;
            padding: 8px;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 8px;
            position: relative;
            z-index: 1;
        }

        .intentos-info span {
            color: #00ccff;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .botones-accion {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 10px 0;
            position: relative;
            z-index: 1;
        }

        .btn-limpiar {
            background: linear-gradient(95deg, #2a1a1a, #4a2a2a);
            border: 1px solid #ff4444;
            color: #ff6666;
            padding: 10px 20px;
            font-family: monospace;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 48%;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .btn-limpiar:hover {
            background: linear-gradient(95deg, #4a2a2a, #6a3a3a);
            box-shadow: 0 0 20px rgba(255, 68, 68, 0.3);
            transform: scale(1.02);
        }

        .btn-verificar {
            background: linear-gradient(95deg, #0a1a2a, #1a3a5a);
            border: 2px solid #00ccff;
            color: #00ccff;
            padding: 14px 30px;
            font-family: monospace;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
            border-radius: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            position: relative;
            z-index: 1;
        }

        .btn-verificar:hover:not(:disabled) {
            background: linear-gradient(95deg, #1a3a5a, #2a5a7a);
            box-shadow: 0 0 30px rgba(0, 204, 255, 0.3);
            transform: scale(1.02);
            letter-spacing: 4px;
        }

        .btn-verificar:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
        }

        .btn-volver {
            background: linear-gradient(95deg, #1a1a2a, #2a2a3a);
            border: 1px solid #4a5a6a;
            color: #8a9aaa;
            padding: 12px 30px;
            font-family: monospace;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 15px;
            border-radius: 10px;
            position: relative;
            z-index: 1;
        }

        .btn-volver:hover {
            background: linear-gradient(95deg, #2a2a3a, #3a3a4a);
            color: #fff;
            border-color: #6a7a8a;
            transform: scale(1.02);
        }

        .resultado {
            margin-top: 20px;
            padding: 18px;
            text-align: center;
            border-radius: 12px;
            font-size: 1rem;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .resultado.exito {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff88;
            color: #00ff88;
            display: block;
            animation: glowSuccess 1.5s infinite;
        }

        @keyframes glowSuccess {
            0%, 100% { box-shadow: 0 0 20px rgba(0, 255, 136, 0.1); }
            50% { box-shadow: 0 0 40px rgba(0, 255, 136, 0.3); }
        }

        .resultado.error {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff4444;
            color: #ff6666;
            display: block;
        }

        .resultado.bloqueado {
            background: rgba(255, 136, 0, 0.1);
            border: 2px solid #ff8800;
            color: #ff8800;
            display: block;
            animation: glowBlock 1s infinite;
        }

        @keyframes glowBlock {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 136, 0, 0.1); }
            50% { box-shadow: 0 0 40px rgba(255, 136, 0, 0.3); }
        }

        .resultado.oculto {
            display: none;
        }

        .temporizador {
            background: rgba(26, 10, 0, 0.9);
            border: 2px solid #ff8800;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            color: #ff8800;
            animation: glowBlock 1s infinite;
            position: relative;
            z-index: 1;
        }

        .temporizador .titulo {
            font-size: 0.9rem;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .temporizador .tiempo {
            font-size: 3rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        .temporizador .segundos {
            font-size: 1rem;
            color: #cc7744;
        }

        .temporizador .barra {
            width: 100%;
            height: 8px;
            background: #1a1a0a;
            border-radius: 4px;
            margin-top: 12px;
            overflow: hidden;
            border: 1px solid #442200;
        }

        .temporizador .barra .progreso {
            height: 100%;
            background: linear-gradient(90deg, #ff8800, #ff4400);
            border-radius: 4px;
            transition: width 1s linear;
        }

        footer {
            text-align: center;
            padding: 15px;
            border-top: 1px solid #1a2a3a;
            font-size: 10px;
            color: #334455;
            margin-top: 20px;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 500px) {
            .container { padding: 20px; }
            .header h1 { font-size: 1.3rem; letter-spacing: 3px; }
            .patron-grid { gap: 10px; padding: 15px; max-width: 240px; }
            .punto { font-size: 0.7rem; }
            .btn-verificar { font-size: 0.8rem; padding: 12px; }
            .pista-espia { font-size: 0.65rem; padding: 8px 12px; }
            .morse-decorativo { font-size: 0.5rem; letter-spacing: 2px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔐 <span>BIOMÉTRICO</span></h1>
        <p>⚡ Autenticación de patrón de desbloqueo ⚡</p>
    </div>

    <div class="top-secret">
        <span>🔒 SISTEMA DE SEGURIDAD NIVEL 8 🔒</span>
    </div>

    <!-- PISTA DE ESPIONAJE -->
    <div class="pista-espia">
        <span class="clasificado">🔎 INTELIGENCIA CLASIFICADA:</span><br>
        El objetivo habla una lengua distinta, nadie entendía lo que decía y su secreto se mantuvo a salvo hasta hoy.
    </div>

    <!-- Código Morse - visible como elemento decorativo -->
    <div class="morse-decorativo">
        <span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span>
        <span class="morse-space"></span>
        <span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">−</span><span class="morse-char">−</span><span class="morse-char">−</span>
        <span class="morse-space"></span>
        <span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">−</span><span class="morse-char">−</span><span class="morse-char">−</span>
        <span class="morse-space"></span>
        <span class="morse-char">·</span><span class="morse-char">−</span><span class="morse-char">−</span><span class="morse-char">−</span><span class="morse-char">−</span>
        <span class="morse-space"></span>
        <span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span>
        <span class="morse-space"></span>
        <span class="morse-char">−</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span>
        <span class="morse-space"></span>
        <span class="morse-char">−</span><span class="morse-char">−</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span>
        <span class="morse-space"></span>
        <span class="morse-char">−</span><span class="morse-char">−</span><span class="morse-char">−</span><span class="morse-char">−</span><span class="morse-char">·</span>
        <span class="morse-space"></span>
        <span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span><span class="morse-char">·</span>
    </div>

    <?php if ($estado['bloqueado']): ?>
        <div class="temporizador" id="temporizador">
            <div class="titulo">⏰ SISTEMA BLOQUEADO</div>
            <div class="tiempo" id="tiempoRestante"><?php echo $estado['tiempo_restante']; ?></div>
            <div class="segundos">segundos para reiniciar</div>
            <div class="barra">
                <div class="progreso" id="barraProgreso" style="width:100%;"></div>
            </div>
        </div>
    <?php endif; ?>

    <div class="patron-grid" id="patronGrid">
        <?php for ($i = 1; $i <= 9; $i++): ?>
            <div class="punto" data-numero="<?php echo $i; ?>" id="punto<?php echo $i; ?>">
                <?php echo $i; ?>
            </div>
        <?php endfor; ?>
    </div>

    <div class="secuencia" id="secuencia">
        <span class="label">🔵 PATRÓN:</span>
        <span id="secuenciaTexto" class="vacia">Toca los puntos o usa teclado (1-9)</span>
    </div>

    <div class="intentos-info">
        🔄 Intentos: <span id="contadorIntentos"><?php echo $estado['intentos']; ?></span>/3
        <?php if ($estado['bloqueado']): ?>
            <span style="color:#ff8800; margin-left:10px;">⛔ BLOQUEADO</span>
        <?php endif; ?>
    </div>

    <form method="POST" action="" id="formPatron">
        <input type="hidden" name="patron" id="patronInput" value="">
        <div class="botones-accion">
            <button type="button" class="btn-limpiar" id="btnLimpiar">🗑️ LIMPIAR</button>
            <button type="button" class="btn-limpiar" id="btnDeshacer" style="border-color:#ff8800; color:#ff8800; background:linear-gradient(95deg, #2a1a0a, #4a2a0a);">↩️ DESHACER</button>
        </div>
        <button type="submit" class="btn-verificar" id="btnVerificar" <?php echo $estado['bloqueado'] ? 'disabled' : ''; ?>>
            🔐 VERIFICAR PATRÓN
        </button>
    </form>

    <button class="btn-volver" onclick="window.location.href='../index.php'">← VOLVER AL HACKATHON</button>

    <?php if ($mostrar_resultado): ?>
        <div id="resultado" class="resultado 
            <?php echo strpos($resultado_html, 'ACCESO BIOMÉTRICO CONCEDIDO') !== false ? 'exito' : 
                (strpos($resultado_html, 'BLOQUEADO') !== false ? 'bloqueado' : 'error'); ?>">
            <?php echo $resultado_html; ?>
        </div>
    <?php endif; ?>

    <footer>
        <div style="text-align:center; margin-top:10px;">
            <img src="../../img/cyt.png" alt="Logo Unidad de Ciencia y Tecnología" style="width:90px; height:auto; opacity:0.85;">
        </div>
        🔐 Autenticación Biométrica • Hackathon Carabobo 2026
    </footer>
</div>

<script>
console.clear();
console.log('%c🔐 SISTEMA BIOMÉTRICO ACTIVADO', 'color: #00ccff; font-size: 20px; font-weight: bold;');
console.log('%c🚫 No encontrarás la flag aquí', 'color: #ff4444; font-size: 16px;');
console.log('%c💀 3 intentos y el sistema se bloquea 15 segundos', 'color: #ff8800; font-size: 14px;');

// ============================================================
// LÓGICA DEL PATRÓN BIOMÉTRICO - PERMITE REPETIR NÚMEROS
// ============================================================

let secuencia = [];
const puntos = document.querySelectorAll('.punto');
const secuenciaTexto = document.getElementById('secuenciaTexto');
const patronInput = document.getElementById('patronInput');
const btnLimpiar = document.getElementById('btnLimpiar');
const btnDeshacer = document.getElementById('btnDeshacer');
const btnVerificar = document.getElementById('btnVerificar');
const contadorIntentos = document.getElementById('contadorIntentos');

function actualizarSecuencia() {
    if (secuencia.length === 0) {
        secuenciaTexto.innerHTML = '<span class="vacia">Toca los puntos o usa teclado (1-9)</span>';
        secuenciaTexto.style.color = '#4a5a6a';
    } else {
        let html = '';
        secuencia.forEach((num, index) => {
            if (index > 0) html += ' <span class="flecha">→</span> ';
            html += '<span class="numero">' + num + '</span>';
        });
        secuenciaTexto.innerHTML = html;
        secuenciaTexto.style.color = '#00ff88';
    }
    patronInput.value = secuencia.join('-');
    
    puntos.forEach(p => {
        p.classList.remove('seleccionado', 'activo');
        const num = parseInt(p.dataset.numero);
        let ultimoIndex = -1;
        for (let i = secuencia.length - 1; i >= 0; i--) {
            if (secuencia[i] === num) {
                ultimoIndex = i;
                break;
            }
        }
        
        if (ultimoIndex !== -1) {
            if (ultimoIndex === secuencia.length - 1) {
                p.classList.add('activo');
            } else {
                p.classList.add('seleccionado');
            }
            p.textContent = num;
            let orden = p.querySelector('.orden');
            if (!orden) {
                orden = document.createElement('span');
                orden.className = 'orden';
                p.appendChild(orden);
            }
            let count = 0;
            for (let i = 0; i <= ultimoIndex; i++) {
                if (secuencia[i] === num) count++;
            }
            orden.textContent = count;
        } else {
            p.textContent = num;
            const orden = p.querySelector('.orden');
            if (orden) orden.remove();
        }
    });
}

function agregarPunto(numero) {
    if (btnVerificar.disabled) return;
    if (secuencia.length >= 20) return;
    secuencia.push(numero);
    actualizarSecuencia();
}

function limpiarSecuencia() {
    secuencia = [];
    actualizarSecuencia();
}

function deshacerPunto() {
    if (secuencia.length > 0) {
        secuencia.pop();
        actualizarSecuencia();
    }
}

puntos.forEach(punto => {
    punto.addEventListener('click', function() {
        const numero = parseInt(this.dataset.numero);
        agregarPunto(numero);
    });
    
    punto.addEventListener('touchstart', function(e) {
        e.preventDefault();
        const numero = parseInt(this.dataset.numero);
        agregarPunto(numero);
    });
});

btnLimpiar.addEventListener('click', limpiarSecuencia);
btnDeshacer.addEventListener('click', deshacerPunto);

document.addEventListener('keydown', function(e) {
    if (e.key >= '1' && e.key <= '9') {
        if (btnVerificar.disabled) return;
        agregarPunto(parseInt(e.key));
    }
    if (e.key === 'Backspace' || e.key === 'Delete') {
        deshacerPunto();
    }
    if (e.key === 'Escape' || e.key === 'c' || e.key === 'C') {
        limpiarSecuencia();
    }
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('formPatron').submit();
    }
});

<?php if ($estado['bloqueado']): ?>
let tiempoRestante = <?php echo $estado['tiempo_restante']; ?>;
const tiempoElemento = document.getElementById('tiempoRestante');
const barraProgreso = document.getElementById('barraProgreso');

const intervalo = setInterval(function() {
    tiempoRestante--;
    if (tiempoElemento) {
        tiempoElemento.textContent = tiempoRestante;
    }
    if (barraProgreso) {
        const porcentaje = (tiempoRestante / 15) * 100;
        barraProgreso.style.width = porcentaje + '%';
    }
    
    if (tiempoRestante <= 0) {
        clearInterval(intervalo);
        location.reload();
    }
}, 1000);
<?php endif; ?>

setInterval(function() {
    fetch('../obtener_estado_biometrico.php')
        .then(response => response.json())
        .then(data => {
            if (data.intentos !== undefined && contadorIntentos) {
                contadorIntentos.textContent = data.intentos;
            }
            if (data.bloqueado) {
                if (btnVerificar) btnVerificar.disabled = true;
            } else {
                if (btnVerificar) btnVerificar.disabled = false;
            }
        })
        .catch(error => console.log('Error al actualizar contador'));
}, 3000);
</script>
</body>
</html>