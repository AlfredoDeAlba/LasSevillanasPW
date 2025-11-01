<?php
declare(strict_types=1);
namespace App\Lib;

use PDO;
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/storage.php'; // Necesitamos findProduct

/**
 * Busca un pedido (padre) por su ID.
 */
function findOrder(string $orderId): ?array
{
    $sql = "SELECT * FROM pedido WHERE id_pedido = :id LIMIT 1";
    $stmt = getPDO()->prepare($sql);
    $stmt->execute([':id' => $orderId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Busca todos los artículos de un pedido (hijos) y los une con la info del producto.
 */
function findOrderItems(string $orderId): array
{
    $sql = <<<SQL
SELECT 
    pi.cantidad, 
    pi.precio_unitario, 
    pi.precio_total, 
    p.nombre, 
    p.foto
FROM pedido_item AS pi
JOIN producto AS p ON pi.id_producto = p.id_producto
WHERE pi.id_pedido = :id_pedido
ORDER BY p.nombre
SQL;
    
    $stmt = getPDO()->prepare($sql);
    $stmt->execute([':id_pedido' => $orderId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Busca un cupon valido por su Id
 */
function getValidCouponById(int $id_cupon) : ?array {
    $pdo = getPDO();
    $sql = "SELECT valor_descuento
            FROM cupones
            WHERE id_cupon = ?
                AND activo = TRUE
                AND NOW() BETWEEN fecha_inicio AND fecha_final
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_cupon]);
    $cupon = $stmt->fetch(PDO::FETCH_ASSOC);
    return $cupon ?: null;
}

/**
 * Crea el registro de Pedido y Pedido_Item dentro de una transacción.
 * Esta función es el núcleo del endpoint de pago.
 * NO maneja el commit/rollback, eso debe hacerlo quien la llama.
 *
 * @param \PDO $db La instancia de la base de datos (para la transacción)
 * @param array $formData Los datos del formulario (email, nombre, dirección...)
 * @param array $cartItems Los artículos del carrito (desde cart.js)
 * @param float $discount El descuento total calculado
 * @param ?int $userId ID del usuario logueado (opcional)
 * @param ?int $cuponId ID del cupón usado (opcional)
 *
 * @return string El ID del nuevo pedido creado
 * @throws \Exception Si la validación de precios falla o la DB falla
 */
/**
 * Crea el registro de pedido y pedido_item
 */
function createOrderInTransaction(
    \PDO $db,
    array $formData,
    array $cartItems,
    //float $discount,
    ?int $userId,
    ?int $cuponId
): string {

    // 1. Validación de Totales del Lado del Servidor
    $subtotal = 0;
    $itemsParaInsertar = [];
    
    foreach ($cartItems as $item) {
        $productId = $item['id'] ?? null;
        $quantity = intval($item['quantity'] ?? 0);

        if ($quantity <= 0 || !$productId) {
            throw new \Exception("Producto inválido en el carrito.");
        }

        // Buscar el producto en la DB para obtener el precio real
        $product = findProduct((string)$productId);
        if (!$product) {
            throw new \Exception("Producto con ID $productId no encontrado.");
        }
        
        $precioUnitario = floatval($product['price']);
        
        // Guardamos los datos verificados
        $itemsParaInsertar[] = [
            'id_producto' => $productId,
            'cantidad' => $quantity,
            'precio_unitario' => $precioUnitario,
        ];
        
        $subtotal += $precioUnitario * $quantity;
    }

    $discount = 0.00;
    if($cuponId){
        $cuponValido=getValidCouponById($cuponId);
        if($cuponValido){
            $discount = (float)$cuponValido['valor_descuento'];
        }
    }
    // 2. Cálculo Final
    $total = $subtotal - $discount;
    if ($total < 0) $total = 0;

    // 3. Insertar el Pedido (padre)
    $sqlPedido = <<<SQL
        INSERT INTO pedido (
            id_usuario, id_cupon, precio_subtotal, precio_total, descuento_aplicado, 
            direccion, cod_post, nom_cliente, email_cliente, num_cel, 
            estado_envio
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
        SQL;
    $stmtPedido = $db->prepare($sqlPedido);
    if (!$stmtPedido) {
        $errorInfo = $db->errorInfo();
        throw new \Exception("Error al preparar pedido: " . ($errorInfo[2] ?? 'Desconocido'));
    }

    $params = [
        $userId,
        $cuponId,
        $subtotal,
        $total,
        $discount,
        $formData['direccion'] ?? '',
        $formData['cod_post'] ?? '',
        $formData['nom_cliente'] ?? '',
        $formData['email'] ?? '',
        $formData['num_cel'] ?? '',
    ];
    
    if (!$stmtPedido->execute($params)) {
        $errorInfo = $stmtPedido->errorInfo();
        throw new \Exception("Error al guardar pedido: " . ($errorInfo[2] ?? 'Desconocido'));
    }

    $idPedido = $db->lastInsertId();
    //$stmtPedido->close();

    // 4. Insertar los Items del Pedido (hijos)
    $sqlItem = <<<SQL
        INSERT INTO pedido_item(
            id_pedido, id_producto, cantidad, precio_unitario, precio_total) 
        VALUES (?, ?, ?, ?, ?)
    SQL;
    
    $stmtItem = $db->prepare($sqlItem);
    if (!$stmtItem) {
        $errorInfo = $db->errorInfo();
        throw new \Exception("Error al preparar items: " . ($errorInfo[2] ?? 'Desconocido'));

    }

    foreach ($itemsParaInsertar as $item) {
        $precioTotalItem = $item['precio_unitario'] * $item['cantidad'];
        if (!$stmtItem->execute([
            $idPedido,
            $item['id_producto'],
            $item['cantidad'],
            $item['precio_unitario'],
            $precioTotalItem
        ])) {
            $errorInfo = $stmtItem->errorInfo();
            throw new \Exception("Error al guardar item: " . ($errorInfo[2] ?? 'Desconocido'));
        }
    }
    //$stmtItem->close();

    $stmtItem = null;
    // 5. Retornar el ID del nuevo pedido
    return (string)$idPedido;
}

/**
 * Actualiza el estado de pago de un pedido.
 * Se llama DESPUÉS de que el pago (Stripe) sea exitoso.
 */
function markOrderAsPaid(\PDO $db, string $orderId): bool
{
    // El trigger 'trg_actualizar_stock_pedido' se ejecutará aquí
    $sql = "UPDATE pedido SET pago_completado = TRUE WHERE id_pedido = ?";
    $stmt = $db->prepare($sql);
    return $stmt->execute([$orderId]);
}