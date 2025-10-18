<?php
declare(strict_types=1);
namespace App\Lib;

use PDO;
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/storage.php';

/**
 * Obtiene un ítem del carrito por su identificador.
 */
function findCartItem(string $id) : ?array {
    $sql = <<<SQL
SELECT c.id_carrito,
       c.id_producto,
       c.cantidad,
       c.precio,
       c.precio_total,
        c.fecha_agregado
FROM carrito c
WHERE c.id_carrito = :id
LIMIT 1
SQL;

    $stmt = getPDO()->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }

    $product = findProduct((string) ($row['id_producto'] ?? ''));

    return [
        'id' => (string) ($row['id_carrito'] ?? ''),
        'product' => $product,
        'quantity' => isset($row['cantidad']) ? (int) $row['cantidad'] : 0,
        'unitPrice' => isset($row['precio']) ? (float) $row['precio'] : 0.0,
        'subtotal' => isset($row['precio_total']) ? (float) $row['precio_total'] : 0.0,
        'createdAt' => isset($row['fecha_agregado']) ? (string) $row['fecha_agregado'] : null,
    ];
}

/**
 * Crea un registro en la tabla carrito y retorna la información relevante.
 */
function createCartItem(string $productId, int $quantity) : array {
    if ($quantity <= 0) {
        throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
    }
    $product = findProduct($productId);
    if(!$product) {
        throw new \RuntimeException('Producto no encontrado.');
    }
    $availableStock = (int) ($product['stock'] ?? 0);
    if($availableStock <= 0) {
        throw new \RuntimeException('El producto está agotado.');
    }
    if($quantity > $availableStock) {
        throw new \RuntimeException('La cantidad excede el stock disponible.');
    }
    $unitPrice = (float) ($product['price'] ?? 0);
    $subtotal = $unitPrice * $quantity;

    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO carrito (id_producto, cantidad, precio, precio_total) VALUES (:id_producto, :cantidad, :precio, :precio_total)');
    $stmt->execute([
        ':id_producto' => $productId,
        ':cantidad' => $quantity,
        ':precio' => $unitPrice,
        ':precio_total' => $subtotal,
    ]);

    $cartId = $pdo->lastInsertId();
    $cart = findCartItem((string) $cartId);
    if (!$cart) {
        throw new \RuntimeException('No se pudo recuperar el carrito creado.');
    }
    return $cart;
}

