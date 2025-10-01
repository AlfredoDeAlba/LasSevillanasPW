<?php
// Usamos una ruta absoluta para asegurar que siempre encuentre el archivo
require_once __DIR__ . '/../lib/storage.php';

use function App\Lib\readProducts;

// Leemos los productos para que estén disponibles en cualquier página que incluya este header
$products = readProducts();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dulces de Leche - Tradici&oacute;n Mexicana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css" />
</head>
<body data-theme="light">
    <header class="top-bar">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">DL</span>
            <span class="brand-name">Las Sevillanas</span>
        </div>
        <button class="theme-toggle" type="button" aria-pressed="false" aria-label="Cambiar tema">
            <span class="icon-sun" aria-hidden="true">
                <svg viewBox="0 0 24 24" focusable="false">
                    <circle cx="12" cy="12" r="5" />
                    <path d="M12 2v2.5M12 19.5V22M4.5 12H2M22 12h-2.5M5.1 5.1l1.8 1.8M17.1 17.1l1.8 1.8M5.1 18.9l1.8-1.8M17.1 6.9l1.8-1.8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                </svg>
            </span>
            <span class="icon-moon" aria-hidden="true" hidden>
                <svg viewBox="0 0 24 24" focusable="false">
                    <path d="M20 14.5A8.5 8.5 0 0110.5 5 6.5 6.5 0 0014 18.5 8.5 8.5 0 0020 14.5Z" />
                </svg>
            </span>
            <span class="label">Modo claro</span>
        </button>
        <nav class="main-nav" aria-label="Principal">
            <ul>
                <li><a href="index.php#inicio">Inicio</a></li>
                <li><a href="catalogo.php">Cat&aacute;logo</a></li>
                <li><a href="historia.php">Historia</a></li>
                <li><a href="index.php#valores">Valores</a></li>
                <li><a href="index.php#contacto">Contacto</a></li>
                <li><a href="terminos.php">T&eacute;rminos y Condiciones</a></li>
            </ul>
        </nav>
        <div class="social-links" aria-label="Redes sociales">
            <a href="https://www.facebook.com" aria-label="Facebook">
                <svg viewBox="0 0 24 24" role="img" aria-hidden="true">
                    <path d="M20 12.05C20 7.59 16.42 4 11.95 4S4 7.59 4 12.05c0 3.91 2.85 7.15 6.58 7.86v-5.56H8.9v-2.3h1.68V10.3c0-1.66.98-2.58 2.49-2.58.72 0 1.47.13 1.47.13v1.62h-.83c-.82 0-1.08.51-1.08 1.03v1.24h1.85l-.3 2.3h-1.55v5.56c3.73-.71 6.58-3.95 6.58-7.86Z" />
                </svg>
            </a>
            <a href="https://www.instagram.com" aria-label="Instagram">
                <svg viewBox="0 0 24 24" role="img" aria-hidden="true">
                    <path d="M16.75 3h-9.5A4.25 4.25 0 003 7.25v9.5A4.25 4.25 0 007.25 21h9.5A4.25 4.25 0 0021 16.75v-9.5A4.25 4.25 0 0016.75 3Zm2.75 13.75a2.75 2.75 0 01-2.75 2.75h-9.5A2.75 2.75 0 014.5 16.75v-9.5A2.75 2.75 0 017.25 4.5h9.5A2.75 2.75 0 0119.5 7.25v9.5ZM12 7.75A4.25 4.25 0 117.75 12 4.25 4.25 0 0112 7.75Zm0 6.5A2.25 2.25 0 109.75 12 2.25 2.25 0 0012 14.25ZM17 7a1 1 0 11-1-1 1 1 0 011 1Z" />
                </svg>
            </a>
            <a href="https://www.tiktok.com" aria-label="TikTok">
                <svg viewBox="0 0 24 24" role="img" aria-hidden="true">
                    <path d="M19 7.57a5.4 5.4 0 01-3.3-1.08v6.27a4.31 4.31 0 11-3.6-4.25v2.27a1.84 1.84 0 100 3.68 1.83 1.83 0 001.83-1.84V3h2.23a3.18 3.18 0 003 3.22Z" />
                </svg>
            </a>
        </div>
    </header>