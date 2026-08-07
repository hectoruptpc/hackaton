<?php
/**
 * ============================================================
 * DESAFÍO #2: CRIPTOGRAFÍA ÉPICA
 * Unidad de Ciencia y Tecnología — UPTPC 2026
 * ============================================================
 * 
 * 🧠 CONOCIMIENTOS REQUERIDOS:
 * - Fundamentos de cifrado y codificación (Base64, sustitución, ROT13).
 * - Reconocimiento de secuencias y transformaciones de texto.
 * 
 * 🛠️ SOLUCIÓN OFICIAL:
 * 1. Analizar el bloque cifrado en la consola Cyberpunk.
 * 2. Aplicar decodificación por etapas de la cadena presentada.
 * 3. Enviar la bandera obtenida en el formulario de respuesta.
 * 
 * 🔀 ALTERNATIVAS DE RESOLUCIÓN:
 * - Método A: Usar herramientas online como CyberChef o dcode.fr.
 * - Método B: Script en Python con funciones de la librería base64.
 * ============================================================
 */

// Incluir la lógica de funciones
require_once 'conf/functions.php';

// Obtener el texto encriptado desde el backend
$secret_encrypted_text = getEncryptedText();

// Inicializar variables de feedback
$feedback = '';
$feedback_class = '';

