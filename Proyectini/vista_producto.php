<?php
require_once __DIR__ . '/templates/header.php';
use function App\Lib\findProduct;

$id_producto = filter_input(INPUT_GET, 'id_producto', FILTER_SANITIZE_SPECIAL_CHARS);
//echo "<h1> este es el producto: $id_producto</h1>";
$product = findProduct($id_producto);

if (!$product) {
    echo "<h1>Producto no encontrado</h1>";
    exit;
}
?>

<main class="product-page-container section">

    <div class="product-gallery">
        <img src="<?= htmlspecialchars($product['image'] ?? 'placeholder.jpg') ?>" alt="Vista principal de <?= htmlspecialchars($product['name']) ?>">
    </div>

    <div class="product-content">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        
        <p class="price-large">$<?= number_format($product['price'], 2) ?></p>

        <h3>Acerca de este artículo</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p> <div class="product-specs">
            <h4>Especificaciones</h4>
            <ul>
                <li><strong>Disponibilidad:</strong> <?= ($product['stock'] > 0) ? $product['stock'] . ' unidades en stock' : 'Agotado' ?></li>
                <li><strong>Categoría:</strong> Dulce Típico</li>
                <li><strong>Fecha de adición:</strong> <?= ($product['date']) ?></li>
            </ul>
        </div>
    </div>

    <aside class="product-action-card">
        <div class="price-display">
            <strong class="price-large">$<?= number_format($product['price'], 2) ?></strong>
        </div>
        
        <p class="delivery-info">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
            Envío a todo San Luis Potosí
        </p>

        <?php if ($product['stock'] > 0): ?>
            <p class="stock-status available">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                Disponible
            </p>
    
            <a href="compra.php?id_producto=<?= htmlspecialchars($product['id']) ?>" class="primary button full-width">
                Comprar Ahora
            </a>

        <?php else: ?>
            <p class="stock-status unavailable">Agotado</p>
            <button class="primary button full-width" disabled>No disponible</button>
        <?php endif; ?>
    </aside>

</main>

<script src="compra.js"></script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>