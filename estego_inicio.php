<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🔍 AGENTE: ESTEGANOGRAFÍA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f1a 100%);
            font-family: 'Courier New', 'Consolas', monospace;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .dossier {
            max-width: 900px;
            width: 100%;
            background: #0a0a0f;
            border: 1px solid #8b0000;
            border-radius: 5px;
            box-shadow: 0 0 30px rgba(139, 0, 0, 0.3);
            overflow: hidden;
        }

        .dossier-header {
            background: linear-gradient(90deg, #2a0000, #0a0000);
            padding: 20px;
            border-bottom: 2px solid #8b0000;
            text-align: center;
        }

        .dossier-header h1 {
            color: #8b0000;
            font-size: 1.8rem;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .dossier-header h1 span {
            color: #fff;
            background: #8b0000;
            padding: 2px 8px;
            border-radius: 3px;
        }

        .dossier-header p {
            color: #666;
            margin-top: 10px;
            font-size: 0.8rem;
        }

        .dossier-body {
            padding: 30px;
        }

        .top-secret {
            background: #000;
            border: 1px dashed #8b0000;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
        }

        .top-secret span {
            color: #8b0000;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .imagen-container {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .imagen-container img {
            border: 3px solid #333;
            border-radius: 8px;
            max-width: 100%;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        .marca-agua {
            position: absolute;
            bottom: 10px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            color: #444;
            font-size: 10px;
            padding: 2px 5px;
        }

        .info-panel {
            background: #0a0a0a;
            border-left: 3px solid #8b0000;
            padding: 15px;
            margin: 20px 0;
        }

        .info-panel label {
            color: #8b0000;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .info-panel textarea {
            width: 100%;
            background: #000;
            border: 1px solid #333;
            color: #0f0;
            padding: 10px;
            font-family: monospace;
            resize: vertical;
        }

        .btn-verificar {
            background: linear-gradient(95deg, #2a0000, #4a0000);
            border: 1px solid #8b0000;
            color: #fff;
            padding: 12px 30px;
            font-family: monospace;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-verificar:hover {
            background: linear-gradient(95deg, #4a0000, #6a0000);
            box-shadow: 0 0 15px #8b0000;
            letter-spacing: 2px;
        }

        .resultado {
            margin-top: 20px;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            display: none;
        }

        .resultado.exito {
            background: #0a2a0a;
            border: 1px solid #0f0;
            color: #0f0;
            display: block;
        }

        .resultado.error {
            background: #2a0a0a;
            border: 1px solid #f00;
            color: #f66;
            display: block;
        }

        .pistas {
            margin-top: 25px;
            padding: 15px;
            background: #0a0a0f;
            border: 1px solid #222;
            font-size: 12px;
        }

        .pistas summary {
            color: #8b0000;
            cursor: pointer;
        }

        .pistas code {
            background: #000;
            padding: 2px 5px;
            border-radius: 3px;
        }

        footer {
            text-align: center;
            padding: 15px;
            border-top: 1px solid #1a1a1a;
            font-size: 10px;
            color: #333;
        }
    </style>
</head>
<body>
<div class="dossier">
    <div class="dossier-header">
        <h1>🔍 AGENTE: <span>NEGRO</span></h1>
        <p>División de Inteligencia | Operación Esteganografía</p>
    </div>
    <div class="dossier-body">
        <div class="top-secret">
            <span>⚡ TOP SECRET // NIVEL 5 // SOLO PARA TUS OJOS ⚡</span>
        </div>

        <div class="imagen-container">
            <img src="hacker.png" alt="Evidencia fotográfica">
            <div class="marca-agua">EVIDENCIA #404-23</div>
        </div>

        <div class="info-panel">
            <label>📟 INFORME DE INTELIGENCIA:</label>
            <textarea rows="4" readonly style="color:#8b8b8b;">La imagen fue incautada de un servidor clandestino. Según nuestros analistas, contiene información oculta en los bits menos significativos (LSB). Descifra el mensaje y reporta.</textarea>
        </div>

        <div class="info-panel">
            <label>🔓 INGRESA EL MENSAJE DESCIFRADO:</label>
            <input type="text" id="mensaje" placeholder="Escribe aquí lo que encontraste..." style="width:100%; background:#000; border:1px solid #333; color:#0f0; padding:10px;">
        </div>

        <button class="btn-verificar" onclick="verificarMensaje()">🔎 VERIFICAR Y REPORTAR</button>
        <div id="resultado"></div>

        
    </div>
    <footer>
        Gobierno de los Hacker | Todos los derechos reservados | Este documento es clasificado
    </footer>
</div>

<script>
    function verificarMensaje() {
        let mensaje = document.getElementById("mensaje").value.trim().toLowerCase();
        let resultadoDiv = document.getElementById("resultado");
        
        // El mensaje correcto (lo que debe salir de la esteganografía)
        let correcto = "el ataque sera al amanecer";
        let correcto2 = "elataqueseraalamanecer";
        
        if (mensaje === correcto || mensaje === correcto2 || mensaje === "el ataque será al amanecer") {
            resultadoDiv.innerHTML = `
                <div style="background:#0a2a0a; border:1px solid #0f0; border-radius:5px; padding:15px; margin-top:15px;">
                    🎉 <strong>ACCESO CONCEDIDO</strong> 🎉<br><br>
                    <span style="color:#ff0;">🏆 FLAG{LSB_STEGANOGRAPHY_MASTER} 🏆</span><br><br>
                    Has completado la misión. Reporta este código a tu superior.
                </div>
            `;
            resultadoDiv.style.display = "block";
        } else {
            resultadoDiv.innerHTML = `
                <div style="background:#2a0a0a; border:1px solid #f00; border-radius:5px; padding:15px; margin-top:15px;">
                    ❌ <strong>ACCESO DENEGADO</strong> ❌<br><br>
                    Mensaje incorrecto. Revisa la imagen con herramientas de esteganografía.
                </div>
            `;
            resultadoDiv.style.display = "block";
        }
    }
</script>
</body>
</html>