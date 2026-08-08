<?php
/**
 * ============================================================
 * DESAFIO #4: TERMINAL KALI LINUX — SSH al Servidor UPTPC
 * Unidad de Ciencia y Tecnologia — UPTPC 2026
 * ============================================================
 * SOLUCION OFICIAL:
 * 1. Desde la terminal Kali Linux emulada ejecutar:
 *    ssh invitado@server-hackaton.uptpc.edu.ve
 * 2. Explorar con ls, navegar con cd /etc/secret_vault
 * 3. Elevar privilegios: sudo su
 * 4. Leer: cat /etc/secret_vault/flag_confidencial.txt
 * ============================================================
 */

session_start();
require_once __DIR__ . '/../conf/functions.php';

if (!function_exists('marcarDesafioCompletado')) {
    function marcarDesafioCompletado($equipo_id, $desafio_id) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['desafio_' . intval($desafio_id) . '_completado'] = true;
        $logLine = sprintf("[%s] Equipo:%s Desafio:%s\n", date('Y-m-d H:i:s'), $equipo_id, $desafio_id);
        @file_put_contents(__DIR__ . '/../conf/desafios.log', $logLine, FILE_APPEND | LOCK_EX);
        return true;
    }
}

$mensaje_verificacion = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag_input'])) {
    $flag_ingresada = trim($_POST['flag_input']);
    $flag_clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $flag_ingresada));

    if (in_array($flag_clean, ['IABOSSFINAL', 'FLAGSERVERTERMINALMASTER2026', 'SERVERTERMINALMASTER2026'])) {
        if (isset($_SESSION['equipo_id'])) {
            marcarDesafioCompletado($_SESSION['equipo_id'], 4);
        }
        $mensaje_verificacion = '<div class="alert alert-success mt-3 text-center">&#127881; &iexcl;C&Oacute;DIGO DE SECTORES CORRECTO! Has obtenido la bandera: <strong style="font-size:1.15rem; color:#4ade80;">FLAG{SERVER_TERMINAL_MASTER_2026}</strong> y completado el Desaf&iacute;o #4.</div>';
    } else {
        $mensaje_verificacion = '<div class="alert alert-danger mt-3 text-center">&#10060; C&oacute;digo o bandera incorrecta. Explora el servidor para hallar los 3 sectores del c&oacute;digo.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Desafio 4: Terminal Kali Linux — UPTPC Hackathon 2026</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
<link rel="stylesheet" href="../conf/ia_avatar.css">
<script src="../conf/ia_avatar.js" defer></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#0a0d14;font-family:'Fira Code',monospace;color:#38bdf8;min-height:100vh;padding:28px 15px}
.container-cli{max-width:980px;margin:0 auto}
.challenge-header{text-align:center;margin-bottom:18px}
.challenge-header h2{font-size:1.4rem;font-weight:700;color:#7bf1a8;text-shadow:0 0 18px rgba(123,241,168,.35)}
.challenge-header p{color:#64748b;font-size:.88rem;margin-top:6px}
.ssh-instructions{background:rgba(15,23,42,.9);border:1px solid rgba(123,241,168,.25);border-radius:12px;padding:14px 20px;margin-bottom:18px;font-size:.82rem;color:#94a3b8;line-height:1.7}
.ssh-instructions strong{color:#7bf1a8}
.ssh-instructions code{background:rgba(123,241,168,.1);color:#7bf1a8;padding:1px 6px;border-radius:4px;font-size:.84rem}
.badge-kali{display:inline-block;background:#1e293b;border:1px solid #4ade80;color:#4ade80;padding:2px 10px;border-radius:20px;font-size:.74rem;margin-left:6px;vertical-align:middle}
.terminal-window{background:#0d1117;border:1.5px solid rgba(123,241,168,.4);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.7),0 0 30px rgba(123,241,168,.1);overflow:hidden}
.terminal-header-bar{background:#161b22;padding:9px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(123,241,168,.2);user-select:none}
.terminal-dots{display:flex;gap:7px}
.dot{width:13px;height:13px;border-radius:50%}
.dot-red{background:#ff5f57;box-shadow:0 0 6px #ff5f5788}
.dot-yellow{background:#febc2e;box-shadow:0 0 6px #febc2e88}
.dot-green{background:#28c840;box-shadow:0 0 6px #28c84088}
.terminal-title{font-size:.8rem;color:#58a6ff;font-weight:600;letter-spacing:.03em}
.terminal-phase-badge{font-size:.72rem;padding:2px 10px;border-radius:20px;font-weight:700;letter-spacing:.04em}
.badge-local{background:rgba(250,204,21,.15);color:#facc15;border:1px solid #facc1555}
.badge-remote{background:rgba(34,197,94,.15);color:#22c55e;border:1px solid #22c55e55}
.terminal-body{padding:18px 20px;min-height:460px;max-height:580px;overflow-y:auto;color:#cdd9e5;font-size:.875rem;line-height:1.55;scrollbar-width:thin;scrollbar-color:#30363d #0d1117}
.terminal-body::-webkit-scrollbar{width:6px}
.terminal-body::-webkit-scrollbar-track{background:#0d1117}
.terminal-body::-webkit-scrollbar-thumb{background:#30363d;border-radius:3px}
.terminal-output-line{margin-bottom:2px;white-space:pre-wrap;word-break:break-all}
.prompt-line{display:flex;align-items:flex-end;flex-wrap:nowrap;gap:0;margin-top:6px}
#promptLabel{white-space:pre;flex-shrink:0;line-height:1.55}
.terminal-input{background:transparent;border:none;outline:none;color:#f0f6fc;font-family:'Fira Code',monospace;font-size:.875rem;flex:1;min-width:0;caret-color:#7bf1a8;line-height:1.55}
.c-green{color:#4ade80}.c-blue{color:#58a6ff}.c-yellow{color:#facc15}.c-red{color:#f85149}.c-cyan{color:#22d3ee}.c-gray{color:#8b949e}.c-white{color:#f0f6fc}.c-kali{color:#7bf1a8}.c-orange{color:#fb923c}.c-purple{color:#a855f7}
.flag-box{background:rgba(34,197,94,.12);border:2px solid #22c55e;color:#4ade80;padding:14px 18px;border-radius:10px;margin:8px 0;text-align:center;font-size:1.05rem;font-weight:700;box-shadow:0 0 20px rgba(34,197,94,.25)}
.restricted-msg{background:rgba(248,81,73,.08);border-left:3px solid #f85149;color:#f85149;padding:6px 12px;border-radius:0 6px 6px 0;font-size:.85rem;margin:4px 0;line-height:1.5}
.card-submit{background:rgba(22,27,34,.85);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:24px;margin-top:22px;backdrop-filter:blur(8px)}
</style>
</head>
<body>
<div class="container-cli">

<div class="challenge-header">
  <h2>&#128187; Desaf&iacute;o #4 &mdash; Terminal Kali Linux &#8594; SSH al Servidor</h2>
  <p>Abre la terminal Kali Linux, con&eacute;ctate al servidor v&iacute;a SSH y explora el sistema de archivos para hallar la bandera oculta.</p>
</div>

<div class="ssh-instructions">
  <strong>&#128225; Ruta de Conectividad del Hackathon</strong><span class="badge-kali">&#128009; Kali Linux</span><br>
  Desde tu terminal Kali Linux emulada, ejecuta el siguiente comando para conectarte al servidor oficial del evento:<br><br>
  &nbsp;&nbsp;<code>ssh invitado@server-hackaton.uptpc.edu.ve</code><br><br>
  <strong>Pasos a seguir despu&eacute;s de conectarte:</strong><br>
  &nbsp;&nbsp;1. Explora el sistema con <code>ls</code> y <code>cd</code><br>
  &nbsp;&nbsp;2. Busca directorios restringidos<br>
  &nbsp;&nbsp;3. Eleva privilegios con <code>sudo su</code><br>
  &nbsp;&nbsp;4. Encuentra 3 codigos y lee la bandera oculta<br><br>
  <strong>&#9888;&#65039; Aviso:</strong> Esta terminal est&aacute; <strong>aislada</strong>. Solo puedes conectarte a <code>server-hackaton.uptpc.edu.ve</code>.
  Los comandos <code>rm</code> y <code>cp</code> est&aacute;n deshabilitados incluso como root (pol&iacute;tica del Hackathon).
</div>

<div class="terminal-window">
  <div class="terminal-header-bar">
    <div class="terminal-dots">
      <div class="dot dot-red"></div>
      <div class="dot dot-yellow"></div>
      <div class="dot dot-green"></div>
    </div>
    <div class="terminal-title" id="terminalTitle">&#128009; Terminal Kali Linux &mdash; Hackathon UPTPC 2026</div>
    <span class="terminal-phase-badge badge-local" id="phaseBadge">KALI LOCAL</span>
  </div>

  <div class="terminal-body" id="terminalBody">
    <div id="welcomeBanner">
      <div class="terminal-output-line"><span class="c-kali">   ██╗  ██╗ █████╗ ██╗     ██╗    ██╗     ██╗███╗   ██╗██╗   ██╗██╗  ██╗</span></div>
      <div class="terminal-output-line"><span class="c-kali">   ██║ ██╔╝██╔══██╗██║     ██║    ██║     ██║████╗  ██║██║   ██║╚██╗██╔╝</span></div>
      <div class="terminal-output-line"><span class="c-kali">   █████╔╝ ███████║██║     ██║    ██║     ██║██╔██╗ ██║██║   ██║ ╚███╔╝ </span></div>
      <div class="terminal-output-line"><span class="c-kali">   ██╔═██╗ ██╔══██║██║     ██║    ██║     ██║██║╚██╗██║██║   ██║ ██╔██╗ </span></div>
      <div class="terminal-output-line"><span class="c-kali">   ██║  ██╗██║  ██║███████╗██║    ███████╗██║██║ ╚████║╚██████╔╝██╔╝ ██╗</span></div>
      <div class="terminal-output-line"><span class="c-kali">   ╚═╝  ╚═╝╚═╝  ╚═╝╚══════╝╚═╝    ╚══════╝╚═╝╚═╝  ╚═══╝ ╚═════╝ ╚═╝  ╚═╝</span></div>
      <div class="terminal-output-line"><span class="c-gray">   ─────────────────────────────────────────────────────────────────────</span></div>
      <div class="terminal-output-line"><span class="c-yellow">   &#128009; Kali GNU/Linux Rolling &mdash; Hackathon &Eacute;tico UPTPC 2026</span></div>
      <div class="terminal-output-line"><span class="c-gray">   Escribe </span><span class="c-kali">screenfetch</span><span class="c-gray"> para info del sistema &nbsp;|&nbsp; </span><span class="c-kali">help</span><span class="c-gray"> para ver comandos</span></div>
      <div class="terminal-output-line"><span class="c-gray">   Para conectarte al servidor: &nbsp;</span><span class="c-green">ssh invitado@server-hackaton.uptpc.edu.ve</span></div>
      <div class="terminal-output-line"><span class="c-gray">   ─────────────────────────────────────────────────────────────────────</span></div>
      <div class="terminal-output-line"></div>
    </div>
    <div id="outputHistory"></div>
    <div class="prompt-line" id="promptLine">
      <span id="promptLabel"></span>
      <input type="text" id="cliInput" class="terminal-input" autofocus autocomplete="off" spellcheck="false">
    </div>
  </div>
</div>

<div class="card-submit">
  <h5 class="text-light fw-bold mb-3">&#127989; Validar Bandera del Desaf&iacute;o #4</h5>
  <form method="POST" action="">
    <div class="input-group">
      <input type="text" name="flag_input" class="form-control bg-dark text-info border-secondary" placeholder="FLAG{...}" required>
      <button type="submit" class="btn btn-success fw-bold">&#128640; Validar y Ganar Puntos</button>
    </div>
  </form>
  <?php echo $mensaje_verificacion; ?>
</div>

<div class="text-center mt-4">
  <a href="../index.php" class="btn btn-outline-secondary px-4 py-2" style="border-radius:12px;">&#127968; Volver al Dashboard del Hackathon</a>
</div>

</div><!-- /container-cli -->

<script>
// =============================================================
// EMULADOR TERMINAL KALI LINUX — HACKATHON UPTPC 2026
// Fase 1: Terminal Kali Linux local
// Fase 2: SSH -> server-hackaton.uptpc.edu.ve
// =============================================================

const SERVIDOR_SSH  = 'server-hackaton.uptpc.edu.ve';
const HOST_KALI     = 'kali';
const USER_LOCAL    = 'hacker';

const estado = {
  fase: 'local',
  usuario: USER_LOCAL,
  dirLocal: '~',
  dirSSH: '/var/www/html',
  esRoot: false,
  historial: [],
  histIdx: -1
};

// ── Sistema de archivos del servidor remoto ──────────────────
const serverFS = {
  '/': { type:'dir', owner:'root', children:{ var:'dir', home:'dir', etc:'dir', tmp:'dir', proc:'dir', usr:'dir', root:'dir' } },
  '/root': { type:'dir', owner:'root', restricted:true, children:{
    '.bashrc': { type:'file', size:'220B', owner:'root', content:'# .bashrc root\nexport PATH=$PATH:/sbin:/usr/sbin\nalias ll="ls -la"' }
  }},
  '/proc': { type:'dir', owner:'root', children:{
    'version': { type:'file', size:'96B', owner:'root', content:'Linux version 6.1.0-kali9-amd64 (kali@kali-builder) (gcc version 12.2.0)' },
    'cpuinfo': { type:'file', size:'4.2KB', owner:'root', content:'processor\t: 0\nvendor_id\t: GenuineIntel\nmodel name\t: Intel(R) Xeon(R) CPU E5-2620 @ 2.00GHz\ncpu MHz\t\t: 3000.000\ncache size\t: 15360 KB' },
    'meminfo': { type:'file', size:'1.2KB', owner:'root', content:'MemTotal:       16384000 kB\nMemFree:         9842000 kB\nMemAvailable:   12048000 kB\nSwapTotal:       4194304 kB\nSwapFree:        4194304 kB' }
  }},
  '/usr': { type:'dir', owner:'root', children:{ bin:'dir', share:'dir', lib:'dir' } },
  '/usr/bin': { type:'dir', owner:'root', children:{} },
  '/usr/share': { type:'dir', owner:'root', children:{} },
  '/usr/lib':   { type:'dir', owner:'root', children:{} },
  '/var': { type:'dir', owner:'root', children:{ www:'dir', log:'dir', run:'dir' } },
  '/var/log': { type:'dir', owner:'root', children:{
    'auth.log':  { type:'file', size:'12.4KB', owner:'root',
      content:'[2026-08-06 07:58:22] sshd[1024]: Accepted password for invitado from 192.168.1.42 port 54322 ssh2\n[2026-08-06 08:01:04] sudo[1105]: invitado : TTY=pts/0 ; PWD=/var/www/html ; USER=root ; COMMAND=/bin/bash\n[2026-08-06 08:03:17] sshd[1024]: Disconnected from user invitado 192.168.1.42 port 54322' },
    'syslog':    { type:'file', size:'8.1KB', owner:'root', restricted:true,
      content:'[2026-08-06 08:00:00] kernel: Linux server-hackaton 6.1.0-kali9-amd64 iniciado\n[2026-08-06 08:00:12] systemd[1]: Iniciado Apache2 HTTP Server.\n[2026-08-06 08:00:15] systemd[1]: Iniciado OpenSSH Daemon.\n[2026-08-06 08:00:42] apache2[612]: AH00558: Escuchando en puerto 80' },
    'apache2':   { type:'dir', owner:'root', children:{ 'access.log': { type:'file', size:'3.8KB', owner:'root', content:'192.168.1.42 - - [06/Aug/2026:08:02:01 +0000] "GET / HTTP/1.1" 200 4821\n192.168.1.42 - - [06/Aug/2026:08:02:45 +0000] "GET /index.php HTTP/1.1" 200 3240' } } }
  }},
  '/var/log/apache2': { type:'dir', owner:'root', children:{ 'access.log': { type:'file', size:'3.8KB', owner:'root', content:'192.168.1.42 - - [06/Aug/2026:08:02:01] "GET / HTTP/1.1" 200 4821' } }},
  '/var/run': { type:'dir', owner:'root', children:{} },
  '/var/www': { type:'dir', owner:'root', children:{ html:'dir' } },
  '/var/www/html': { type:'dir', owner:'www-data', children:{
    'index.php':     { type:'file', size:'2.1KB', owner:'www-data', content:'<?php\n// Portal web del Hackathon UPTPC 2026\necho "Bienvenido al servidor del Hackathon!";' },
    'readme.txt':    { type:'file', size:'150B', owner:'www-data', content:'BIENVENIDO AL SERVIDOR UPTPC — server-hackaton.uptpc.edu.ve\n\n[ CODIGO SECTOR 1 ]: IA_' },
    'public_assets': { type:'dir', owner:'www-data' }
  }},
  '/var/www/html/public_assets': { type:'dir', owner:'www-data', children:{
    'logo.png':   { type:'file', size:'28.4KB', owner:'www-data', content:'[Datos binarios PNG - imagen no legible en terminal]' },
    'styles.css': { type:'file', size:'6.2KB',  owner:'www-data', content:'/* Hoja de estilos del portal UPTPC */' }
  }},
  '/home': { type:'dir', owner:'root', children:{ invitado:'dir', admin:'dir' } },
  '/home/invitado': { type:'dir', owner:'invitado', children:{
    '.bashrc':   { type:'file', size:'220B', owner:'invitado', content:'# .bashrc — Configuracion de shell Bash\nexport PATH=$PATH:/usr/local/bin\nalias ll="ls -la"\nalias cls="clear"' },
    '.bash_history': { type:'file', size:'180B', owner:'invitado', content:'ls\ncat /var/www/html/readme.txt\ncat /home/invitado/notas.txt\nsudo su\ncat /etc/secret_vault/flag_confidencial.txt' },
    'notas.txt': { type:'file', size:'150B', owner:'invitado', content:'NOTAS DE AUDITORIA — invitado\n\n[ CODIGO SECTOR 2 ]: BOSS_' }
  }},
  '/home/admin': { type:'dir', owner:'admin', restricted:true, children:{
    'server_config.conf': { type:'file', size:'88B', owner:'admin', restricted:true, content:'PUERTO=8080\nHOST=127.0.0.1\nSSL=habilitado' },
    '.ssh': { type:'dir', owner:'admin', restricted:true, children:{} }
  }},
  '/home/admin/.ssh': { type:'dir', owner:'admin', restricted:true, children:{
    'authorized_keys': { type:'file', size:'572B', owner:'admin', restricted:true, content:'[Claves SSH del administrador — Acceso denegado]' }
  }},
  '/etc': { type:'dir', owner:'root', children:{
    'hostname':    { type:'file', size:'30B', owner:'root', content:'server-hackaton.uptpc.edu.ve' },
    'os-release':  { type:'file', size:'185B', owner:'root', content:'NAME="Debian GNU/Linux"\nVERSION_ID="12"\nID=debian\nPRETTY_NAME="Debian GNU/Linux 12 (bookworm)"\nHOME_URL="https://www.debian.org/"' },
    'passwd':      { type:'file', size:'1.2KB', owner:'root', content:'root:x:0:0:root:/root:/bin/bash\nwww-data:x:33:33:www-data:/var/www:/usr/sbin/nologin\ninvitado:x:1001:1001:Usuario Invitado,,,:/home/invitado:/bin/bash\nadmin:x:1002:1002:Administrador UPTPC,,,:/home/admin:/bin/bash' },
    'shadow':      { type:'file', size:'640B', owner:'root', restricted:true, content:'ACCESO DENEGADO — Archivo de contrasenas cifradas.\nSolo accesible con privilegios de superusuario (root).' },
    'hosts':       { type:'file', size:'120B', owner:'root', content:'127.0.0.1\tlocalhost\n10.10.0.1\tserver-hackaton.uptpc.edu.ve server-hackaton\n10.10.0.2\tgateway.uptpc.edu.ve' },
    'crontab':     { type:'file', size:'90B',  owner:'root', content:'# Tareas programadas del sistema\n*/5 * * * * root /usr/bin/backup.sh\n0 3 * * * root /usr/sbin/logrotate /etc/logrotate.conf' },
    'secret_vault': { type:'dir', owner:'root', restricted:true }
  }},
  '/etc/secret_vault': { type:'dir', owner:'root', restricted:true, children:{
    'flag_confidencial.txt': { type:'file', size:'180B', owner:'root', restricted:true,
      content:'BOVEDA CENTRAL DE PRIVILEGIOS DE ROOT\n\n[ CODIGO SECTOR 3 ]: FINAL' },
    '.clave_oculta': { type:'file', size:'20B', owner:'root', restricted:true, content:'FINAL' },
    'instrucciones_admin.txt': { type:'file', size:'130B', owner:'root', restricted:true,
      content:'INSTRUCCIONES INTERNAS — ADMIN\n\nCodigos de acceso registrados en el servidor. Unir los 3 sectores de clave.' }
  }},
  '/tmp': { type:'dir', owner:'root', children:{
    'system.log':  { type:'file', size:'128B', owner:'root', content:'[2026-08-06 08:00:00] Servidor arrancado correctamente.\n[2026-08-06 08:00:14] Apache2 escuchando en puerto 80/443.\n[2026-08-06 08:00:16] sshd escuchando en puerto 22.' },
    '.tmp_cache':  { type:'file', size:'12B',  owner:'root', content:'cache temporal interno' }
  }}
};

// ── DOM ──────────────────────────────────────────────────────
const cliInput      = document.getElementById('cliInput');
const outputHistory = document.getElementById('outputHistory');
const terminalBody  = document.getElementById('terminalBody');
const promptLabel   = document.getElementById('promptLabel');
const termTitle     = document.getElementById('terminalTitle');
const phaseBadge    = document.getElementById('phaseBadge');

function esc(t){ return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function addLine(html){ const d=document.createElement('div'); d.className='terminal-output-line'; d.innerHTML=html; outputHistory.appendChild(d); }
function addText(txt,cls='c-white'){ if(txt===null||txt===undefined) return; String(txt).split('\n').forEach(l=>addLine('<span class="'+cls+'">'+esc(l)+'</span>')); }
function scroll(){ terminalBody.scrollTop=terminalBody.scrollHeight; }

// ── Prompt ───────────────────────────────────────────────────
function updatePrompt(){
  if(estado.fase==='local'){
    promptLabel.innerHTML =
      '<span class="c-kali">\u250c\u2500\u2500(</span>'+
      '<span class="c-kali" style="font-weight:700;">'+esc(estado.usuario)+'</span>'+
      '<span style="color:#cdd9e5;">\u327f</span>'+
      '<span class="c-blue" style="font-weight:700;">'+HOST_KALI+'</span>'+
      '<span class="c-kali">)-[</span>'+
      '<span class="c-kali" style="font-weight:700;">'+esc(estado.dirLocal)+'</span>'+
      '<span class="c-kali">]\n\u2514\u2500</span>'+
      '<span class="c-white">$ </span>';
    termTitle.textContent='Kali Linux Terminal — Hackathon UPTPC 2026';
    phaseBadge.textContent='KALI LOCAL';
    phaseBadge.className='terminal-phase-badge badge-local';
  } else {
    const dir = estado.dirSSH;
    const shortDir = (dir==='/home/invitado'||dir==='/root') ? '~' : dir;
    if(estado.esRoot){
      promptLabel.innerHTML =
        '<span style="color:#f85149;font-weight:700;">root</span>'+
        '<span style="color:#8b949e;">@</span>'+
        '<span style="color:#facc15;font-weight:700;">server-hackaton</span>'+
        '<span style="color:#8b949e;">:</span>'+
        '<span style="color:#f85149;font-weight:700;">'+esc(shortDir)+'</span>'+
        '<span style="color:#f85149;font-weight:700;"># </span>';
    } else {
      promptLabel.innerHTML =
        '<span style="color:#22c55e;font-weight:700;">invitado</span>'+
        '<span style="color:#8b949e;">@</span>'+
        '<span style="color:#facc15;font-weight:700;">server-hackaton</span>'+
        '<span style="color:#8b949e;">:</span>'+
        '<span style="color:#22c55e;">'+esc(shortDir)+'</span>'+
        '<span style="color:#cdd9e5;">$ </span>';
    }
    termTitle.textContent='SSH: invitado@'+SERVIDOR_SSH;
    phaseBadge.textContent='SSH REMOTO';
    phaseBadge.className='terminal-phase-badge badge-remote';
  }
  scroll();
}

// ── Resolver ruta en servidor ─────────────────────────────────
function resolveSSH(p){
  if(!p||p==='.'){ return estado.dirSSH; }
  if(p==='~'){ return estado.esRoot?'/root':'/home/invitado'; }
  let parts;
  if(p.startsWith('/')){
    parts=p.split('/').filter(Boolean);
  } else {
    parts=estado.dirSSH.split('/').filter(Boolean);
    for(const seg of p.split('/').filter(Boolean)){
      if(seg==='..')  { if(parts.length>0) parts.pop(); }
      else if(seg!=='.') parts.push(seg);
    }
  }
  const r='/'+parts.join('/');
  return r=='//'?'/':r;
}

// ── Animacion conexion SSH ────────────────────────────────────
function animarSSH(host, cb){
  const msgs=[
    [300,'SSH: Resolviendo nombre de host '+host+'...','c-gray'],
    [700,'SSH: Conectando a '+host+':22...','c-gray'],
    [1100,'SSH: Intercambiando claves ECDH...','c-gray'],
    [1500,'The authenticity of host \''+host+'\' can\'t be established.','c-yellow'],
    [1700,'ECDSA key fingerprint is SHA256:uP7c4Xk2mNzR1qBvJL8eWoYtGsDpHnA3FkE9sZiUv0=','c-gray'],
    [2100,'Are you sure you want to continue connecting (yes/no)? yes','c-white'],
    [2500,'Warning: Permanently added \''+host+'\' (ECDSA) to the list of known hosts.','c-yellow'],
    [2900,'invitado@'+host+'\'s password: ','c-gray'],
    [3400,'',''],
    [3600,'Linux server-hackaton 6.1.0-kali9-amd64 #1 SMP PREEMPT_DYNAMIC Kali 6.1.15-2kali1 (2026-04-12)','c-gray'],
    [3700,'',''],
    [3800,'Ultimo inicio de sesion: Wed Aug  6 07:58:22 2026 desde 192.168.1.42','c-gray'],
    [3900,'','']
  ];
  msgs.forEach(([ms,txt,cls])=>{
    setTimeout(()=>{ if(txt) addText(txt,cls); else addLine(''); scroll(); },ms);
  });
  setTimeout(()=>{
    addLine('<span class="c-green">\u2554\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2557</span>');
    addLine('<span class="c-green">\u2551</span>  <span class="c-yellow">SERVIDOR UPTPC &mdash; HACKATHON ETICO 2026</span>                 <span class="c-green">\u2551</span>');
    addLine('<span class="c-green">\u2551</span>  <span class="c-gray">Hostname : server-hackaton.uptpc.edu.ve</span>              <span class="c-green">\u2551</span>');
    addLine('<span class="c-green">\u2551</span>  <span class="c-gray">Sistema  : Debian GNU/Linux 12 (bookworm)</span>             <span class="c-green">\u2551</span>');
    addLine('<span class="c-green">\u2551</span>  <span class="c-gray">Kernel   : 6.1.0-kali9-amd64</span>                         <span class="c-green">\u2551</span>');
    addLine('<span class="c-green">\u2551</span>  <span class="c-red">AVISO: Acceso restringido. Solo personal autorizado.</span>  <span class="c-green">\u2551</span>');
    addLine('<span class="c-green">\u255a\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u255d</span>');
    addLine('');
    estado.fase='ssh';
    estado.dirSSH='/var/www/html';
    estado.esRoot=false;
    updatePrompt();
    scroll();
    if(cb) cb();
  },4300);
}

// ── screenfetch LOCAL (Kali) ──────────────────────────────────
function screenfetchKali(){
  addLine('');
  const u=esc(estado.usuario);
  addLine('<span class="c-blue" style="font-weight:700;">    .............          </span><span class="c-kali" style="font-weight:700;">'+u+'</span><span class="c-gray">@</span><span class="c-blue" style="font-weight:700;">kali</span>');
  addLine('<span class="c-blue">  .´´´´´´´´´´´´´.         </span><span class="c-gray">────────────────────────────────────</span>');
  addLine('<span class="c-blue"> ´                 `.      </span><span class="c-white">SO:      </span><span class="c-kali">Kali GNU/Linux Rolling (2026.2)</span>');
  addLine('<span class="c-blue">´       ´ ´´´´      `.     </span><span class="c-white">Kernel:  </span><span class="c-green">6.6.9-amd64 #1 SMP PREEMPT_DYNAMIC</span>');
  addLine('<span class="c-blue">´      ´    ´ ´      `.    </span><span class="c-white">Tiempo:  </span><span class="c-green">0 dias, 0h 42m</span>');
  addLine('<span class="c-blue">´     ´  ´´´  ´       `.   </span><span class="c-white">Paquetes:</span><span class="c-green"> 2847 (dpkg)</span>');
  addLine('<span class="c-blue">´     ´ ´´´´´ ´        `.  </span><span class="c-white">Shell:   </span><span class="c-green">bash 5.2.15</span>');
  addLine('<span class="c-blue">´     ´ ´     ´    ´    `. </span><span class="c-white">Resol.:  </span><span class="c-green">1920x1080</span>');
  addLine('<span class="c-blue">´      ´      ´   ´      `.</span><span class="c-white">Entorno: </span><span class="c-green">XFCE 4.18</span>');
  addLine('<span class="c-blue">´       `.....´   ´      ´ </span><span class="c-white">Tema:    </span><span class="c-green">Kali-Dark [GTK3]</span>');
  addLine('<span class="c-blue"> `.              ´          </span><span class="c-white">Iconos:  </span><span class="c-green">Flat-Remix-Blue-Dark</span>');
  addLine('<span class="c-blue">   `..............          </span><span class="c-white">Terminal:</span><span class="c-green"> xfce4-terminal 1.0.4</span>');
  addLine('<span class="c-blue">                           </span><span class="c-white">CPU:     </span><span class="c-green">Intel Core i7-12700H (4) @ 4.700GHz</span>');
  addLine('<span class="c-blue">                           </span><span class="c-white">GPU:     </span><span class="c-green">NVIDIA GeForce RTX 3060 Mobile</span>');
  addLine('<span class="c-blue">                           </span><span class="c-white">Memoria: </span><span class="c-green">1.8GB / 15.5GB</span>');
  addLine('<span class="c-blue">                           </span><span class="c-white">IP:      </span><span class="c-green">192.168.1.42 (eth0)</span>');
  addLine('');
  addLine(
    '<span style="background:#ef4444;padding:1px 8px;">&nbsp;&nbsp;&nbsp;</span>'+
    '<span style="background:#f97316;padding:1px 8px;">&nbsp;&nbsp;&nbsp;</span>'+
    '<span style="background:#eab308;padding:1px 8px;">&nbsp;&nbsp;&nbsp;</span>'+
    '<span style="background:#22c55e;padding:1px 8px;">&nbsp;&nbsp;&nbsp;</span>'+
    '<span style="background:#38bdf8;padding:1px 8px;">&nbsp;&nbsp;&nbsp;</span>'+
    '<span style="background:#6366f1;padding:1px 8px;">&nbsp;&nbsp;&nbsp;</span>'+
    '<span style="background:#a855f7;padding:1px 8px;">&nbsp;&nbsp;&nbsp;</span>'
  );
  addLine('');
}

// ── COMANDOS LOCAL ────────────────────────────────────────────
function cmdLocal(raw){
  const args=raw.trim().split(/\s+/);
  const cmd=args[0].toLowerCase();

  switch(cmd){
    case 'help': case '--help':
      addLine('<span class="c-kali">\u250c\u2500 COMANDOS DISPONIBLES EN KALI LINUX \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2510</span>');
      addText('  screenfetch / neofetch       — Informacion del sistema Kali','c-green');
      addText('  ssh usuario@host             — Conectar al servidor via SSH','c-green');
      addText('  find / locate / grep         — Busqueda de archivos en el sistema','c-green');
      addText('  whoami / pwd / id            — Usuario y directorio actual','c-green');
      addText('  ls / ls -la                  — Listar archivos','c-green');
      addText('  cd <ruta>                    — Cambiar de directorio','c-green');
      addText('  uname -a / ifconfig / ip a   — Kernel y red','c-green');
      addText('  clear                        — Limpiar pantalla','c-green');
      addLine('<span class="c-kali">\u2514\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2518</span>');
      addLine('');
      addText('  Para conectarte al servidor del Hackathon:','c-yellow');
      addText('    ssh invitado@'+SERVIDOR_SSH,'c-green');
      break;
    case 'screenfetch': case 'neofetch': screenfetchKali(); break;
    case 'whoami': addText(estado.usuario,'c-kali'); break;
    case 'pwd': addText('/home/'+estado.usuario,'c-white'); break;
    case 'id': addText('uid=1000('+estado.usuario+') gid=1000('+estado.usuario+') grupos=1000('+estado.usuario+'),27(sudo),1000(kali)','c-white'); break;
    case 'uname':
      addText(args.includes('-a')||args.includes('-r')
        ?'Linux kali 6.6.9-amd64 #1 SMP PREEMPT_DYNAMIC Kali 6.6.9-1kali1 (2026-01-08) x86_64 GNU/Linux'
        :'Linux','c-white'); break;
    case 'ifconfig': case 'ip':
      addText('eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500','c-white');
      addText('      inet 192.168.1.42  netmask 255.255.255.0  broadcast 192.168.1.255','c-white');
      addText('      ether 08:00:27:4e:b4:82  txqueuelen 1000  (Ethernet)','c-gray');
      addLine('');
      addText('lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536','c-white');
      addText('    inet 127.0.0.1  netmask 255.0.0.0','c-white'); break;
    case 'ls': case 'dir': {
      const la=args.some(a=>/^-[a-zA-Z]*a/.test(a));
      if(la){
        addText('total 36','c-gray');
        addText('drwxr-xr-x  3 '+estado.usuario+' '+estado.usuario+' 4096 ago  6 08:00 .','c-white');
        addText('drwxr-xr-x 12 root root                4096 ago  5 18:00 ..','c-white');
        addText('-rw-r--r--  1 '+estado.usuario+' '+estado.usuario+'  220 ago  6 08:00 .bash_logout','c-gray');
        addText('-rw-r--r--  1 '+estado.usuario+' '+estado.usuario+' 3526 ago  6 08:00 .bashrc','c-gray');
        addText('-rw-r--r--  1 '+estado.usuario+' '+estado.usuario+'  807 ago  6 08:00 .profile','c-gray');
        addLine('<span class="c-blue" style="font-weight:700;">drwxr-xr-x  2 '+estado.usuario+' '+estado.usuario+' 4096 ago  6 08:00 Escritorio</span>');
      } else {
        addLine('<span class="c-blue" style="font-weight:700;">Escritorio</span>');
      } break;
    }
    case 'cd': {
      const d=args[1]||'~';
      estado.dirLocal=(d==='~'||d==='')?'~':(d==='..'?'~':d);
      updatePrompt(); break;
    }
    case 'clear': case 'cls': outputHistory.innerHTML=''; break;
    case 'ssh': {
      const dest=args[1]||'';
      if(!dest){ addText('uso: ssh usuario@host','c-red'); break; }
      const hp=dest.includes('@')?dest.split('@')[1]:dest;
      const up=dest.includes('@')?dest.split('@')[0]:'root';
      if(hp===SERVIDOR_SSH||hp==='server-hackaton'||hp==='server-hackaton.uptpc.edu.ve'){
        if(up!=='invitado'&&up!=='admin'&&up!=='root'){
          addText('ssh: '+esc(up)+'@'+SERVIDOR_SSH+': Permiso denegado (publickey,password).','c-red');
          addText("     Solo el usuario 'invitado' tiene acceso SSH en este servidor.",'c-yellow');
          break;
        }
        cliInput.disabled=true;
        animarSSH(SERVIDOR_SSH,()=>{ cliInput.disabled=false; cliInput.focus(); });
      } else {
        addLine('<div class="restricted-msg">CONEXION BLOQUEADA — Terminal Aislada del Hackathon<br>No es posible conectarse via SSH a <strong>'+esc(hp)+'</strong>.<br>Esta terminal solo permite conexiones al servidor oficial:<br><code>ssh invitado@'+SERVIDOR_SSH+'</code></div>');
      } break;
    }
    case 'ping': case 'curl': case 'wget': case 'nmap': {
      const th=args[1]||'';
      if(th.includes('uptpc')||th===SERVIDOR_SSH){
        if(cmd==='ping'){
          addText('PING '+SERVIDOR_SSH+' (10.10.0.1): 56 bytes de datos','c-gray');
          addText('64 bytes de 10.10.0.1: icmp_seq=0 ttl=64 tiempo=1.2 ms','c-green');
          addText('64 bytes de 10.10.0.1: icmp_seq=1 ttl=64 tiempo=0.9 ms','c-green');
          addText('--- '+SERVIDOR_SSH+' estadisticas ---','c-gray');
          addText('2 paquetes transmitidos, 2 recibidos, 0% perdida','c-green');
        } else {
          addText(cmd+': operacion permitida solo dentro del entorno del Hackathon.','c-yellow');
        }
      } else {
        addLine('<div class="restricted-msg">ACCESO DE RED BLOQUEADO<br>Esta terminal esta aislada. No se permite trafico de red externo al Hackathon.</div>');
      } break;
    }
    case 'rm': case 'cp': case 'mv':
      addLine('<div class="restricted-msg">COMANDO DESHABILITADO POR LOS ORGANIZADORES<br>El comando <strong>'+esc(cmd)+'</strong> esta inhabilitado en todos los niveles durante el Hackathon.<br>Esto incluye sesiones root. Politica de seguridad del evento.</div>'); break;
    case 'exit': case 'logout':
      addText('Esta terminal local no puede cerrarse durante el Hackathon.','c-yellow'); break;
    default:
      if(cmd){ addText('bash: '+esc(cmd)+': comando no encontrado','c-red'); addText("Escribe 'help' para ver los comandos disponibles.",'c-gray'); }
  }
}

// ── COMANDOS SSH (servidor remoto) ────────────────────────────
function cmdSSH(raw){
  const args=raw.trim().split(/\s+/);
  let cmd=args[0].toLowerCase();
  let esSudo=false;
  if(cmd==='sudo'){
    esSudo=true; args.shift();
    cmd=(args[0]||'').toLowerCase();
    if(!cmd){ addText('sudo: se requiere un comando. Ejemplo: sudo su','c-red'); return; }
  }

  // Comandos bloqueados
  if(['rm','cp','mv','dd','shred'].includes(cmd)){
    addLine('<div class="restricted-msg">COMANDO DESHABILITADO — Politica del Hackathon<br>El comando <strong>'+esc(cmd)+'</strong> esta inhabilitado en TODOS los niveles de privilegio.<br>Esto incluye sesiones root. Los comandos de escritura/eliminacion estan bloqueados por seguridad.</div>');
    return;
  }
  // Bloquear SSH desde servidor
  if(cmd==='ssh'){
    const d2=args[1]||'';
    const h2=d2.includes('@')?d2.split('@')[1]:d2;
    if(!h2||h2==='localhost'||h2==='127.0.0.1') addText('ssh: '+esc(h2||'localhost')+': conexion rechazada','c-red');
    else addLine('<div class="restricted-msg">SALTO SSH BLOQUEADO<br>Desde el servidor del Hackathon no es posible establecer conexiones SSH externas.<br>El entorno esta completamente aislado.</div>');
    return;
  }

  switch(cmd){
    case 'help':
      addLine('<span class="c-green">\u250c\u2500 COMANDOS EN EL SERVIDOR \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2510</span>');
      addText('  ls [-la] [-R] [ruta]  — Listar archivos (soporta -R recursivo)','c-green');
      addText('  find [ruta] [opciones]— Buscar archivos (find / -name "*.txt" 2>/dev/null)','c-green');
      addText('  locate <patron>       — Busqueda de archivos en el sistema','c-green');
      addText('  grep [-i] [-r] <pat>  — Filtrar o buscar texto en archivos','c-green');
      addText('  cat <archivo>         — Leer contenido de un archivo','c-green');
      addText('  cd <directorio>       — Cambiar directorio (cd .., cd /etc, cd ~)','c-green');
      addText('  pwd                   — Ruta actual','c-green');
      addText('  whoami / id           — Usuario activo','c-green');
      addText('  uname -a              — Informacion del sistema','c-green');
      addText('  ps aux                — Ver procesos del sistema','c-green');
      addText('  sudo su               — Elevar privilegios a root','c-yellow');
      addText('  sudo find ...         — Buscar archivos con privilegios root','c-yellow');
      addText('  sudo cat <archivo>    — Leer archivo con privilegios root','c-yellow');
      addText('  base64 -d <codigo>    — Decodificar fragmentos en Base64','c-green');
      addText('  screenfetch           — Info del servidor','c-green');
      addText('  exit / logout         — Cerrar sesion SSH','c-orange');
      addText('  clear                 — Limpiar pantalla','c-green');
      addLine('<span class="c-green">\u2514\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2518</span>');
      addLine('');
      addText('  DESHABILITADOS (politica del Hackathon): rm, cp, mv, dd','c-red');
      break;

    case 'whoami':
      addText(esSudo?'root':(estado.esRoot?'root':'invitado'),'c-green'); break;
    case 'id':
      if(estado.esRoot||esSudo) addText('uid=0(root) gid=0(root) grupos=0(root)','c-red');
      else addText('uid=1001(invitado) gid=1001(invitado) grupos=1001(invitado),27(sudo)','c-green'); break;
    case 'pwd': addText(estado.dirSSH,'c-white'); break;
    case 'uname':
      addText(args.includes('-a')||args.includes('-r')
        ?'Linux server-hackaton 6.1.0-kali9-amd64 #1 SMP PREEMPT_DYNAMIC Kali 6.1.15-2kali1 (2026-04-12) x86_64 GNU/Linux'
        :'Linux','c-white'); break;
    case 'hostname': addText(SERVIDOR_SSH,'c-white'); break;
    case 'ps': {
      addText('  PID TTY          TIME CMD','c-gray');
      addText(' 1024 pts/0    00:00:00 sshd','c-white');
      addText(' 1105 pts/0    00:00:00 bash','c-white');
      addText(' 1248 pts/0    00:00:00 ps','c-white');
      if(estado.esRoot||args.includes('aux')){
        addText('    1 ?        00:00:01 systemd','c-white');
        addText('  514 ?        00:00:00 sshd','c-white');
        addText('  612 ?        00:00:02 apache2','c-white');
        addText('  613 ?        00:00:00 apache2 <worker>','c-gray');
        addText('  614 ?        00:00:00 apache2 <worker>','c-gray');
      } break;
    }
    case 'base64': {
      let rawText = raw.replace(/^sudo\s+/i, '').replace(/^base64\s*/i, '').replace(/-d|-decode/gi, '').replace(/echo|\|/gi, '').trim();
      if (!rawText) rawText = 'RkxBR3tTRVJWRVJfVEVSTUlOQUxfTUFTVEVSXzIwMjZ9';
      try {
        const decoded = window.atob(rawText);
        if (decoded.includes('FLAG{')) {
          addLine('<div class="flag-box">FLAG REVELADA: ' + esc(decoded) + '</div>');
        } else {
          addText(decoded, 'c-green');
        }
      } catch(e) {
        addText('base64: error de sintaxis o formato no valido', 'c-red');
      }
      break;
    }
    case 'echo': {
      if (raw.includes('| base64')) {
        const match = raw.match(/([A-Za-z0-9+/=]{12,})/);
        if (match) {
          try {
            const decoded = window.atob(match[1]);
            if (decoded.includes('FLAG{')) {
              addLine('<div class="flag-box">FLAG REVELADA: ' + esc(decoded) + '</div>');
            } else {
              addText(decoded, 'c-green');
            }
            return;
          } catch(e) {}
        }
      }
      const strEcho = raw.replace(/^sudo\s+/i, '').replace(/^echo\s*/i, '').replace(/\|.*$/,'').trim();
      addText(strEcho, 'c-white');
      break;
    }
    case 'clear': case 'cls': outputHistory.innerHTML=''; break;
    case 'history':
      estado.historial.slice(-15).forEach((h,i)=>addText(String(i+1).padStart(4)+' '+h,'c-gray')); break;

    case 'su':
    case 'sudo': {
      const sub=args[0]?args[0].toLowerCase():'';
      if(sub==='su'||sub==='-i'||sub==='-s'||cmd==='su'||sub===''){
        estado.esRoot=true;
        estado.dirSSH='/root';
        updatePrompt();
        addLine('');
        addText('Privilegios elevados a ROOT — Superusuario del servidor.','c-red');
        addText('  Ahora tienes acceso completo al sistema de archivos.','c-yellow');
        addText('  NOTA: Los comandos rm, cp y mv siguen inhabilitados (politica del Hackathon).','c-orange');
        addLine('');
        addText('  Pista: explora /etc/secret_vault/','c-yellow');
      } else {
        addText('uso: sudo su  (para convertirse en root)','c-red');
      } break;
    }

    case 'cd': {
      const parg=args[1]||'~';
      const tgt=resolveSSH(parg);
      const obj=serverFS[tgt];
      if(!obj){ addText('bash: cd: '+esc(parg)+': No existe el archivo o directorio','c-red'); break; }
      if(obj.type!=='dir'){ addText('bash: cd: '+esc(parg)+': No es un directorio','c-red'); break; }
      if(obj.restricted&&!estado.esRoot&&!esSudo){
        addText('bash: cd: '+esc(parg)+': Permiso denegado','c-red');
        addText('  Necesitas privilegios de root. Usa: sudo su','c-yellow'); break;
      }
      estado.dirSSH=tgt;
      updatePrompt(); break;
    }

    case 'ls': {
      const la=args.some(a=>/^-[a-zA-Z]*a/.test(a));
      const ll=args.some(a=>/^-[a-zA-Z]*l/.test(a));
      const rutaArgs=args.slice(1).filter(a=>!a.startsWith('-'));
      const dirTarget=rutaArgs.length>0?resolveSSH(rutaArgs[0]):estado.dirSSH;
      const dObj=serverFS[dirTarget];
      if(!dObj||dObj.type!=='dir'){
        addText("ls: no se puede acceder a '"+esc(dirTarget)+"': No existe el archivo o directorio",'c-red'); break;
      }
      if(dObj.restricted&&!estado.esRoot&&!esSudo){
        addText("ls: no se puede abrir el directorio '"+esc(dirTarget)+"': Permiso denegado",'c-red');
        addText('  Usa: sudo su  para obtener privilegios de root','c-yellow'); break;
      }
      const items=dObj.children||{};
      const filas=[];
      if(la){
        const ow=dObj.owner||'root';
        if(ll){
          filas.push('<span style="color:#8b949e;">drwxr-xr-x</span>  2 '+ow+' '+ow+' 4096 ago  6 08:00 <span class="c-blue">.</span>');
          filas.push('<span style="color:#8b949e;">drwxr-xr-x</span>  4 root root 4096 ago  6 08:00 <span class="c-blue">..</span>');
        } else {
          filas.push('<span class="c-blue">.</span>  <span class="c-blue">..</span>');
        }
      }
      for(const nm in items){
        const it=items[nm];
        const io=typeof it==='string'?{type:it}:it;
        if(nm.startsWith('.')&&!la) continue;
        if(io.type==='dir'){
          const col=io.restricted?'#f85149':'#58a6ff';
          const perms=io.restricted?'drwx------':'drwxr-xr-x';
          const ow2=io.owner||'root';
          filas.push(ll
            ?'<span style="color:#8b949e;">'+perms+'</span>  2 '+ow2+' '+ow2+' 4096 ago  6 08:00 <span style="color:'+col+';font-weight:700;">'+esc(nm)+'</span>'
            :'<span style="color:'+col+';font-weight:700;">'+esc(nm)+'</span>');
        } else {
          const sz=(io.size||'0B').padStart(6);
          const ow2=io.owner||'root';
          const perms2=io.restricted?'-rw-------':'-rw-r--r--';
          const col2=nm.endsWith('.txt')||nm.endsWith('.conf')?'#4ade80':'#cdd9e5';
          const disp=nm.startsWith('.')?'<span style="color:#8b949e;">'+esc(nm)+'</span>':'<span style="color:'+col2+';">'+esc(nm)+'</span>';
          filas.push(ll
            ?'<span style="color:#8b949e;">'+perms2+'</span>  1 '+ow2+' '+ow2+' '+sz+' ago  6 08:00 '+disp
            :disp);
        }
      }
      if(filas.length===0){ addText('(directorio vacio)','c-gray'); }
      else if(ll){ filas.forEach(f=>addLine(f)); }
      else {
        for(let i=0;i<filas.length;i+=4){
          addLine(filas.slice(i,i+4).join('   '));
        }
      } break;
    }

    case 'cat': {
      const fa=args[1];
      if(!fa){ addText('uso: cat <nombre_archivo>','c-red'); break; }
      let fp=fa.includes('/')?resolveSSH(fa):(estado.dirSSH==='/'?'/'+fa:estado.dirSSH+'/'+fa);
      const pp=fp.substring(0,fp.lastIndexOf('/'))||'/';
      const fn=fp.substring(fp.lastIndexOf('/')+1);
      const po=serverFS[pp];
      if(!po||!po.children||!po.children[fn]){
        addText('cat: '+esc(fa)+': No existe el archivo o directorio','c-red'); break;
      }
      const fo=po.children[fn];
      const fi=typeof fo==='string'?{type:fo}:fo;
      if(fi.type==='dir'){ addText('cat: '+esc(fa)+': Es un directorio','c-red'); break; }
      const restr=fi.restricted||po.restricted;
      if(restr&&!estado.esRoot&&!esSudo){
        addText('cat: '+esc(fa)+': Permiso denegado','c-red');
        addText('  Usa: sudo su  para obtener privilegios de root','c-yellow');
        addText('  O bien: sudo cat '+esc(fa),'c-yellow'); break;
      }
      const cont=fi.content||'(archivo vacio)';
      if(cont.includes('FLAG{')){
        addLine('<div class="flag-box">'+esc(cont)+'</div>');
      } else {
        addText(cont,'c-white');
      } break;
    }

    case 'screenfetch': case 'neofetch': {
      addLine('');
      addText('       _,met$$$$$gg.           invitado@server-hackaton','c-red');
      addText('    ,g$$$$$$$$$$$$$$$P.         ────────────────────────────────────────','c-red');
      addText("  ,g$$P\"        \"\"\"Y$$.\"        SO:      Debian GNU/Linux 12 (bookworm)",'c-red');
      addText("  ,$$P'              `$$$.       Kernel:  6.1.0-kali9-amd64",'c-red');
      addText(" ',$$P       ,ggs.     `$$b:     Tiempo:  12 dias, 3:41",'c-red');
      addText(" `d$$'     ,$P\"'   .    $$$      Paquetes: 1847 (dpkg)",'c-red');
      addText("  $$P      d$'     ,    $$P      Shell:   bash 5.1.16",'c-red');
      addText("  $$:      $$.   -    ,d$$'      CPU:     Intel Xeon E5-2620 (8) @ 3.000GHz",'c-red');
      addText("  $$;      Y$b._   _,d$P'        Memoria: 3.2GB / 15.6GB",'c-red');
      addText("  Y$$.    `.`\"Y$$$$P\"'           IP:      10.10.0.1",'c-red');
      addText("  `$$b      \"-.__               Hostname: server-hackaton.uptpc.edu.ve",'c-red');
      addText("   `Y$$                          Rol:     Servidor Web / Hackathon UPTPC 2026",'c-red');
      addLine(''); break;
    }

    case 'exit': case 'logout':
      addLine('');
      addText('Cerrando sesion SSH...','c-yellow');
      addText('Conexion a '+SERVIDOR_SSH+' cerrada.','c-gray');
      addLine('');
      estado.fase='local';
      estado.esRoot=false;
      estado.dirLocal='~';
      updatePrompt(); break;

    default:
      if(cmd.endsWith('.txt')||cmd.endsWith('.conf')||cmd.endsWith('.log')||cmd.endsWith('.php')){
        cmdSSH('cat '+cmd); return;
      }
      if(cmd){ addText('bash: '+esc(cmd)+': comando no encontrado','c-red'); addText("Escribe 'help' para ver los comandos disponibles.",'c-gray'); }
  }
}

// ── SISTEMA DE BÚSQUEDA Y RECURSOS LINUX (find, locate, grep, ls -R) ──────────

function resolveLocal(p){
  if(!p||p==='.') return estado.dirLocal==='~'?'/home/hacker':estado.dirLocal;
  if(p==='~') return '/home/hacker';
  if(p.startsWith('/')) return p;
  return '/home/hacker/'+p.replace(/^\.\//,'');
}

const localFS = {
  '/': { type:'dir', children:{ home:'dir', etc:'dir', usr:'dir', var:'dir' } },
  '/home': { type:'dir', children:{ hacker:'dir' } },
  '/home/hacker': { type:'dir', children:{ Escritorio:'dir', '.bashrc':'file' } },
  '/home/hacker/Escritorio': { type:'dir', children:{ 'notas_kali.txt':'file' } }
};

const localFileContents = {
  '/home/hacker/Escritorio/notas_kali.txt': 'NOTAS DE KALI LINUX — HACKATHON UPTPC 2026\n\nPara buscar archivos en el servidor del Hackathon, primero conéctate vía SSH:\n  ssh invitado@server-hackaton.uptpc.edu.ve'
};

function collectFSItems(isSSH, startPath, isRoot, suppressErrors) {
  const fs = isSSH ? serverFS : localFS;
  const rootPath = isSSH ? resolveSSH(startPath) : resolveLocal(startPath);
  const items = [];
  const errors = [];

  function walk(currentPath) {
    const dirObj = fs[currentPath];
    if(!dirObj) return;

    if(dirObj.restricted && !isRoot && !estado.esRoot) {
      if(!suppressErrors) {
        errors.push(`find: '${currentPath}': Permiso denegado`);
      }
      return;
    }

    const children = dirObj.children || {};
    for(const name in children) {
      const fullPath = (currentPath === '/' ? '' : currentPath) + '/' + name;
      const childVal = children[name];

      if(fs[fullPath]) {
        const childDir = fs[fullPath];
        const isRestr = childDir.restricted && !isRoot && !estado.esRoot;
        items.push({
          path: fullPath,
          name: name,
          type: 'dir',
          owner: childDir.owner || 'root',
          restricted: !!childDir.restricted,
          content: '',
          size: '4096B'
        });
        if(isRestr) {
          if(!suppressErrors) {
            errors.push(`find: '${fullPath}': Permiso denegado`);
          }
        } else {
          walk(fullPath);
        }
      } else {
        const fileObj = typeof childVal === 'string' ? { type: childVal } : childVal;
        const isRestr = fileObj.restricted && !isRoot && !estado.esRoot;
        if(isRestr) {
          if(!suppressErrors) {
            errors.push(`find: '${fullPath}': Permiso denegado`);
          }
        } else {
          let cont = fileObj.content || (localFileContents[fullPath] || '');
          items.push({
            path: fullPath,
            name: name,
            type: fileObj.type || 'file',
            owner: fileObj.owner || 'root',
            restricted: !!fileObj.restricted,
            content: cont,
            size: fileObj.size || '100B'
          });
        }
      }
    }
  }

  const startObj = fs[rootPath];
  if(!startObj) {
    if(!suppressErrors) {
      errors.push(`find: '${startPath}': No existe el archivo o directorio`);
    }
  } else if(startObj.type === 'file') {
    const name = rootPath.substring(rootPath.lastIndexOf('/') + 1);
    items.push({
      path: rootPath,
      name: name,
      type: 'file',
      owner: startObj.owner || 'root',
      restricted: !!startObj.restricted,
      content: startObj.content || '',
      size: startObj.size || '100B'
    });
  } else {
    walk(rootPath);
  }

  return { items, errors };
}

function globToRegex(pattern, caseInsensitive = false) {
  let p = pattern
    .replace(/[.+^${}()|[\]\\]/g, '\\$&')
    .replace(/\*/g, '.*')
    .replace(/\?/g, '.');
  return new RegExp('^' + p + '$', caseInsensitive ? 'i' : '');
}

function execFind(rawArgs, isSSH, isRoot, suppressErrors) {
  let execCmd = null;
  let argsString = rawArgs;

  const execMatch = argsString.match(/-exec\s+(.+?)\s+(\\|;|\+)/i);
  if(execMatch) {
    execCmd = execMatch[1].trim();
    argsString = argsString.replace(/-exec\s+.+?(\\|;|\+)/gi, '');
  }

  const tokens = argsString.trim().split(/\s+/).filter(Boolean);

  const paths = [];
  let namePattern = null;
  let isCaseInsensitive = false;
  let typeFilter = null;

  for(let i = 0; i < tokens.length; i++) {
    const tok = tokens[i];
    if(tok === '-name' && i + 1 < tokens.length) {
      namePattern = tokens[++i].replace(/^["']|["']$/g, '');
    } else if(tok === '-iname' && i + 1 < tokens.length) {
      namePattern = tokens[++i].replace(/^["']|["']$/g, '');
      isCaseInsensitive = true;
    } else if(tok === '-type' && i + 1 < tokens.length) {
      typeFilter = tokens[++i].toLowerCase();
    } else if(!tok.startsWith('-')) {
      paths.push(tok);
    }
  }

  if(paths.length === 0) {
    paths.push(isSSH ? estado.dirSSH : estado.dirLocal);
  }

  const outputLines = [];

  paths.forEach(p => {
    const { items, errors } = collectFSItems(isSSH, p, isRoot, suppressErrors);
    if(errors.length > 0 && !suppressErrors) {
      outputLines.push(...errors);
    }

    let regex = namePattern ? globToRegex(namePattern, isCaseInsensitive) : null;

    items.forEach(it => {
      if(regex && !regex.test(it.name)) return;
      if(typeFilter === 'f' && it.type !== 'file') return;
      if(typeFilter === 'd' && it.type !== 'dir') return;

      if(execCmd) {
        const finalCmd = execCmd.replace(/\{\}/g, it.path);
        if(finalCmd.startsWith('cat')) {
          if(it.content) {
            outputLines.push(it.content);
          } else {
            outputLines.push(`cat: ${it.path}: Archivo vacio o sin acceso`);
          }
        } else if(finalCmd.startsWith('ls')) {
          const perms = it.type === 'dir' ? 'drwxr-xr-x' : '-rw-r--r--';
          outputLines.push(`${perms} 1 ${it.owner} ${it.owner} ${it.size} ago 6 08:00 ${it.path}`);
        } else {
          outputLines.push(`${it.path}`);
        }
      } else {
        outputLines.push(it.path);
      }
    });
  });

  if(!isSSH && outputLines.length > 0) {
    outputLines.push('<span class="c-yellow">Pista: Estás en Kali local. Para explorar el servidor del Hackathon:</span>');
    outputLines.push('<span class="c-green">  ssh invitado@server-hackaton.uptpc.edu.ve</span>');
  }

  return outputLines;
}

function execLocate(rawArgs, isSSH, isRoot, suppressErrors) {
  const query = rawArgs.trim().replace(/^["']|["']$/g, '');
  if(!query) return ['locate: patron de busqueda requerido'];

  const { items } = collectFSItems(isSSH, '/', isRoot, true);
  const regex = globToRegex(query.includes('*') ? query : `*${query}*`, true);

  const matched = items.filter(it => regex.test(it.path) || regex.test(it.name)).map(it => it.path);

  if(!isSSH && matched.length > 0) {
    matched.push('<span class="c-yellow">Pista: Estás en Kali local. Para explorar el servidor objetivo:</span>');
    matched.push('<span class="c-green">  ssh invitado@server-hackaton.uptpc.edu.ve</span>');
  }

  return matched.length > 0 ? matched : ['locate: no se encontraron coincidencias'];
}

function execGrep(rawArgs, inputLines, isSSH, isRoot, suppressErrors) {
  const tokens = rawArgs.trim().split(/\s+/).filter(Boolean);

  let isCaseInsensitive = false;
  let isRecursive = false;
  let invertMatch = false;
  let pattern = null;
  const targetFiles = [];

  for(let i = 0; i < tokens.length; i++) {
    const tok = tokens[i];
    if(tok.startsWith('-')) {
      if(tok.includes('i')) isCaseInsensitive = true;
      if(tok.includes('r') || tok.includes('R')) isRecursive = true;
      if(tok.includes('v')) invertMatch = true;
      if(tok === '-e' && i + 1 < tokens.length) {
        pattern = tokens[++i];
      }
    } else if(!pattern) {
      pattern = tok.replace(/^["']|["']$/g, '');
    } else {
      targetFiles.push(tok);
    }
  }

  if(!pattern) return ['grep: patron de busqueda requerido'];

  let regex;
  try {
    regex = new RegExp(pattern, isCaseInsensitive ? 'i' : '');
  } catch(e) {
    regex = new RegExp(pattern.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), isCaseInsensitive ? 'i' : '');
  }

  const outputLines = [];

  if(inputLines && Array.isArray(inputLines)) {
    inputLines.forEach(line => {
      const match = regex.test(line);
      if((match && !invertMatch) || (!match && invertMatch)) {
        outputLines.push(line);
      }
    });
  } else {
    const startPath = targetFiles[0] || (isSSH ? estado.dirSSH : estado.dirLocal);
    const { items } = collectFSItems(isSSH, startPath, isRoot, suppressErrors);

    items.forEach(it => {
      if(it.type === 'file' && it.content) {
        const lines = it.content.split('\n');
        lines.forEach(l => {
          const match = regex.test(l);
          if((match && !invertMatch) || (!match && invertMatch)) {
            outputLines.push(`${it.path}:${l}`);
          }
        });
      }
    });
  }

  return outputLines;
}

function execLsRecursive(rawArgs, isSSH, isRoot, suppressErrors) {
  const startPath = rawArgs.replace(/-R|--recursive/gi, '').trim() || '/';
  const { items, errors } = collectFSItems(isSSH, startPath, isRoot, suppressErrors);

  const outputLines = [];
  if(errors.length > 0 && !suppressErrors) {
    outputLines.push(...errors);
  }

  items.forEach(it => {
    outputLines.push(it.path);
  });

  return outputLines;
}

function executePipeline(rawCmd, isSSH) {
  let suppressErrors = false;
  let cleanCmd = rawCmd;

  if(/2>\s*\/dev\/null/i.test(cleanCmd)) {
    suppressErrors = true;
    cleanCmd = cleanCmd.replace(/2>\s*\/dev\/null/gi, '');
  }
  if(/>\s*\/dev\/null/i.test(cleanCmd)) {
    cleanCmd = cleanCmd.replace(/>\s*\/dev\/null/gi, '');
  }
  if(/2>&1/i.test(cleanCmd)) {
    cleanCmd = cleanCmd.replace(/2>&1/gi, '');
  }
  cleanCmd = cleanCmd.trim();

  let isRoot = estado.esRoot;
  if(cleanCmd.toLowerCase().startsWith('sudo ')) {
    isRoot = true;
    cleanCmd = cleanCmd.substring(5).trim();
  }

  const stages = cleanCmd.split('|').map(s => s.trim()).filter(Boolean);
  if(stages.length === 0) return;

  let currentLines = null;
  let handledByEngine = false;

  for(let i = 0; i < stages.length; i++) {
    const stageStr = stages[i];
    const tokens = stageStr.split(/\s+/);
    const mainCmd = tokens[0].toLowerCase();
    const argsStr = stageStr.substring(tokens[0].length).trim();

    if(mainCmd === 'find') {
      handledByEngine = true;
      currentLines = execFind(argsStr, isSSH, isRoot, suppressErrors);
    } else if(mainCmd === 'locate') {
      handledByEngine = true;
      currentLines = execLocate(argsStr, isSSH, isRoot, suppressErrors);
    } else if(mainCmd === 'grep') {
      handledByEngine = true;
      currentLines = execGrep(argsStr, currentLines, isSSH, isRoot, suppressErrors);
    } else if(mainCmd === 'ls' && (stageStr.includes('-R') || stageStr.includes('--recursive'))) {
      handledByEngine = true;
      currentLines = execLsRecursive(argsStr, isSSH, isRoot, suppressErrors);
    } else if(mainCmd === 'sort') {
      handledByEngine = true;
      if(currentLines && Array.isArray(currentLines)) {
        const reverse = argsStr.includes('-r');
        currentLines.sort((a, b) => reverse ? b.localeCompare(a) : a.localeCompare(b));
      }
    } else if(mainCmd === 'wc') {
      handledByEngine = true;
      if(currentLines && Array.isArray(currentLines)) {
        currentLines = [String(currentLines.length)];
      }
    } else if(mainCmd === 'head') {
      handledByEngine = true;
      if(currentLines && Array.isArray(currentLines)) {
        const countMatch = argsStr.match(/-n\s*(\d+)/);
        const count = countMatch ? parseInt(countMatch[1]) : 10;
        currentLines = currentLines.slice(0, count);
      }
    } else if(mainCmd === 'tail') {
      handledByEngine = true;
      if(currentLines && Array.isArray(currentLines)) {
        const countMatch = argsStr.match(/-n\s*(\d+)/);
        const count = countMatch ? parseInt(countMatch[1]) : 10;
        currentLines = currentLines.slice(-count);
      }
    } else if(mainCmd === 'whereis' || mainCmd === 'which') {
      handledByEngine = true;
      const target = argsStr.trim() || 'find';
      currentLines = [`/usr/bin/${target}`];
    } else {
      if(i === 0) {
        if(isSSH) {
          cmdSSH(isRoot && !stageStr.startsWith('sudo') ? 'sudo ' + stageStr : stageStr);
        } else {
          cmdLocal(stageStr);
        }
        return;
      }
    }
  }

  if(handledByEngine && currentLines && Array.isArray(currentLines)) {
    currentLines.forEach(line => {
      if(typeof line === 'string') {
        if(line.includes('FLAG{')) {
          addLine('<div class="flag-box">' + esc(line) + '</div>');
        } else if(line.startsWith('find:') || line.startsWith('grep:') || line.startsWith('ls:')) {
          addText(line, 'c-red');
        } else {
          addText(line, 'c-white');
        }
      }
    });
  }
}

// ── FUNCIÓN AUXILIAR: Encontrar prefijo común ─────────────────
function findCommonPrefix(strings) {
  if(!strings || strings.length === 0) return '';
  if(strings.length === 1) return strings[0];
  
  let prefix = strings[0];
  for(let i = 1; i < strings.length; i++){
    while(strings[i].indexOf(prefix) !== 0){
      prefix = prefix.slice(0, -1);
      if(prefix === '') return '';
    }
  }
  return prefix;
}

// ── Listener del input con TAB mejorado ────────────────────────
cliInput.addEventListener('keydown', function(e){
  // ── AUTOCOMPLETADO CON TAB MEJORADO ──────────────────────────
  if(e.key === 'Tab'){
    e.preventDefault();
    
    const inputValue = this.value;
    const cursorPos = this.selectionStart;
    const commandLine = inputValue.slice(0, cursorPos);
    const args = commandLine.split(/\s+/);
    const lastArg = args[args.length - 1] || '';
    const cmd = args[0] ? args[0].toLowerCase() : '';
    
    // Si estamos en modo SSH
    if(estado.fase === 'ssh'){
      if(cmd === 'cd'){
        const currentDir = estado.dirSSH;
        const dirObj = serverFS[currentDir];
        const children = dirObj ? dirObj.children || {} : {};
        
        const directories = Object.keys(children).filter(name => {
          const item = children[name];
          const isDir = typeof item === 'string' ? item === 'dir' : item.type === 'dir';
          const isRestricted = typeof item === 'string' ? false : !!item.restricted;
          if(isRestricted && !estado.esRoot) return false;
          return isDir && name !== '.' && name !== '..';
        });
        
        const prefix = lastArg || '';
        const matches = directories.filter(dir => dir.startsWith(prefix));
        
        if(matches.length === 1){
          const match = matches[0];
          const parts = inputValue.split(/\s+/);
          parts[parts.length - 1] = match;
          this.value = parts.join(' ') + (match.endsWith('/') ? '' : '/');
          this.selectionStart = this.selectionEnd = this.value.length;
        } else if(matches.length > 1){
          const commonPrefix = findCommonPrefix(matches);
          if(commonPrefix.length > prefix.length){
            const parts = inputValue.split(/\s+/);
            parts[parts.length - 1] = commonPrefix;
            this.value = parts.join(' ');
            this.selectionStart = this.selectionEnd = this.value.length;
          } else {
            addLine('');
            const columns = Math.ceil(matches.length / 4);
            for(let i = 0; i < matches.length; i += columns){
              const row = matches.slice(i, i + columns).map(d => 
                d.endsWith('/') ? d : d + '/'
              ).join('  ');
              addLine('<span class="c-gray">' + row + '</span>');
            }
            addLine(promptLabel.innerHTML + '<span class="c-white">' + esc(inputValue) + '</span>');
            scroll();
          }
        }
        return;
      }
      
      if(['ls', 'cat', 'less', 'more', 'head', 'tail', 'find', 'locate', 'grep'].includes(cmd)){
        const currentDir = estado.dirSSH;
        const dirObj = serverFS[currentDir];
        const children = dirObj ? dirObj.children || {} : {};
        
        const items = Object.keys(children).filter(name => {
          const item = children[name];
          const isRestricted = typeof item === 'string' ? false : !!item.restricted;
          if(isRestricted && !estado.esRoot) return false;
          return name !== '.' && name !== '..';
        });
        
        const prefix = lastArg || '';
        const matches = items.filter(item => item.startsWith(prefix));
        
        if(matches.length === 1){
          const match = matches[0];
          const item = children[match];
          const isDir = typeof item === 'string' ? item === 'dir' : item.type === 'dir';
          const parts = inputValue.split(/\s+/);
          parts[parts.length - 1] = match;
          this.value = parts.join(' ') + (isDir ? '/' : '');
          this.selectionStart = this.selectionEnd = this.value.length;
        } else if(matches.length > 1){
          const commonPrefix = findCommonPrefix(matches);
          if(commonPrefix.length > prefix.length){
            const parts = inputValue.split(/\s+/);
            parts[parts.length - 1] = commonPrefix;
            this.value = parts.join(' ');
            this.selectionStart = this.selectionEnd = this.value.length;
          } else {
            addLine('');
            const columns = Math.ceil(matches.length / 4);
            for(let i = 0; i < matches.length; i += columns){
              const row = matches.slice(i, i + columns).map(item => {
                const it = children[item];
                const isDir = typeof it === 'string' ? it === 'dir' : it.type === 'dir';
                return (isDir ? '<span class="c-blue">' + item + '/</span>' : 
                              '<span class="c-white">' + item + '</span>');
              }).join('  ');
              addLine(row);
            }
            addLine(promptLabel.innerHTML + '<span class="c-white">' + esc(inputValue) + '</span>');
            scroll();
          }
        }
        return;
      }
    }
    
    if(estado.fase === 'local'){
      if(cmd === 'ssh' && !inputValue.includes('@')){
        this.value = 'ssh invitado@' + SERVIDOR_SSH;
        this.selectionStart = this.selectionEnd = this.value.length;
        return;
      }
      
      const localCommands = [
        'help', 'screenfetch', 'neofetch', 'whoami', 'pwd', 'id', 
        'uname', 'ifconfig', 'ip', 'ls', 'cd', 'clear', 'cls', 'ssh',
        'find', 'locate', 'grep'
      ];
      
      const prefix = cmd || '';
      const matches = localCommands.filter(c => c.startsWith(prefix));
      
      if(matches.length === 1 && prefix.length > 0){
        const match = matches[0];
        const newValue = inputValue.replace(new RegExp('^' + prefix), match);
        this.value = newValue;
        this.selectionStart = this.selectionEnd = this.value.length;
      } else if(matches.length > 1 && prefix.length > 0){
        const commonPrefix = findCommonPrefix(matches);
        if(commonPrefix.length > prefix.length){
          const newValue = inputValue.replace(new RegExp('^' + prefix), commonPrefix);
          this.value = newValue;
          this.selectionStart = this.selectionEnd = this.value.length;
        }
      }
    }
  }
  
  // ── ENTER ──────────────────────────────────────────────────────
  if(e.key==='Enter'){
    const raw=this.value.trim();
    this.value='';
    if(raw){ estado.historial.push(raw); estado.histIdx=estado.historial.length; }
    addLine(promptLabel.innerHTML+'<span class="c-white">'+esc(raw)+'</span>');
    if(!raw){ scroll(); return; }
    executePipeline(raw, estado.fase==='ssh');
    scroll();
  }
  
  // ── FLECHAS ARRIBA/ABAJO (historial) ──────────────────────
  if(e.key==='ArrowUp'){
    e.preventDefault();
    if(estado.histIdx>0){ estado.histIdx--; this.value=estado.historial[estado.histIdx]||''; }
  }
  if(e.key==='ArrowDown'){
    e.preventDefault();
    if(estado.histIdx<estado.historial.length-1){ estado.histIdx++; this.value=estado.historial[estado.histIdx]||''; }
    else{ estado.histIdx=estado.historial.length; this.value=''; }
  }
});

document.getElementById('terminalBody').addEventListener('click',()=>cliInput.focus());
updatePrompt();
</script>
</body>
</html>