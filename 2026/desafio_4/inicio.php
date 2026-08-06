<?php
/**
 * ============================================================
 * DESAFÍO #4: CONSOLA DE CONSULTA DE SERVIDOR INTERACTIVA (CLI)
 * Unidad de Ciencia y Tecnología — UPTPC 2026
 * ============================================================
 * 
 * 🧠 CONOCIMIENTOS REQUERIDOS:
 * - Comandos básicos de consola Unix/Linux (ls, cd, cat, sudo, pwd, whoami).
 * - Exploración de directorios del servidor web y elevación de privilegios (sudo).
 * 
 * 🛠️ SOLUCIÓN OFICIAL:
 * 1. Abrir la terminal interactiva.
 * 2. Explorar las carpetas con 'ls' y navegar con 'cd /etc/secret_vault' o 'cd home/admin'.
 * 3. Elevar privilegios con 'sudo su' o 'sudo cat /etc/secret_vault/flag_confidencial.txt'.
 * 4. Leer el archivo 'flag_confidencial.txt' para obtener la bandera: FLAG{SERVER_TERMINAL_MASTER_2026}.
 * ============================================================
 */

session_start();
require_once __DIR__ . '/../conf/functions.php';

// Fallback implementation in case functions.php does not define marcarDesafioCompletado
if (!function_exists('marcarDesafioCompletado')) {
    function marcarDesafioCompletado($equipo_id, $desafio_id)
    {
        // Minimal fallback: mark completion in session and append a simple log entry.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['desafio_' . intval($desafio_id) . '_completado'] = true;

        $logLine = sprintf("[%s] Equipo:%s Desafio:%s\n", date('Y-m-d H:i:s'), $equipo_id, $desafio_id);
        @file_put_contents(__DIR__ . '/../conf/desafios.log', $logLine, FILE_APPEND | LOCK_EX);

        return true;
    }
}

$mensaje_verificacion = "";

