<?php require_once __DIR__ . '/templates/header.php'; ?>

    <main>
        <section id="inicio" class="hero section">
            <div class="hero-content">
                <p class="eyebrow">Sabor artesanal</p>
                <h1>El sabor de la tradici&oacute;n mexicana</h1>
                <p class="lead">Dulces de leche elaborados con recetas familiares y los mejores ingredientes de nuestra tierra.</p>
                <div class="hero-actions">
                    <button class="primary" type="button" onclick="location.href='catalogo.php'">Conoce nuestros productos</button>
                    <span class="hero-subheadline">Descubre nuestros dulces artesanales.</span>
                </div>
            </div>
            <div class="hero-showcase">
                <div class="hero-carousel" aria-label="Productos destacados">
                    <button class="carousel-control prev" type="button" aria-label="Producto anterior">
                        <span aria-hidden="true">&#10094;</span>
                    </button>
                    <div class="carousel-window">
                        <ul class="carousel-track"></ul>
                    </div>
                    <button class="carousel-control next" type="button" aria-label="Producto siguiente">
                        <span aria-hidden="true">&#10095;</span>
                    </button>
                </div>
                <div class="carousel-indicator" aria-hidden="true"></div>
                <p class="carousel-status" data-carousel-status role="status" aria-live="polite"></p>
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

        <script src="./users/user.js"></script>
        <script src="script.js"></script>
    </main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
