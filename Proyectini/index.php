<?php


require_once __DIR__ . '/lib/storage.php';

use function App\Lib\readProducts;

$products = readProducts();
?><!DOCTYPE html>
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
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#catalogo">Cat&aacute;logo</a></li>
                <li><a href="#historia">Historia</a></li>
                <li><a href="#valores">Valores</a></li>
                <li><a href="#contacto">Contacto</a></li>
                <li><a href="#terminos">T&eacute;rminos y Condiciones</a></li>
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

    <main>
        <section id="inicio" class="hero section">
            <div class="hero-content">
                <p class="eyebrow">Sabor artesanal</p>
                <h1>El sabor de la tradici&oacute;n mexicana</h1>
                <p class="lead">Dulces de leche elaborados con recetas familiares y los mejores ingredientes de nuestra tierra.</p>
                <button class="primary" type="button" data-scroll="#catalogo">Conoce nuestros productos</button>
            </div>
            <figure class="hero-figure">
                <img src="https://images.unsplash.com/photo-1589308078050-1989c0ac6805?auto=format&fit=crop&w=900&q=80" alt="Dulces de leche artesanales" loading="lazy">
            </figure>
        </section>

        <section id="catalogo" class="section">
            <header class="section-header">
                <h2>Cat&aacute;logo de productos</h2>
                <p>Descubre nuestra selecci&oacute;n de dulces de leche, cada uno creado con dedicaci&oacute;n y sabor aut&eacute;ntico.</p>
            </header>
            <div class="product-controls">
                <span class="control-label">Vista:</span>
                <div class="view-toggle" role="group" aria-label="Cambiar vista del cat&aacute;logo">
                    <button type="button" data-view="grid" class="active">Cuadr&iacute;cula</button>
                    <button type="button" data-view="list">Lista</button>
                </div>
            </div>
            <div class="product-list" data-view="grid" aria-live="polite"></div>
        </section>

        <section id="historia" class="section">
            <div class="section-grid">
                <div>
                    <h2>Nuestra historia</h2>
                    <p>Hace m&aacute;s de 50 a&ntilde;os, Do&ntilde;a Lupita comenz&oacute; a preparar dulces de leche para su familia y amigos en el coraz&oacute;n de Michoac&aacute;n. Con el tiempo, la pasi&oacute;n por el sabor aut&eacute;ntico y la calidad artesanal nos llev&oacute; a compartir estas recetas con todo M&eacute;xico.</p>
                    <p>Hoy seguimos honrando esa tradici&oacute;n con ingredientes seleccionados y procesos responsables, creando momentos especiales en cada bocado.</p>
                </div>
                <figure>
                    <img src="https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80" alt="Maestro dulcero preparando dulces artesanales" loading="lazy">
                </figure>
            </div>
        </section>

        <section id="valores" class="section values">
            <header class="section-header">
                <h2>Valores que nos definen</h2>
                <p>Creemos en hacer las cosas con coraz&oacute;n, cuidando a nuestra gente, nuestro entorno y tu paladar.</p>
            </header>
            <div class="values-grid">
                <article class="value-card">
                    <span class="value-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="M12 4.2l8 6.2-1.2 1.6-1.3-1v7.8h-4.5v-4.5h-3v4.5H5.5V10.9l-1.3 1-1.2-1.6 9-6.1z" />
                        </svg>
                    </span>
                    <h3>Tradici&oacute;n</h3>
                    <p>Recetas heredadas que mantienen viva la esencia de los dulces mexicanos.</p>
                </article>
                <article class="value-card">
                    <span class="value-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="M12 4l2.1 4.9 5.4.4-4.1 3.5L16 18l-4-2.6L8 18l.6-5.2-4.1-3.5 5.4-.4z" />
                        </svg>
                    </span>
                    <h3>Calidad</h3>
                    <p>Ingredientes seleccionados y procesos cuidadosos para un sabor incomparable.</p>
                </article>
                <article class="value-card">
                    <span class="value-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="M20 12.5a5.4 5.4 0 00-.1-1.2l2-1.2-1-1.8-2.2.5a5.6 5.6 0 00-1.7-1l-.3-2.3h-2l-.3 2.3a5.6 5.6 0 00-1.7 1l-2.2-.5-1 1.8 2 1.2a5.4 5.4 0 000 2.4l-2 1.2 1 1.8 2.2-.5a5.6 5.6 0 001.7 1l.3 2.3h2l.3-2.3a5.6 5.6 0 001.7-1l2.2.5 1-1.8-2-1.2a5.4 5.4 0 00.1-1.2zm-8 0a2.5 2.5 0 112.5 2.5 2.5 2.5 0 01-2.5-2.5z" />
                        </svg>
                    </span>
                    <h3>Innovaci&oacute;n</h3>
                    <p>Sabores contempor&aacute;neos que reinventan lo cl&aacute;sico sin perder lo aut&eacute;ntico.</p>
                </article>
                <article class="value-card">
                    <span class="value-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="M17.5 4.5c-3 1.7-5 4.5-5.5 8.1-1-1.8-2.6-3-4.8-3.6-.4 5.3 3 8.4 5.2 9.9.7.5 1.6.5 2.3-.1 2.5-2.1 4.4-5.9 2.8-10.5zM8 4.5a5.5 5.5 0 00-5.5 5.5c0 3.1 2.6 5.2 6 5.4-2.1-1.6-3.2-3.7-3-6.3 1.9.6 3.2 1.8 4.1 3.4.2-4-2.3-6.7-4.1-7z" />
                        </svg>
                    </span>
                    <h3>Responsabilidad</h3>
                    <p>Compromiso con proveedores locales y pr&aacute;cticas sustentables.</p>
                </article>
            </div>
        </section>

        <section id="testimonios" class="section testimonials">
            <header class="section-header">
                <h2>Lo que dicen nuestros clientes</h2>
                <p>Testimonios que nos impulsan a seguir creando dulces momentos.</p>
            </header>
            <div class="testimonial-list" aria-live="polite"></div>
        </section>

        <section id="contacto" class="section contact">
            <header class="section-header">
                <h2>Contacto</h2>
                <p>&iquest;Tienes dudas o deseas una cotizaci&oacute;n especial? Escr&iacute;benos, ser&aacute; un placer atenderte.</p>
            </header>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Vis&iacute;tanos</h3>
                    <p>Av. de la Dulzura 123, Col. Centro<br>Michoac&aacute;n, M&eacute;xico</p>
                    <h3>Ll&aacute;manos</h3>
                    <p><a href="tel:+523511234567">+52 351 123 4567</a></p>
                    <h3>Correo</h3>
                    <p><a href="mailto:LaSevillanas@gmail.com">LaSevillanas@gmail.com</a></p>
                </div>
                <form class="contact-form" aria-label="Formulario de contacto">
                    <div class="form-field">
                        <label for="nombre">Nombre</label>
                        <input id="nombre" name="nombre" type="text" placeholder="Tu nombre" required />
                    </div>
                    <div class="form-field">
                        <label for="correo">Correo electr&oacute;nico</label>
                        <input id="correo" name="correo" type="email" placeholder="tunombre@email.com" required />
                    </div>
                    <div class="form-field">
                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" rows="4" placeholder="&iquest;C&oacute;mo podemos ayudarte?" required></textarea>
                    </div>
                    <button class="primary" type="submit">Enviar mensaje</button>
                </form>
            </div>
        </section>

        <section id="terminos" class="section terms">
            <header class="section-header">
                <h2>T&eacute;rminos y Condiciones</h2>
                <p>Lee nuestra pol&iacute;tica de privacidad y las condiciones de uso de nuestro sitio.</p>
            </header>
            <article class="terms-content">
                <p>La informaci&oacute;n proporcionada en este sitio tiene fines informativos. Al navegar y realizar pedidos aceptas nuestras pol&iacute;ticas de cumplimiento, protecci&oacute;n de datos y uso responsable.</p>
                <p>Para solicitar la eliminaci&oacute;n de datos personales o consultar m&aacute;s detalles, cont&aacute;ctanos a trav&eacute;s de nuestro formulario.</p>
            </article>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-brand">
            <span class="brand-mark" aria-hidden="true">DL</span>
            <span class="brand-name">Las Sevillanas</span>
        </div>
        <div class="footer-info">
            <p>Av. de la Dulzura 123, Col. Centro, Michoac&aacute;n, M&eacute;xico</p>
            <p>Tel: <a href="tel:+523511234567">+52 351 123 4567</a> &middot; <a href="mailto:LaSevillanas@gmail.com">LaSevillanas@gmail.com</a></p>
        </div>
        <div class="footer-links">
            <a href="https://www.facebook.com/share/1SSXYufzZw/">Facebook</a>
            <a href=" https://www.instagram.com/lassevillanas2025?igsh=bnM3cDc5N3RyN3ox">Instagram</a>
            <a href="https://www.tiktok.com/@lassevillanasnooficial">TikTok</a>
        </div>
        <div class="footer-legal">
            <a href="#terminos">Aviso de privacidad</a>
            <span aria-hidden="true">&middot;</span>
            <a href="#terminos">T&eacute;rminos y condiciones</a>
        </div>
        <div class="footer-legal">
            <a href="admin/login.php">Panel administrativo</a>
        </div>
    </footer>

    <script>
        window.__INITIAL_PRODUCTS__ = <?php echo json_encode($products, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="script.js" defer></script>
</body>
</html>