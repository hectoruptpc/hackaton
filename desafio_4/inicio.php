<?php
session_start();
session_destroy();
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🔎 CASO 404 - Archivo Perdido</title>
    <style>
        body {
            background: #0a0c12;
            font-family: 'Courier New', monospace;
            color: #b0b8d0;
            text-align: center;
            padding-top: 60px;
        }
        .terminal {
            max-width: 700px;
            margin: auto;
            background: #11161f;
            border: 1px solid #2a6f6f;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(0,180,180,0.2);
            text-align: left;
        }
        .glitch {
            color: #0ff;
            font-weight: bold;
        }
        input, button {
            background: #0a0f18;
            border: 1px solid #2a8f8f;
            color: #0ff;
            padding: 10px 15px;
            font-family: monospace;
            margin-top: 20px;
        }
        button {
            cursor: pointer;
        }
        .pista {
            font-size: 13px;
            color: #6a7a8a;
            margin-top: 25px;
            border-top: 1px dashed #2a6f6f;
            padding-top: 15px;
        }
        .warning {
            color: #f66;
            font-size: 11px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="terminal">
    <span class="glitch">>_ SISTEMA DE ARCHIVOS CONFIDENCIALES_</span>
    <hr>
    <p>🔍 <strong>Expediente: CASO 404</strong></p>
    <p>Un informante dejó este mensaje antes de desaparecer:</p>
    <blockquote style="border-left: 3px solid #0ff; padding-left: 15px;">
        "El archivo no está donde debería.<br>
        Lo movieron a un lugar que <strong>no existe</strong>.<br>
        O tal vez sí, si sabes cómo buscarlo.<br>
        <br>
        <em>Empieza donde todo termina.</em>"
    </blockquote>
    
    <p>📁 <strong>¿Qué archivo buscas?</strong></p>
    <input type="text" id="buscar" placeholder="nombre_del_archivo">
    <button onclick="validar()">🔍 ACCEDER</button>
    
    <div class="pista">
        💡 Pista: El código que un servidor devuelve cuando algo no existe... es también un número.
    </div>
    <div class="warning">
        ⚠️ Cuidado: No todos los caminos son lo que parecen.
    </div>
</div>

<script>
    function validar() {
        let entrada = document.getElementById("buscar").value.trim().toLowerCase();
        if(entrada === "404" || entrada === "notfound" || entrada === "no_existe") {
            window.location.href = "nivel_1.php";
        } else if(entrada === "admin" || entrada === "root" || entrada === "backup" || entrada === "config") {
            window.location.href = "acceso_concedido.php";
        } else {
            alert("❌ Acceso denegado. El archivo no existe... o tal vez no se llama así.");
        }
    }
</script>
</body>
</html>