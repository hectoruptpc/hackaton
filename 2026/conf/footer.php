<?php
/**
 * ============================================================
 * conf/footer.php — Pie de página HTML genérico
 * Hackathon UPTPC 2026 — Unidad de Ciencia y Tecnología
 * ============================================================
 *
 * Variables de control (opcionales, definir ANTES del require_once):
 *   $extra_footer (string) — HTML adicional (scripts, modales, etc.)
 *                            que se inyecta justo antes del cierre </body>.
 *
 * Uso típico en cada página PHP:
 *   $extra_footer = '<script>console.log("hola");</script>';
 *   require_once __DIR__ . '/conf/footer.php';
 *   echo $footer;
 *
 * ── Recursos del footer ──────────────────────────────────────────────────────
 * Para cambiar o agregar imágenes del footer, editar SOLO esta sección:
 */

// ── Logos e imágenes del footer (editar aquí para todos los archivos) ─────────
$footer_logo_cyt  = '../img/cyt.png';   // Logo Unidad de Ciencia y Tecnología
// $footer_logo_otro = '../img/otro.png';  // Ejemplo: añadir otro logo aquí

if (!isset($extra_footer)) {
    $extra_footer = '';
}

$footer = ($extra_footer ? $extra_footer . "\n" : '') . '
<footer style="text-align:center; padding: 24px 0 16px; margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.08);">
    <div style="display:flex; justify-content:center; align-items:center; gap:20px; flex-wrap:wrap;">
        <img src="' . $footer_logo_cyt . '"
             alt="Logo Unidad de Ciencia y Tecnología — UPTPC"
             style="width:90px; height:auto; opacity:0.85;"
             onerror="this.style.display=\'none\'">
    </div>
    <p style="margin-top:10px; font-size:0.75rem; opacity:0.45; letter-spacing:0.05em;">
        Hackathon CARABOBO 2026 &mdash; Unidad de Ciencia y Tecnología &mdash; UPTPC
    </p>
</footer>
</body>
</html>
';