// Procesar el envío de la bandera en la página
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag_input'])) {
    $flag_ingresada = trim($_POST['flag_input']);
    if (strtoupper($flag_ingresada) === 'FLAG{SERVER_TERMINAL_MASTER}' || $flag_ingresada === 'FLAG_SERVER_TERMINAL_MASTER_2026') {
        if (isset($_SESSION['equipo_id'])) {
            marcarDesafioCompletado($_SESSION['equipo_id'], 4);
        }
        $mensaje_verificacion = '<div class="alert alert-success mt-3 text-center">🎉 ¡BANDERA CORRECTA! Has completado el Desafío #4. ¡Puntos acreditados a tu equipo!</div>';
    } else {
        $mensaje_verificacion = '<div class="alert alert-danger mt-3 text-center">❌ Bandera incorrecta. Sigue explorando el servidor.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💻 Desafío 4: Consola de Servidor Web - UPTPC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../conf/ia_avatar.css">
    <script src="../conf/ia_avatar.js" defer></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #090d16 linear-gradient(135deg, #070a10 0%, #0f172a 100%);
            font-family: 'Fira Code', monospace;
            color: #38bdf8;
            min-height: 100vh;
            padding: 30px 15px;
        }

        .container-cli {
            max-width: 950px;
            margin: 0 auto;
        }

        .terminal-window {
            background: rgba(15, 23, 42, 0.95);
            border: 2px solid #38bdf8;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(56, 189, 248, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .terminal-header-bar {
            background: #1e293b;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(56, 189, 248, 0.3);
        }

        .terminal-dots {
            display: flex;
            gap: 8px;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        .dot-red { background: #ef4444; }
        .dot-yellow { background: #eab308; }
        .dot-green { background: #22c55e; }

        .terminal-title {
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 700;
        }

        .terminal-body {
            padding: 20px;
            min-height: 420px;
            max-height: 520px;
            overflow-y: auto;
            color: #4ade80;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .terminal-output-line {
            margin-bottom: 8px;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .prompt-line {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .prompt-user {
            color: #38bdf8;
            font-weight: 700;
        }

        .prompt-user.root {
            color: #ef4444;
        }

        .terminal-input {
            background: transparent;
            border: none;
            outline: none;
            color: #f8fafc;
            font-family: 'Fira Code', monospace;
            font-size: 0.95rem;
            flex: 1;
        }

        .flag-box {
            background: rgba(34, 197, 94, 0.15);
            border: 2px solid #22c55e;
            color: #4ade80;
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .card-submit {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 25px;
            margin-top: 25px;
        }
    </style>
</head>
<body>

<div class="container-cli">

    <!-- Header del Desafío -->
    <div class="text-center mb-4">
        <h2 class="fw-bold text-info">💻 Desafío 4: Consola de Servidor Web (Linux CLI)</h2>
        <p class="text-secondary fs-6">Explora el sistema de archivos del servidor web, eleva privilegios con <code>sudo</code> y localiza la bandera <code>.txt</code> secreta.</p>
    </div>

    <!-- Ventana del Terminal Interactivo -->
    <div class="terminal-window">
        <div class="terminal-header-bar">
            <div class="terminal-dots">
                <div class="dot dot-red"></div>
                <div class="dot dot-yellow"></div>
                <div class="dot dot-green"></div>
            </div>
            <div class="terminal-title">SSH Session: invitado@uptpc-web-server (/var/www/html)</div>
            <div></div>
        </div>

        <div class="terminal-body" id="terminalBody">
            <div class="terminal-output-line text-info">
===================================================================
  🖥️ CONSOLA DE CONSULTA DE SERVIDOR WEB — UPTPC 2026 🖥️
===================================================================
  Escribe 'help' para ver la lista de comandos disponibles.
  Comandos soportados: ls, cd, cat, sudo, pwd, whoami, clear, help.
===================================================================
            </div>

            <!-- Contenedor dinámico de salidas -->
            <div id="outputHistory"></div>

            <!-- Entrada activa del prompt -->
            <div class="prompt-line">
                <span id="promptLabel" class="prompt-user">invitado@uptpc-server:<span id="currentPath">/var/www/html</span>$</span>
                <input type="text" id="cliInput" class="terminal-input" autofocus autocomplete="off" spellcheck="false">
            </div>
        </div>
    </div>

    <!-- Formulario para enviar la bandera -->
    <div class="card-submit">
        <h5 class="text-light fw-bold mb-3">🚩 Validar Bandera del Desafío #4</h5>
        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="flag_input" class="form-control bg-dark text-info border-secondary" placeholder="FLAG{...}" required>
                <button type="submit" class="btn btn-info fw-bold">🚀 Validar y Ganar Puntos</button>
            </div>
        </form>
        <?php echo $mensaje_verificacion; ?>
    </div>

    <div class="text-center mt-4">
        <a href="../index.php" class="btn btn-outline-secondary px-4 py-2" style="border-radius:12px;">🏠 Volver al Dashboard del Hackathon</a>
    </div>

</div>

<script>
// ============================================================
// SIMULADOR DEL SISTEMA DE ARCHIVOS VIRTUAL (LINUX UNIX CLI)
// ============================================================
class VirtualFileSystem {
    constructor() {
        this.currentUser = 'invitado'; // 'invitado' o 'root'
        this.currentDir = '/var/www/html';

        this.fs = {
            '/': {
                type: 'dir',
                owner: 'root',
                children: {
                    'var': { type: 'dir', owner: 'root' },
                    'home': { type: 'dir', owner: 'root' },
                    'etc': { type: 'dir', owner: 'root' },
                    'tmp': { type: 'dir', owner: 'root' }
                }
            },
            '/var': {
                type: 'dir',
                owner: 'root',
                children: {
                    'www': { type: 'dir', owner: 'root' },
                    'log': { type: 'dir', owner: 'root' }
                }
            },
            '/var/www': {
                type: 'dir',
                owner: 'root',
                children: {
                    'html': { type: 'dir', owner: 'www-data' }
                }
            },
            '/var/www/html': {
                type: 'dir',
                owner: 'www-data',
                children: {
                    'index.php': { type: 'file', content: '<?php echo "Servidor activo"; ?>', size: '342B' },
                    'readme.txt': { type: 'file', content: '📘 BIENVENIDO AL SERVIDOR DE LA UPTPC\nPara inspeccionar directorios restringidos del sistema usa el comando "sudo su" o eleva privilegios.', size: '180B' },
                    'public_assets': { type: 'dir', owner: 'www-data' }
                }
            },
            '/var/www/html/public_assets': {
                type: 'dir',
                owner: 'www-data',
                children: {
                    'logo.png': { type: 'file', content: '[Binary PNG Image Data]', size: '14.2KB' }
                }
            },
            '/home': {
                type: 'dir',
                owner: 'root',
                children: {
                    'invitado': { type: 'dir', owner: 'invitado' },
                    'admin': { type: 'dir', owner: 'admin' }
                }
            },
            '/home/invitado': {
                type: 'dir',
                owner: 'invitado',
                children: {
                    'notas.txt': { type: 'file', content: '📝 NOTA DE RECORDATORIO:\nEl administrador guardó el expediente confidencial en la carpeta /etc/secret_vault/', size: '120B' }
                }
            },
            '/home/admin': {
                type: 'dir',
                owner: 'admin',
                restricted: true,
                children: {
                    'server_config.conf': { type: 'file', content: 'PORT=8080\nHOST=127.0.0.1', size: '45B' }
                }
            },
            '/etc': {
                type: 'dir',
                owner: 'root',
                children: {
                    'hostname': { type: 'file', content: 'uptpc-sec-server-2026', size: '22B' },
                    'secret_vault': { type: 'dir', owner: 'root', restricted: true }
                }
            },
            '/etc/secret_vault': {
                type: 'dir',
                owner: 'root',
                restricted: true,
                children: {
                    'flag_confidencial.txt': { 
                        type: 'file', 
                        content: '🎉 ¡FELICIDADES HACKER DE LA UPTPC!\n' +
                                 '=======================================================\n' +
                                 'LA BANDERA DEL DESAFÍO #4 ES:\n' +
                                 'FLAG{SERVER_TERMINAL_MASTER_2026}\n' +
                                 '=======================================================\n' +
                                 'Copia y valida la bandera en el formulario inferior.', 
                        size: '280B' 
                    },
                    '.clave_oculta.txt': { type: 'file', content: 'FLAG{SERVER_TERMINAL_MASTER_2026}', size: '40B' }
                }
            },
            '/tmp': {
                type: 'dir',
                owner: 'root',
                children: {
                    'system.log': { type: 'file', content: '[2026-08-06 08:00:00] Server booted successfully.', size: '90B' }
                }
            }
        };
    }

    resolverRuta(pathStr) {
        if (!pathStr || pathStr === '.') return this.currentDir;
        if (pathStr === '~') return '/home/' + this.currentUser;

        let partes;
        if (pathStr.startsWith('/')) {
            partes = pathStr.split('/').filter(Boolean);
        } else {
            const actualPartes = this.currentDir.split('/').filter(Boolean);
            const relPartes = pathStr.split('/').filter(Boolean);
            partes = [...actualPartes];

            for (let p of relPartes) {
                if (p === '..') {
                    if (partes.length > 0) partes.pop();
                } else if (p !== '.') {
                    partes.push(p);
                }
            }
        }

        return '/' + partes.join('/');
    }

    ejecutarComando(cmdLine) {
        const lineTrim = cmdLine.trim();
        if (!lineTrim) return '';

        const args = lineTrim.split(/\s+/);
        let cmd = args[0].toLowerCase();

        // Soporte de prefijo sudo (ej. sudo ls, sudo cat, sudo su)
        let esSudo = false;
        if (cmd === 'sudo') {
            esSudo = true;
            args.shift();
            cmd = (args[0] || '').toLowerCase();
        }

        if (!cmd) {
            return 'Uso: sudo <comando> (ejemplo: sudo su, sudo ls, sudo cat <archivo>)';
        }

        switch (cmd) {
            case 'help':
                return `📌 COMANDOS DISPONIBLES:
  ls [-la]             : Listar archivos y directorios de la carpeta actual.
  cd <directorio>      : Cambiar de directorio (ej: cd /etc/secret_vault, cd ..).
  cat <archivo.txt>    : Leer y mostrar el contenido de un archivo .txt.
  <archivo.txt>        : Abrir directamente un archivo de texto por su nombre.
  sudo su / sudo -i    : Elevar privilegios a usuario root (Administrador).
  pwd                  : Mostrar la ruta del directorio actual.
  whoami               : Mostrar el usuario activo (invitado / root).
  clear                : Limpiar la pantalla de la terminal.`;

            case 'whoami':
                return esSudo ? 'root' : this.currentUser;

            case 'pwd':
                return this.currentDir;

            case 'clear':
            case 'cls':
                document.getElementById('outputHistory').innerHTML = '';
                return null;

            case 'sudo':
            case 'su':
                if (args[0] === 'su' || args[0] === '-i' || cmd === 'su') {
                    this.currentUser = 'root';
                    this.actualizarPrompt();
                    return '⚡ Privilegios elevados a usuario ROOT (Superusuario). Ahora tienes acceso total al servidor.';
                }
                return 'Uso: sudo su (para convertirse en usuario root)';

            case 'ls':
            case 'dir':
                return this.cmdLs(args, esSudo);

            case 'cd':
                return this.cmdCd(args[1] || '~', esSudo);

            case 'cat':
            case 'type':
                return this.cmdCat(args[1], esSudo);

            default:
                // Si el comando introducido es directamente el nombre de un archivo .txt
                if (cmd.endsWith('.txt') || cmd.endsWith('.conf') || cmd.endsWith('.log')) {
                    return this.cmdCat(cmd, esSudo);
                }
                return `bash: ${cmd}: comando no encontrado. Escribe 'help' para ver los comandos válidos.`;
        }
    }

    cmdLs(args, esSudo) {
        const mostrarOcultos = args.includes('-la') || args.includes('-a') || args.includes('-al');
        const targetDir = this.fs[this.currentDir];

        if (!targetDir || targetDir.type !== 'dir') {
            return 'ls: imposible acceder a la carpeta: No existe el directorio.';
        }

        if (targetDir.restricted && this.currentUser !== 'root' && !esSudo) {
            return 'ls: Permiso denegado. Se requieren privilegios de superusuario (usa sudo su o sudo ls).';
        }

        const items = targetDir.children || {};
        let output = [];

        if (mostrarOcultos) {
            output.push('drwxr-xr-x 2 root root 4096 .');
            output.push('drwxr-xr-x 4 root root 4096 ..');
        }

        for (let nombre in items) {
            const item = items[nombre];
            const esOculto = nombre.startsWith('.');

            if (esOculto && !mostrarOcultos) continue;

            if (item.type === 'dir') {
                const color = item.restricted ? '#ef4444' : '#38bdf8';
                output.push(`<span style="color:${color}; font-weight:bold;">📁 ${nombre}/</span>`);
            } else {
                output.push(`<span style="color:#4ade80;">📄 ${nombre} (${item.size})</span>`);
            }
        }

        return output.length > 0 ? output.join('  \n') : '(Directorio vacío)';
    }

    cmdCd(pathStr, esSudo) {
        const targetPath = this.resolverRuta(pathStr);
        const targetObj = this.fs[targetPath];

        if (!targetObj) {
            return `cd: ${pathStr}: No existe el archivo o el directorio.`;
        }

        if (targetObj.type !== 'dir') {
            return `cd: ${pathStr}: No es un directorio.`;
        }

        if (targetObj.restricted && this.currentUser !== 'root' && !esSudo) {
            return `cd: ${pathStr}: Permiso denegado. Se requieren privilegios de administrador (usa 'sudo su' para elevar acceso).`;
        }

        this.currentDir = targetPath;
        this.actualizarPrompt();
        return `Navegado a: ${this.currentDir}`;
    }

    cmdCat(fileStr, esSudo) {
        if (!fileStr) return 'Uso: cat <nombre_archivo.txt>';

        let targetPath;
        if (fileStr.includes('/')) {
            targetPath = this.resolverRuta(fileStr);
        } else {
            targetPath = this.currentDir === '/' ? '/' + fileStr : this.currentDir + '/' + fileStr;
        }

        // Buscar el archivo dentro del objeto del directorio actual o ruta completa
        const parentPath = targetPath.substring(0, targetPath.lastIndexOf('/')) || '/';
        const fileName = targetPath.substring(targetPath.lastIndexOf('/') + 1);

        const parentObj = this.fs[parentPath];
        if (!parentObj || !parentObj.children || !parentObj.children[fileName]) {
            return `cat: ${fileStr}: No existe el archivo o el directorio.`;
        }

        const fileObj = parentObj.children[fileName];
        if (fileObj.type === 'dir') {
            return `cat: ${fileStr}: Es un directorio. Usa 'cd ${fileStr}' para ingresar.`;
        }

        if (parentObj.restricted && this.currentUser !== 'root' && !esSudo) {
            return `cat: ${fileStr}: Permiso denegado. Requiere privilegios de root (usa sudo su o sudo cat).`;
        }

        // Si contiene la bandera, destacar visualmente
        if (fileObj.content.includes('FLAG{')) {
            return `<div class="flag-box">${fileObj.content}</div>`;
        }

        return fileObj.content;
    }

    actualizarPrompt() {
        const label = document.getElementById('promptLabel');
        const pathSpan = document.getElementById('currentPath');
        if (label && pathSpan) {
            const userClass = this.currentUser === 'root' ? 'prompt-user root' : 'prompt-user';
            const char = this.currentUser === 'root' ? '#' : '$';
            label.className = userClass;
            label.innerHTML = `${this.currentUser}@uptpc-server:<span id="currentPath">${this.currentDir}</span>${char}`;
        }
    }
}

// Inicializar el simulador CLI
const vfs = new VirtualFileSystem();
const cliInput = document.getElementById('cliInput');
const outputHistory = document.getElementById('outputHistory');
const terminalBody = document.getElementById('terminalBody');

cliInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const command = this.value;
        this.value = '';

        // Imprimir línea ingresada
        const promptText = document.getElementById('promptLabel').innerText;
        const lineDiv = document.createElement('div');
        lineDiv.className = 'terminal-output-line';
        lineDiv.innerHTML = `<span style="color:#38bdf8;">${promptText}</span> ${escapeHtml(command)}`;
        outputHistory.appendChild(lineDiv);

        // Ejecutar comando
        const res = vfs.ejecutarComando(command);
        if (res !== null) {
            const resDiv = document.createElement('div');
            resDiv.className = 'terminal-output-line';
            resDiv.innerHTML = res;
            outputHistory.appendChild(resDiv);
        }

        // Auto-scroll al final
        terminalBody.scrollTop = terminalBody.scrollHeight;
    }
});

function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
</script>

</body>
</html>