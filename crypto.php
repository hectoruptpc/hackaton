<?php
// ============================================================
// crypto.php - Desafío de Encriptación Épica para Hackathon
// ============================================================

// ------------------------------------------------------------
// >>> ¡PEGA AQUÍ TU TEXTO ENCRIPTADO LARGUÍSIMO! <<<
// ------------------------------------------------------------
$secret_encrypted_text = "Vm0wd2QyVkZOVWRXV0doVFYwZG9XRll3Wkc5WFJsbDNXa2M1VjFadGVGWlZNbmhQVmpKS1NHVkdiR0ZXVjJoeVZtcEJlRmRIVmtsaVJtUk9ZV3RhU1ZadGNFZFpWMDE0V2toV2FWSnRVbkJXTUZwSFRURmFkRTFVVWxSTmJFcElWbTAxUzJGc1NuVlJhemxXWWxob1YxcFZXbUZrUlRGVlZXeHdWMkpJUWxsV1ZFa3hWREZzVjFOdVVsWmlSa3BvVm1wT1UyRkdiSEZTYlVacVRWWmFlVmRyV2xOVWJGcFpVV3BXVjFJemFHaFhWbHBhWlZaT2NtRkdXbWxTTW1oWFZtMTBWMlF5VW5OVmJsSnNVakJhY1ZSV1pGTk5SbFowWlVoa1YwMXJWalpWVjNoelZqRmFSbUo2UWxwbGExcDZWbXBHVDJSV1VuTmhSMnhYVFcxb2RsWnRNWGRVTVVWNFVsaG9WbUpyTlZSV2EyUTBWV3hhVjFWWVpGQlZWREE1";

// Si se envía una respuesta por POST
$user_answer = isset($_POST['answer']) ? trim($_POST['answer']) : '';
$feedback = '';
$feedback_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normalizamos para comparar (eliminar espacios extra, mayúsculas/minúsculas)
    $normalized_user = strtolower(preg_replace('/\s+/', '', $user_answer));
    $normalized_correct = strtolower(preg_replace('/\s+/', '', "H4CK4TH0N_3P1C_D3CRYPT10N")); // Cambia esto por tu flag real
    
    if ($normalized_user === $normalized_correct) {
        $feedback = "✅ ¡ACCESO CONCEDIDO! Has descifrado el mensaje. FLAG{CRYPTO_MASTER} ✅";
        $feedback_class = "success";
    } else {
        $feedback = "❌ ACCESO DENEGADO. Sigue intentando, el cifrado es largo pero no imposible. ❌";
        $feedback_class = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔓 CRYPTO CHALLENGE | Élite Hackathon 🔓</title>
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

        /* Grid de líneas estilo Matrix */
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

        /* Efecto glitch en el título */
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

        /* Tarjeta del texto cifrado */
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

        /* Formulario */
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

        /* Hint épico */
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

        /* Scrollbar personalizada */
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

    <!-- Caja con el texto cifrado (el enorme) -->
    <div class="crypto-box">
        <div class="crypto-label">
            <span>🔒 SECURE_ENCRYPTION_BLOB</span>
            <span>[ data_length: <?php echo number_format(strlen($secret_encrypted_text)); ?> bytes ]</span>
        </div>
        <pre id="encryptedData"><?php echo htmlspecialchars($secret_encrypted_text); ?></pre>
        
    </div>

    <!-- Formulario para descifrar -->
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
            <button type="button" onclick="history.back();">⬅️ REGRESAR</button>
        </div>
    </div>

    <div class="hint">
        💡 [ HINT ] 💡 La encriptación es larguísima pero reversible. 
        El formato de la flag es: <strong style="color:#ff44ee;">FLAG{...}</strong>
    </div>
    <footer>
        ⚡ HACK THE GIBSON ⚡ // NINGÚN SISTEMA ES SEGURO SI PERSISTES // LARGUÍSIMO ENCRYPT CHALL
    </footer>
</div>

<!-- Sonido mental hacker? solo vibe -->
<script>
    // pequeño efecto copilot: consola épica
    console.log("%c🔥 HACKATON MODE ACTIVATED 🔥", "color: #0ff; font-size: 18px; font-family: monospace;");
    console.log("%cEl texto encriptado es LARGUÍSIMO... pero tiene solución.", "color: #f0f;");
    // Auto ajuste visual si es muy largo
    const preBlock = document.getElementById('encryptedData');
    if(preBlock && preBlock.innerText.length > 3000) {
        preBlock.style.maxHeight = "400px";
    }
</script>
</body>
</html>