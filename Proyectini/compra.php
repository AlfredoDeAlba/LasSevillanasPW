<?php
// Ya no necesitamos un producto individual
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/lib/config.php';
?>

<main class="purchase-page">
    <div class="purchase-container">
        
        <section class="payment-form-section">
            
            <div class="form-section">
                <div class="form-section-header">
                    <span class="section-number">1</span>
                    <h3>Tu Carrito</h3>
                </div>
                <div id="checkout-cart-list" class="checkout-list">
                    </div>
                <div id="cart-empty-message" style="display: none;">
                    <p>Tu carrito está vacío. <a href="catalogo.php">Volver al catálogo</a>.</p>
                </div>
            </div>

            <form id="payment-form" novalidate>
                <div class="form-section">
                    <div class="form-section-header">
                        <span class="section-number">2</span>
                        <h3>Información de Contacto</h3>
                    </div>
                    <div class="form-field">
                        <label for="email">Correo Electrónico</label>
                        <div class="input-with-icon">
                            <input type="email" id="email" name="email" placeholder="Para enviarte el recibo de tu compra" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">
                        <span class="section-number">3</span>
                        <h3>Datos de Envío</h3>
                    </div>
                    <div class="form-field">
                        <label for="nom_cliente">Nombre Completo</label>
                        <input type="text" id="nom_cliente" name="nom_cliente" required>
                    </div>
                    <div class="form-field">
                        <label for="direccion">Dirección de Envío</label>
                        <input type="text" id="direccion" name="direccion" required>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="cod_post">Código Postal</label>
                            <input type="text" id="cod_post" name="cod_post" required>
                        </div>
                        <div class="form-field">
                            <label for="ciudad">Ciudad</label>
                            <input type="text" id="ciudad" name="ciudad" value="San Luis Potosí" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="num_cel">Teléfono de Contacto</label>
                        <input type="tel" id="num_cel" name="num_cel" required>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">
                        <span class="section-number">4</span>
                        <h3>Método de Pago</h3>
                    </div>
                    <button type="button" id="add-card-btn" class="secondary button full-width">
                        Agregar una nueva tarjeta
                    </button>
                </div>

                <button type="submit" id="submit-payment-btn" class="primary button full-width">Procesar Pago</button>
                <div id="payment-status"></div>
            </form>
        </section>

        <aside class="product-summary">
            <div class="product-summary-card">
                
                <div class="form-field coupon-field">
                    <label for="coupon-code">Cupón de Descuento</label>
                    <div class="coupon-input-group">
                        <input type="text" id="coupon-code" name="coupon_code" placeholder="Ej: BIENVENIDA10">
                        <button type="button" id="apply-coupon-btn">Aplicar</button>
                    </div>
                </div>
                <hr class="divider">
                <div class="order-total">
                    <p><span>Subtotal:</span> <span id="subtotal-amount">$0.00</span></p>
                    <p><span>Descuento:</span> <span id="discount-amount">-$0.00</span></p>
                    <hr class="total-divider">
                    <p class="total-line"><strong>Total a Pagar:</strong> <strong id="total-amount">$0.00</strong></p>
                </div>
            </div>
        </aside>
    </div>

    <div id="add-card-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Agregar Tarjeta de Pago</h3>
                <button id="close-modal-btn" class="close-btn" type="button" aria-label="Cerrar">&times;</button>
            </div>

            <div class="modal-body">
                <form id="stripe-card-form">
                    <div class="form-field">
                        <label for="cardholder-name">Nombre del titular</label>
                        <input id="cardholder-name" type="text" class="StripeElement" placeholder="Nombre como aparece en la tarjeta" required>
                    </div>

                    <div class="form-field">
                        <label for="card-element">Datos de la Tarjeta</label>
                        <div id="card-element"></div>
                        <div id="card-errors" role="alert" class="error-message" style="margin-top: 10px;"></div>
                    </div>
                </form>

                <div class="card-accepted-logos">
                    <p>Aceptamos las principales tarjetas:</p>
                    <img src="uploads/stripe-logos.png" alt="Visa, Mastercard, American Express">
                </div>
            </div>

            <div class="modal-footer">
                <button id="cancel-card-btn" type="button" class="secondary">Cancelar</button>
                <button id="submit-card-btn" type="button" class="primary">Pagar</button>
            </div>
        </div>
    </div>
</main>

<script>
    window.STRIPE_PUBLIC_KEY = "<?php echo htmlspecialchars($_ENV['STRIPE_PUBLIC_KEY']); ?>";
</script>
<script src="./js/compra.js" defer></script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>