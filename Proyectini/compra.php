<?php
require_once __DIR__ . '/templates/header.php';
use function App\Lib\findProduct;

$id_producto = filter_input(INPUT_GET, 'id_producto', FILTER_SANITIZE_SPECIAL_CHARS);
$product = findProduct($id_producto);

if (!$product) {
    echo "<h1>Producto no encontrado</h1>";
    exit;
}
?>

<main class="purchase-page">
    <div class="purchase-container">
        <section class="payment-form-section">
            <form id="payment-form" novalidate>
                <input type="hidden" id="id_producto" name="id_producto" value="<?= htmlspecialchars($product['id'] ?? '') ?>">

                <div class="form-section">
                    <div class="form-section-header">
                        <span class="section-number">1</span>
                        <h3>Información de Contacto</h3>
                    </div>
                    <div class="form-field">
                        <label for="email">Correo Electrónico</label>
                        <div class="input-with-icon">
                            <svg aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>
                            <input type="email" id="email" name="email" placeholder="Para enviarte el recibo de tu compra" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">
                        <span class="section-number">2</span>
                        <h3>Datos de Envío</h3>
                    </div>
                    <div class="form-field">
                        <label for="nom_cliente">Nombre Completo</label>
                        <input type="text" id="nom_cliente" name="nom_cliente" placeholder="Ingresa tu nombre completo" required>
                    </div>
                    <div class="form-field">
                        <label for="direccion">Dirección de Envío</label>
                        <input type="text" id="direccion" name="direccion" placeholder="Calle, número, colonia" required>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="cod_post">Código Postal</label>
                            <input type="text" id="cod_post" name="cod_post" placeholder="Ej: 78000" required>
                        </div>
                        <div class="form-field">
                            <label for="ciudad">Ciudad</label>
                            <input type="text" id="ciudad" name="ciudad" value="San Luis Potosí" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="num_cel">Teléfono de Contacto</label>
                        <input type="tel" id="num_cel" name="num_cel" placeholder="10 dígitos" required>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">
                        <span class="section-number">3</span>
                        <h3>Método de Pago</h3>
                    </div>
                    <div id="selected-card-container" class="selected-card" style="display: none;"></div>
                    <button type="button" id="add-card-btn" class="secondary button full-width">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"></path></svg>
                        Agregar una nueva tarjeta
                    </button>
                </div>

                <button type="submit" id="submit-payment-btn" class="primary button full-width">Pagar Ahora</button>
                <div id="payment-status"></div>
            </form>
        </section>
        <aside class="product-summary">
            <div class="product-summary-card">
                <div class="product-info">
                    <img src="<?= htmlspecialchars($product['image'] ?? 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($product['name'] ?? 'Producto') ?>">
                    <div class="product-details">
                        <h2><?= htmlspecialchars($product['name'] ?? 'Producto no encontrado') ?></h2>
                        <div class="quantity-container">
                            <div class="quantity-selector">
                                <label for="quantity">Cantidad:</label>
                                <input type="number" id="quantity" name="cantidad" value="1" min="1" max="<?= htmlspecialchars($product['stock'] ?? 10) ?>" aria-label="Cantidad de producto">
                            </div>
                            <span id="stock-error-message" class="error-message"></span>
                        </div>
                    <div class="price-display">
                        <strong id="product-price">$<?= number_format($product['price'] ?? 0, 2) ?></strong>
                    </div>
                </div>
                </div>
                <div class="form-field coupon-field">
                    <label for="coupon-code">Cupón de Descuento</label>
                    <div class="coupon-input-group">
                        <input type="text" id="coupon-code" name="coupon_code" placeholder="Ej: BIENVENIDA10">
                        <button type="button" id="apply-coupon-btn">Aplicar</button>
                    </div>
                </div>
                <hr class="divider">
                <div class="order-total">
                    <p><span>Subtotal:</span> <span id="subtotal-amount">$<?= number_format($product['price'] ?? 0, 2) ?></span></p>
                    <p><span>Descuento:</span> <span id="discount-amount">-$0.00</span></p>
                    <hr class="total-divider">
                    <p class="total-line"><strong>Total a Pagar:</strong> <strong id="total-amount">$<?= number_format($product['price'] ?? 0, 2) ?></strong></p>
                </div>
            </div>
        </aside>
        </div>

    <div id="add-card-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Agregar una tarjeta de crédito o débito</h3>
                <button type="button" id="close-modal-btn" class="close-btn" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body">
                <div class="card-form-container">
                    <form id="new-card-form">
                        <div class="form-field">
                            <label for="card-number">Número de tarjeta</label>
                            <input type="text" id="card-number" placeholder="XXXX XXXX XXXX XXXX">
                        </div>
                        <div class="form-field">
                            <label for="card-name">Nombre en la tarjeta</label>
                            <input type="text" id="card-name" placeholder="Juan Pérez">
                        </div>
                        <div class="form-row">
                            <div class="form-field">
                                <label for="card-expiry">Fecha de vencimiento</label>
                                <input type="text" id="card-expiry" placeholder="MM/AA">
                            </div>
                            <div class="form-field">
                                <label for="card-cvc">Código de Seguridad</label>
                                <input type="text" id="card-cvc" placeholder="CVC">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-accepted-logos">
                    <p>Se aceptan las siguientes tarjetas de crédito y débito:</p>
                    <img src="https/d1yjjnpx0p53s8.cloudfront.net/styles/logo-thumbnail/s3/0017/3389/brand.gif?itok=j2QnThRX" alt="Stripe">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="secondary button" id="cancel-card-btn">Cancelar</button>
                <button type="button" class="primary button" id="save-card-btn">Agregar tarjeta</button>
            </div>
        </div>
    </div>
</main>

<script src="https://js.stripe.com/v3/"></script>
<script src="compra.js" defer></script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