// Procesar la respuesta del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_answer = isset($_POST['answer']) ? trim($_POST['answer']) : '';
    
    // Validar usando la función del backend
    $result = validateHackathonAnswer($user_answer);
    $feedback = $result['feedback'];
    $feedback_class = $result['class'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔓 CRYPTO CHALLENGE | Élite Hackathon 🔓</title>
    <link rel="stylesheet" href="conf/ia_avatar.css?v=2026_v14">
    <script src="conf/ia_avatar.js?v=2026_v14" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: radial-gradient(circle at 20% 30%, #0a0f1e, #03060c);
            font-family: 'Share Tech Mono', 'Courier New', monospace;
            color: #00ffcc;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
            overflow-x: auto;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 204, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 204, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes glitch {
            0% { text-shadow: -2px 0 red, 2px 0 blue; transform: skew(0.5deg); }
            25% { text-shadow: 2px 0 red, -2px 0 blue; transform: skew(-0.5deg); }
            50% { text-shadow: -1px 0 red, 1px 0 blue; transform: skew(0deg); }
            75% { text-shadow: 1px 0 red, -1px 0 blue; transform: skew(0.2deg); }
            100% { text-shadow: -2px 0 red, 2px 0 blue; transform: skew(-0.2deg); }
        }

        @keyframes flicker {
            0% { opacity: 0.8; }
            5% { opacity: 0.4; }
            10% { opacity: 0.9; }
            15% { opacity: 0.2; }
            20% { opacity: 1; }
            100% { opacity: 1; }
        }

        .container {
            background: rgba(8, 12, 25, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid #00ffcc;
            border-radius: 32px;
            padding: 2rem;
            box-shadow: 0 0 40px rgba(0, 255, 204, 0.2), inset 0 0 20px rgba(0, 255, 204, 0.05);
            z-index: 2;
            max-width: 1300px;
            width: 100%;
            transition: all 0.3s ease;
        }

        h1 {
            font-size: 3rem;
            text-align: center;
            letter-spacing: 6px;
            text-transform: uppercase;
            font-weight: bold;
            animation: glitch 1.8s infinite, flicker 3s infinite;
            margin-bottom: 1rem;
            word-break: break-word;
        }

        .sub {
            text-align: center;
            color: #88ffdd;
            border-bottom: 1px dashed #00ffcc88;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            letter-spacing: 2px;
        }

        .crypto-box {
            background: #010a12;
            border-left: 6px solid #ff00aa;
            border-right: 2px solid #00ffcc;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0 20px rgba(255, 0, 170, 0.3);
            transition: 0.2s;
        }

        .crypto-box:hover {
            border-left-color: #00ffcc;
            box-shadow: 0 0 25px #00ffcc55;
        }

        .crypto-label {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            font-weight: bold;
            color: #ff66cc;
        }

        .crypto-label span:first-child {
            font-size: 1.4rem;
            letter-spacing: 2px;
            background: #ff00aa20;
            padding: 0 10px;
            border-radius: 30px;
        }

        .crypto-label span:last-child {
            font-family: monospace;
            font-size: 0.75rem;
            background: #111;
            padding: 4px 10px;
            border-radius: 20px;
        }

        pre {
            background: #03080e;
            padding: 1.2rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #b0ffec;
            border-radius: 16px;
            border: 1px solid #00ffcc44;
            max-height: 320px;
            overflow-y: auto;
            line-height: 1.4;
        }

        .hack-form {
            background: #07121e80;
            border-radius: 28px;
            padding: 1.8rem;
            margin: 2rem 0;
            border: 1px solid #00ffcc66;
        }

        label {
            display: block;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        label i {
            font-size: 1.8rem;
            filter: drop-shadow(0 0 5px cyan);
        }

        input {
            width: 100%;
            padding: 1rem;
            background: #010a14;
            border: 2px solid #00ffcc;
            border-radius: 60px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 1rem;
            color: #00ffcc;
            outline: none;
            transition: 0.2s;
            margin-bottom: 1.5rem;
        }

        input:focus {
            border-color: #ff44ee;
            box-shadow: 0 0 15px #ff44ee;
            background: #021a24;
        }

        button {
            background: linear-gradient(95deg, #00ccaa, #0066ff);
            border: none;
            padding: 12px 32px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 1.3rem;
            font-weight: bold;
            text-transform: uppercase;
            color: #010101;
            border-radius: 60px;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 0 12px #00ffcc;
            letter-spacing: 2px;
        }

        button:hover {
            transform: scale(1.02);
            background: linear-gradient(95deg, #ff44ee, #00ccff);
            box-shadow: 0 0 22px #ff44ee;
            color: black;
        }

        .feedback {
            margin-top: 1rem;
            font-size: 1.3rem;
            text-align: center;
            padding: 16px;
            border-radius: 60px;
            font-weight: bold;
            backdrop-filter: blur(8px);
        }

        .feedback.success {
            background: #00ffcc22;
            border: 2px solid #00ffcc;
            color: #aaffee;
            text-shadow: 0 0 4px cyan;
        }

        .feedback.error {
            background: #ff115522;
            border: 2px solid #ff1155;
            color: #ff99bb;
        }

        .hint {
            text-align: center;
            font-size: 0.8rem;
            color: #77ffbb;
            background: #00000066;
            padding: 12px;
            border-radius: 24px;
            margin-top: 20px;
            border-top: 1px dashed cyan;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.7rem;
            color: #338877;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #021018;
        }
        ::-webkit-scrollbar-thumb {
            background: #00ffcc;
            border-radius: 10px;
        }

        @media (max-width: 700px) {
            .container { padding: 1rem; }
            h1 { font-size: 1.8rem; }
            .crypto-label span:first-child { font-size: 1rem; }
            button { font-size: 1rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>⚡ DESAFIO::ENCRIPTACION ⚡</h1>
    <div class="sub">[ 2do HACKATHON ] // NIVEL: SUPER ENCRIPTACIÓN</div>

    <div class="crypto-box">
        <div class="crypto-label">
            <span>🔒 SECURE_ENCRYPTION_BLOB</span>
            <span>[ data_length: <?php echo number_format(strlen($secret_encrypted_text)); ?> bytes ]</span>
        </div>
        <pre id="encryptedData"><?php echo htmlspecialchars($secret_encrypted_text); ?></pre>
    </div>

    <div class="hack-form">
        <form method="POST" action="">
            <label>
                <i>🗝️</i> INGRESA LA CLAVE / FLAG DESCIFRADA:
            </label>
            <input type="text" name="answer" placeholder="FLAG{...}" autocomplete="off" autofocus>
            <div style="display: flex; justify-content: center;">
                <button type="submit">💀 DESCIFRAR AHORA 💀</button>
            </div>
            <?php if ($feedback): ?>
                <div class="feedback <?php echo $feedback_class; ?>">
                    <?php echo htmlspecialchars($feedback); ?>
                </div>
            <?php endif; ?>
        </form>
        <div style="display: flex; justify-content: center; margin-top: 1rem;">
            <button type="button" onclick="window.location.href='index.php';">⬅️ REGRESAR</button>
        </div>
    </div>

    <div class="hint">
        💡 [ HINT ] 💡 La encriptación es larguísima pero reversible. 
        El formato de la flag es: <strong style="color:#ff44ee;">FLAG{...}</strong>
    </div>
    <footer>
         <div style="text-align:center; margin-top:10px;">
            <img src="../img/cyt.png" alt="Logo Unidad de Ciencia y Tecnología" style="width:90px; height:auto; opacity:0.85;">
        </div>
        ⚡ HACK THE GIBSON ⚡ // NINGÚN SISTEMA ES SEGURO SI PERSISTES // LARGUÍSIMO ENCRYPT CHALL
    </footer>
</div>

<!-- MENSAJE DE CONSOLA OFUSCADO -->
<script>
// Mensaje épico directamente en el script (no ofuscado para que funcione bien)
console.clear();

// Muro de texto enorme
console.log(
'%c' + 
`
╔══════════════════════════════════════════════════════════════════╗
║                                                                  ║
║   🎯  ¡B U E N   I N T E N T O ,   H A C K E R !  🎯           ║
║                                                                  ║
║   ┌──────────────────────────────────────────────────────────┐   ║
║   │                                                          │   ║
║   │   😂 ¿CREÍAS QUE AQUÍ IBAS A ENCONTRAR LA FLAG? 😂      │   ║
║   │                                                          │   ║
║   │   JAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJAJA   │   ║
║   │                                                          │   ║
║   │   La flag está en el BACKEND, no en el frontend.        │   ║
║   │   Sigue intentando, pero no por aquí, CAMPEÓN.          │   ║
║   │                                                          │   ║
║   │   😘  Saludos desde el equipo de Ciencia y Tecnología  😘 │   ║
║   │                                                          │   ║
║   └──────────────────────────────────────────────────────────┘   ║
║                                                                  ║
║   💀  EL VERDADERO HACKER USA SU CEREBRO, NO LA CONSOLA  💀    ║
║                                                                  ║
╚══════════════════════════════════════════════════════════════════╝
`,
'color: #ff00ff; font-size: 14px; font-weight: bold; font-family: monospace;'
);

// Mensajes adicionales con diferentes colores
console.log('%c🔍🔍🔍  ¿BUSCANDO PISTAS?  🔍🔍🔍', 'color: #00ffcc; font-size: 24px; font-weight: bold; background: #000; padding: 10px; border: 3px solid #ff00ff;');
console.log('%c🚫  NO HAY NADA QUE VER AQUÍ, SIGUE TU CAMINO  🚫', 'color: #ff0000; font-size: 20px; font-weight: bold; background: #1a0000; padding: 10px;');
console.log('%c🤣  TE CREÍSTE MUY LISTO, ¿VERDAD?  🤣', 'color: #ffff00; font-size: 18px; font-weight: bold; text-shadow: 2px 2px 4px #ff0000;');
console.log('%c⚠️  PISTA: La flag es "H4CK3R_M4ST3R_2026" (es broma, no es esa)  ⚠️', 'color: #ff8800; font-size: 16px; font-style: italic; background: #222; padding: 5px;');

// ASCII Art
console.log(
'%c  _   _   _   _   _   _   _   _   _   _   _   _   _  \n' +
' / \\ / \\ / \\ / \\ / \\ / \\ / \\ / \\ / \\ / \\ / \\ / \\ / \\ \n' +
'( N O   E N C O N T R A R Á S   N A D A )\n' +
' \\_/ \\_/ \\_/ \\_/ \\_/ \\_/ \\_/ \\_/ \\_/ \\_/ \\_/ \\_/ \\_/ \n',
'color: #00ff00; font-size: 18px; font-weight: bold;'
);

console.log('%c[SYSTEM] >> Acceso denegado. No eres bienvenido aquí. << [SYSTEM]', 'color: #ff4444; font-size: 16px; background: #330000; padding: 8px; border: 2px solid #ff0000;');

// Falso conteo de descarga
setTimeout(() => {
    console.log('%c⏳  DESCARGANDO FLAG... 0%  ⏳', 'color: #00ccff; font-size: 16px;');
}, 500);
setTimeout(() => {
    console.log('%c⏳  DESCARGANDO FLAG... 25%  ⏳', 'color: #00ccff; font-size: 16px;');
}, 1000);
setTimeout(() => {
    console.log('%c⏳  DESCARGANDO FLAG... 50%  ⏳', 'color: #00ccff; font-size: 16px;');
}, 1500);
setTimeout(() => {
    console.log('%c⏳  DESCARGANDO FLAG... 75%  ⏳', 'color: #00ccff; font-size: 16px;');
}, 2000);
setTimeout(() => {
    console.log('%c⛔  ERROR: FLAG PROTEGIDA POR EL BACKEND  ⛔', 'color: #ff0000; font-size: 22px; font-weight: bold; background: #400000; padding: 15px; border: 4px solid #ff0000;');
}, 2500);
setTimeout(() => {
    console.log('%c🏆  ¡SIGUE INTENTANDO, CAMPEÓN! LA VICTORIA ES PARA LOS PERSISTENTES  🏆', 'color: #ffd700; font-size: 20px; font-weight: bold; background: #1a1a00; padding: 10px; border: 3px solid #ffd700;');
}, 3000);

// Mensaje final
console.log('%c❌  Las variables están protegidas en el backend  ❌', 'color: #ff0066; font-size: 18px; font-weight: bold;');
console.log('%c😎  No pierdas tu tiempo aquí, ve a resolver el reto  😎', 'color: #00ff99; font-size: 20px; font-weight: bold; background: #002211; padding: 10px; border: 2px solid #00ff99;');

// Ajuste visual para textos muy largos
const preBlock = document.getElementById('encryptedData');
if(preBlock && preBlock.innerText.length > 3000) {
    preBlock.style.maxHeight = "400px";
}
</script>
</body>
</html>