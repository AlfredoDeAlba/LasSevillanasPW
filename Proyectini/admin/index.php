<?php

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/storage.php';

use function App\Lib\requireAuth;
use function App\Lib\readProducts;

requireAuth();
$products = readProducts();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrativo - Las Sevillanas</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body { margin: var(--space-lg); }
        .admin-layout { display: grid; gap: var(--space-lg); }
        .admin-bar { grid-template-columns: auto 1fr auto; }
        .admin-actions { display: flex; gap: var(--space-sm); justify-content: flex-end; }
        .product-list-admin { width: 100%; border-collapse: collapse; }
        .product-list-admin th, .product-list-admin td { padding: 12px; border-bottom: 1px solid var(--color-border); text-align: left; vertical-align: top; }
        .product-list-admin img { width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-base); }
        .table-actions { display: flex; gap: var(--space-xs); }
        .table-actions button { padding-inline: var(--space-sm); }
        .form-actions { display: flex; gap: var(--space-sm); }
        .hint { color: var(--color-text-muted); font-size: 0.9rem; }
        @media (max-width: 720px) {
            .product-list-admin th:nth-child(4),
            .product-list-admin td:nth-child(4) { display: none; }
            .product-list-admin img { width: 60px; height: 60px; }
        }
    </style>
</head>
<body data-theme="light">
    <header class="top-bar admin-bar">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">DL</span>
            <span class="brand-name">Panel administrativo</span>
        </div>
        <div></div>
        <div class="admin-actions">
            <a class="primary" href="../index.php" target="_blank" rel="noopener">Ver sitio</a>
            <a class="primary" href="logout.php">Cerrar sesion</a>
        </div>
    </header>

    <main class="admin-layout">
        <section class="section">
            <header class="section-header">
                <h2>Nuevo producto</h2>
                <p>Completa el formulario para agregar un producto al catálogo o editárlo desde la lista.</p>
            </header>
            <form id="product-form" class="contact-form" enctype="multipart/form-data">
                <input type="hidden" id="product-id" name="id">
                <div class="form-field">
                    <label for="product-name">Nombre</label>
                    <input id="product-name" name="name" type="text" placeholder="Nombre del producto" required>
                </div>
                <div class="form-field">
                    <label for="product-price">Precio</label>
                    <input id="product-price" name="price" type="number" min="0" step="0.01" placeholder="0.00" required>
                </div>
                <div class="form-field">
                    <label for="product-description">DescripciÃ³n</label>
                    <textarea id="product-description" name="description" rows="4" placeholder="Describe el sabor, ingredientes o presentaciÃ³n" required></textarea>
                </div>
                <div class="form-field">
                    <label for="product-stock">Stock</label>
                    <input id="product-stock" name="stock" type="number" placeholder="Stock del producto" required>
                </div>
                <div class="form-field">
                    <label for="product-date">Fecha de Agregado</label>
                    <input id="product-date" name="date" type="text" placeholder="Fecha de agregado del producto" required>
                </div>
                <div class="form-field">
                    <label for="product-image">Imagen</label>
                    <input id="product-image" name="productImage" type="file" accept="image/jpeg,image/png,image/webp">
                    <p class="hint">Formatos permitidos: JPG, PNG, WEBP (mÃ¡x. 4MB). Si editas un producto y no seleccionas una imagen nueva, se conservarÃ¡ la actual.</p>
                </div>
                <div class="form-actions">
                    <button class="primary" type="submit">Guardar</button>
                    <button type="reset">Limpiar</button>
                </div>
            </form>
        </section>

        <section class="section">
            <header class="section-header">
                <h2>Productos publicados</h2>
                <p>Gestiona los productos existentes. Usa los botones para editar o eliminar.</p>
            </header>
            <div class="table-wrapper" style="overflow-x:auto;">
                <table class="product-list-admin" aria-live="polite">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Imagen</th>
                            <th>DescripciÃ³n</th>
                            <th>Stock</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="product-rows">
                        <?php if (empty($products)): ?>
                            <tr><td colspan="5">No hay productos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr data-id="<?php echo htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </td>
                                    <td>$<?php echo number_format((float) $product['price'], 2); ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php else: ?>
                                            <span class="hint">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                    <td><?php echo htmlspecialchars($product['stock'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                    <td><?php echo htmlspecialchars($product['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <button type="button" class="edit" data-action="edit">Editar</button>
                                            <button type="button" class="delete" data-action="delete">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="admin.js" defer></script>
</body>
</html>
