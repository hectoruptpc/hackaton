<?php
/**
 * ============================================================
 * conf/header.php — Cabecera HTML genérica
 * Hackathon UPTPC 2026 — Unidad de Ciencia y Tecnología
 * ============================================================
 *
 * Variables de control (deben definirse ANTES del require_once):
 *   $page_title  (string)  — Texto del <title>. Por defecto: "Hackathon CARABOBO 2026"
 *   $extra_head  (string)  — HTML adicional (estilos/scripts propios de la página)
 *                            que se inyecta al final del <head>, justo antes de </head>.
 *   $body_attrs  (string)  — Atributos adicionales del <body>, p. ej. 'class="dark-mode"'.
 *
 * Uso típico en cada página PHP:
 *   $page_title = 'Mi Página | Hackathon 2026';
 *   $extra_head  = '<style>body { color: red; }</style>';
 *   $body_attrs  = 'class="penalized"';   // Opcional
 *   require_once __DIR__ . '/conf/header.php';
 *   echo $header;
 */

// Valores por defecto para las variables de control
if (!isset($page_title)) {
    $page_title = 'Hackathon CARABOBO 2026 — UPTPC';
}
if (!isset($extra_head)) {
    $extra_head = '';
}
if (!isset($body_attrs)) {
    $body_attrs = '';
}

$header = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hackathon CARABOBO 2026 — Desafíos de Seguridad Informática — Unidad de Ciencia y Tecnología UPTPC">
    <meta name="theme-color" content="#0d1117">
    <title>' . htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . '</title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- IA Avatar — Estilos y lógica del asistente virtual -->
    <link rel="stylesheet" href="conf/ia_avatar.css?v=2026_v18">
    <script src="conf/ia_avatar.js?v=2026_v18" defer></script>

    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">

    <!-- Bootstrap 5.3.3 JS Bundle (Popper incluido) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
' . ($extra_head ? "\n    <!-- Estilos / scripts específicos de la página -->\n" . $extra_head . "\n" : '') . '
</head>
<body' . ($body_attrs ? ' ' . $body_attrs : '') . '>
';
