<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/storage.php';
require_once __DIR__ . '/lib/order.php';
require_once __DIR__ . '/lib/auth_usr.php';
require_once __DIR__ . '/lib/receipt.php';


require_once __DIR__ . '/templates/header.php';

use function App\Lib\findOrder;
use function App\Lib\findOrderItems;
use function App\Lib\sendEmail;
use function App\Lib\buildReceiptHtml;
use function App\Lib\generateOrderPdfStream;

// 1. Obtener y sanitizar el ID del pedido
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT);
$order = null;
$items = [];
$pdfFilename = null; // Para el enlace de descarga
$emailSent = false; // Para mostrar un mensaje de estado

if ($order_id) {
    // 2. Buscar el pedido en la base de datos
    $order = findOrder($order_id);
    if ($order) {
        // 3. Buscar los artículos de ese pedido
        $items = findOrderItems($order_id);
    }
}

// 4. Generar PDF y enviar correo (solo si el pedido es válido)
if ($order && !empty($items)) {
    try {
        // Define el nombre y la ruta del archivo
        $pdfFilename = 'recibo_pedido_' . $order['id_pedido'] . '.pdf';
        $savePath = __DIR__ . '/uploads/recibos/' . $pdfFilename;

        // Generar el contenido HTML (para email y PDF)
        $receiptHtml = buildReceiptHtml($order, $items);

        // Generar el PDF
        $pdfData = generateOrderPdfStream($receiptHtml);
        
        // Guardar el PDF en el servidor
        file_put_contents($savePath, $pdfData);
        
        // Enviar el correo coN el PDF adjunto
        $emailSubject = "Confirmación de tu pedido en Las Sevillanas #" . $order['id_pedido'];
        $emailSent = sendEmail(
            $order['email_cliente'], 
            $emailSubject, 
            $receiptHtml, 
            $pdfData,       // data del PDF
            $pdfFilename    //nombre del archivo
        );

    } catch (Exception $e) {
        error_log('Error al generar PDF o enviar email: ' . $e->getMessage());
        // No detenemos la página, solo mostramos que algo falló
    }
}

?>

<main class="purchase-page" style="max-width: 800px; margin: 20px auto;">
    <div class="section">

        <?php if ($order && !empty($items)): ?>
            <header class="section-header" style="text-align: center;">
                <span style="font-size: 3rem;">🎉</span>
                <h2>¡Gracias por tu compra, <?= htmlspecialchars($order['nom_cliente']) ?>!</h2>
                
                <?php if ($emailSent): ?>
                    <p>Tu pedido ha sido confirmado. Hemos enviado un recibo con el PDF a <strong><?= htmlspecialchars($order['email_cliente']) ?></strong>.</p>
                <?php else: ?>
                    <p>Tu pedido ha sido confirmado. Hubo un problema al enviar el correo, pero tu pedido está seguro.</p>
                <?php endif; ?>
                
                <p><strong>Número de Pedido:</strong> #<?= htmlspecialchars($order['id_pedido']) ?></p>

                <p style="margin-top: 20px;">
                    <a href="uploads/recibos/<?= htmlspecialchars($pdfFilename) ?>" class="secondary button" download>
                        Descargar Recibo en PDF
                    </a>
                </p>
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
            
            <?php else: ?>
            <?php endif; ?>

        <div style="text-align: center; margin-top: var(--space-lg);">
            <a href="catalogo.php" class="primary button">Seguir comprando</a>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/templates/footer.php'; // Carga el Footer ?>