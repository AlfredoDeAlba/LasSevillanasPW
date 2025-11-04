<?php

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/storage.php';

use function App\Lib\requireAuth;
use function App\Lib\readProducts;

//requireAuth();
$products = readProducts();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrativo - Las Sevillanas</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <nav class="admin-nav">
            <a href="#products-view">Gestionar Productos</a>
            <a href="#orders-view">Ver Pedidos</a>
            <a href="#stats-view">Estadísticas</a>
            <a href="#cupones-view">Cupones</a>
            <a href="#banners-view">Banners</a>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </nav>
    </header>
    <main class="admin-layout">
        <div id="products-view" class="view">
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
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="product-rows">
                            <?php if (empty($products)): ?>
                                <tr><td colspan="6">No hay productos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr data-id="<?php echo htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </td>
                                        <td>$<?php echo number_format((float) $product['price'], 2); ?></td>
                                        <td>
                                            <?php if (!empty($product['image'])): ?>
                                                <?php
                                                    $imagePath = $product['image'];
                                                    if (strpos($imagePath, 'http') !== 0) {
                                                        $imagePath = '../uploads/' . $imagePath;
                                                    }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>"
                                                     alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php else: ?>
                                                <span class="hint">Sin imagen</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($product['stock'], ENT_QUOTES, 'UTF-8'); ?></td>
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
        </div>
        <div id="stats-view" class="view" style="display: none;">
            <h2>Estadisticas y Reportes:</h2>
            <div style="width: 80%; margin: auto;">
                <h3>Ventas Mensuales</h3>
                <canvas id="chart-ventas-mensuales"></canvas>
            </div>
            
            <div style="width: 80%; margin: auto; margin-top: 50px;">
                <h3>Ventas Estacionales (Trimestrales)</h3>
                <canvas id="chart-ventas-estacionales"></canvas>
            </div>
            
            <div style="width: 80%; margin: auto; margin-top: 50px;">
                <h3>Visitas Diarias (Últimos 30 días)</h3>
                <canvas id="chart-visitas-diarias"></canvas>
            </div>

            <div style="width: 80%; margin: auto; margin-top: 50px;">
                <h3>Top 5 Productos Vendidos</h3>
                <canvas id="chart-top-productos"></canvas>
            </div>
        </div>
        <div id="cupones-view" class="view" style="display: none;">
            <h2>Gestion de Cupones:</h2>
            <form>
                <h3>Crear / Editar Cupones</h3>
                <input type="hidden" id="cupon-id" name="id_cupon">
                Codigo: <input type="text" id="cupon-codigo" required>
                Descripcion: <input type="text" id="cupon-descripcion">
                Valor Descuento ($$$): <input type="number" step="0.01" id="cupon-valor" required>
                Fecha Inicio: <input type="datetime-local" id="cupon-inicio" required>
                Fecha Fin: <input type="datetime-local" id="cupon-fin" required>
                Activo:<input type="checkbox" id="cupon-activo" checked>
                <button type="submit">Guardar Cupón</button>
                <button type="button" id="btn-cancelar-cupon" style="display: none;">Cancelar Edición</button>
            </form>
            <h3>Cupones Existentes</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Valor</th>
                        <th>Válido Desde</th>
                        <th>Válido Hasta</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-cupones-body">
                    </tbody>
            </table>
        </div>
        <div id="banners-view" class="view" style="display: none;">
            <h2> Gestión de Banners y Promociones Visuales</h2>

            <form id="form-banner" class="admin-form">
                <h3>Nuevo Banner</h3>
                <div>
                    <label for="banner-titulo">Título:</label>
                    <input type="text" id="banner-titulo" required>
                </div>
                <div>
                    <label for="banner-descripcion">Descripción Corta:</label>
                    <input type="text" id="banner-descripcion" style="width: 300px;">
                </div>
                <div>
                    <label for="banner-link">Link Destino (ej. /catalogo.php):</label>
                    <input type="text" id="banner-link">
                </div>
                <div>
                    <label for="banner-imagen-file">Imagen del Banner:</label>
                    <input type="file" id="banner-imagen-file" accept="image/*" required>
                </div>
                <button type="submit">Subir y Guardar Banner</button>
            </form>

            <h3>Banners Actuales</h3>
            <table class="admin-table">
                 <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Título</th>
                        <th>Link</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-banners-body">
                    </tbody>
            </table>
        </div>
    </main>
    <script src="admin.js" defer></script>
</body>
</html>
