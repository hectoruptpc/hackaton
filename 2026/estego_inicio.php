<?php
session_start();
require_once 'conf/functions.php';

$resultado_html = '';
$mostrar_resultado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = $_POST['mensaje'];
    $resultado = verificarEsteganografia($mensaje);
    
    $resultado_html = $resultado['mensaje'];
    $mostrar_resultado = true;
}
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

        .info-panel input {
            width: 100%;
            background: #000;
            border: 1px solid #333;
            color: #0f0;
            padding: 10px;
            font-family: monospace;
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

        .btn-volver {
            background: linear-gradient(95deg, #1a1a2a, #2a2a3a);
            border: 1px solid #555;
            color: #aaa;
            padding: 12px 30px;
            font-family: monospace;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 15px;
        }

        .btn-volver:hover {
            background: linear-gradient(95deg, #2a2a3a, #3a3a4a);
            color: #fff;
            border-color: #777;
        }

        .resultado {
            margin-top: 20px;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
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

        .resultado.oculto {
            display: none;
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
            <form method="POST" action="">
                <input type="text" id="mensaje" name="mensaje" placeholder="Escribe aquí lo que encontraste...">
                <button type="submit" class="btn-verificar" style="margin-top:10px;">🔎 VERIFICAR Y REPORTAR</button>
            </form>
        </div>
        
        <!-- BOTÓN PARA VOLVER ATRÁS -->
        <button class="btn-volver" onclick="window.location.href='index.php'">← VOLVER AL HACKATHON</button>
        
        <?php if ($mostrar_resultado): ?>
            <div id="resultado" class="resultado <?php echo strpos($resultado_html, 'ACCESO CONCEDIDO') !== false ? 'exito' : 'error'; ?>">
                <?php echo $resultado_html; ?>
            </div>
        <?php endif; ?>
    </div>
    <footer>
        Gobierno de los Hacker | Todos los derechos reservados | Este documento es clasificado
    </footer>
</div>

<!-- 
    ============================================================
    MENSAJE DE CONSOLA - Para burlarse de los curiosos
    ============================================================
-->
<script>
console.clear();

console.log(
'%c' + 
`
╔══════════════════════════════════════════════════════════════════╗
║                                                                  ║
║   🎯  ¡B U E N   I N T E N T O ,   A G E N T E !  🎯           ║
║                                                                  ║
║   ┌──────────────────────────────────────────────────────────┐   ║
║   │                                                          │   ║
║   │   😂 ¿CREÍAS QUE AQUÍ IBAS A ENCONTRAR LA FLAG? 😂      │   ║
║   │                                                          │   ║
║   │   La flag está en el BACKEND, no en el frontend.        │   ║
║   │   Para obtenerla, descifra el mensaje correcto.         │   ║
║   │                                                          │   ║
║   │   😘  Suerte, agente. Lo vas a necesitar.  😘          │   ║
║   │                                                          │   ║
║   └──────────────────────────────────────────────────────────┘   ║
║                                                                  ║
║   💀  EL VERDADERO AGENTE USA SU CEREBRO, NO LA CONSOLA  💀    ║
║                                                                  ║
╚══════════════════════════════════════════════════════════════════╝
`,
'color: #ff00ff; font-size: 14px; font-weight: bold; font-family: monospace;'
);

console.log('%c🔍🔍🔍  ¿BUSCANDO PISTAS?  🔍🔍🔍', 'color: #00ffcc; font-size: 24px; font-weight: bold; background: #000; padding: 10px; border: 3px solid #ff00ff;');
console.log('%c🚫  NO HAY NADA QUE VER AQUÍ, REVISA LA IMAGEN  🚫', 'color: #ff0000; font-size: 20px; font-weight: bold; background: #1a0000; padding: 10px;');
console.log('%c🤣  TE CREÍSTE MUY LISTO, ¿VERDAD?  🤣', 'color: #ffff00; font-size: 18px; font-weight: bold; text-shadow: 2px 2px 4px #ff0000;');
console.log('%c😎  No pierdas tu tiempo aquí, analiza la imagen  😎', 'color: #00ff99; font-size: 20px; font-weight: bold; background: #002211; padding: 10px; border: 2px solid #00ff99;');
</script>
</body>
</html>