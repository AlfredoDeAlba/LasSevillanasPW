<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/storage.php';
require_once __DIR__ . '/lib/order.php';
require_once __DIR__ . '/templates/header.php';

use function App\Lib\findOrder;
use function App\Lib\findOrderItems;

// 1. Obtener y sanitizar el ID del pedido
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT);
$order = null;
$items = [];

if ($order_id) {
    // 2. Buscar el pedido en la base de datos
    $order = findOrder($order_id);
    if ($order) {
        // 3. Buscar los artículos de ese pedido
        $items = findOrderItems($order_id);
    }
}

?>

<main class="purchase-page" style="max-width: 800px; margin: 20px auto;">
    <div class="section">

        <?php if ($order && !empty($items)): ?>
            <header class="section-header" style="text-align: center;">
                <span style="font-size: 3rem;">🎉</span>
                <h2>¡Gracias por tu compra, <?= htmlspecialchars($order['nom_cliente']) ?>!</h2>
                <p>Tu pedido ha sido confirmado. Hemos enviado un recibo a <strong><?= htmlspecialchars($order['email_cliente']) ?></strong>.</p>
                <p><strong>Número de Pedido:</strong> #<?= htmlspecialchars($order['id_pedido']) ?></p>
            </header>

            <hr class="divider">

            <h3>Resumen de tu Pedido</h3>
            
            <div id="checkout-cart-list" class="checkout-list">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($item['foto'] ?? 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
                        <div class="cart-item-info">
                            <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                            <div class="cart-item-meta">
                                <span>Cantidad: <?= htmlspecialchars($item['cantidad']) ?></span>
                                <span>Precio: $<?= number_format((float)$item['precio_unitario'], 2) ?> c/u</span>
                            </div>
                        </div>
                        <strong>$<?= number_format((float)$item['precio_total'], 2) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr class="divider">

            <div class="order-total">
                <p><span>Subtotal:</span> <span>$<?= number_format((float)$order['precio_subtotal'], 2) ?></span></p>
                <p><span>Descuento:</span> <span>-$<?= number_format((float)$order['descuento_aplicado'], 2) ?></span></p>
                <p class="total-line"><strong>Total Pagado:</strong> <strong>$<?= number_format((float)$order['precio_total'], 2) ?></strong></p>
            </div>

            <hr class="divider">
            
            <h4>Información de Envío</h4>
            <address style="font-style: normal;">
                <strong><?= htmlspecialchars($order['nom_cliente']) ?></strong><br>
                <?= htmlspecialchars($order['direccion']) ?><br>
                CP: <?= htmlspecialchars($order['cod_post']) ?><br>
                Tel: <?= htmlspecialchars($order['num_cel']) ?>
            </address>

        <?php else: ?>
            <header class="section-header" style="text-align: center;">
                <h2>Hubo un problema</h2>
                <p>No pudimos encontrar los detalles de tu pedido. Por favor, contacta a soporte.</p>
            </header>
        <?php endif; ?>

        <div style="text-align: center; margin-top: var(--space-lg);">
            <a href="catalogo.php" class="primary button">Seguir comprando</a>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/templates/footer.php'; // Carga el Footer ?>