<?php
declare(strict_types=1);
namespace App\Lib;

use PDO;
use Exception; // Asegúrate de importar la clase Exception

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/storage.php'; // Necesitamos findProduct y applyPromotions

use function App\Lib\getPDO;
use function App\Lib\findProduct;
use function App\Lib\applyPromotions; // ¡Importante!

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
 * Calcula el total final del pedido de forma segura en el servidor.
 * Aplica promociones (por producto) y luego cupones (al total).
 *
 * @param array $cartItems Items del carrito (solo usamos id y quantity)
 * @param ?int $cuponId ID del cupón (si aplica)
 * @return array [totalFinal, subtotalConPromos, descuentoCupon]
 * @throws Exception Si un producto no se encuentra
 */
function calculateServerTotal(array $cartItems, ?int $cuponId): array
{
    if (empty($cartItems)) {
        return [0.0, 0.0, 0.0];
    }

    // 1. Obtener IDs y crear un mapa de cantidades
    $productIds = [];
    $quantityMap = [];
    foreach ($cartItems as $item) {
        $id = (int)$item['id'];
        $productIds[] = $id;
        $quantityMap[$id] = (int)$item['quantity'];
    }
    
    // 2. Obtener todos los productos de la BD (en bucle, simple pero menos eficiente)
    $productsFromDB = [];
    foreach (array_unique($productIds) as $id) {
        $product = findProduct((string)$id);
        if (!$product) {
            throw new Exception("Producto ID $id no encontrado.");
        }
        $productsFromDB[] = $product;
    }

    // 3. Aplicar PROMOCIONES a los productos
    // (Asegúrate que 'applyPromotions' esté en storage.php)
    $productsWithPromos = applyPromotions($productsFromDB); 

    // 4. Calcular el subtotal (ya con promociones)
    $subtotalConPromos = 0.0;
    foreach ($productsWithPromos as $product) {
        $id = (int)$product['id_producto'];
        // Usar precio_descuento si existe, si no, el precio normal
        $precioReal = (float)($product['precio_descuento'] ?? $product['precio']);
        
        // Asegurarse de que el ID esté en el mapa (debería estarlo)
        if (isset($quantityMap[$id])) {
             $subtotalConPromos += $precioReal * $quantityMap[$id];
        }
    }

    // 5. Aplicar CUPÓN
    $descuentoCupon = 0.0;
    if ($cuponId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT valor_descuento FROM cupones
            WHERE id_cupon = ? AND activo = TRUE AND NOW() BETWEEN fecha_inicio AND fecha_final
        ");
        $stmt->execute([$cuponId]);
        $cupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cupon) {
            $descuentoCupon = (float)$cupon['valor_descuento'];
        }
    }

    // 6. Calcular Total Final
    $totalFinal = $subtotalConPromos - $descuentoCupon;
    
    // Asegurarse de que el total no sea negativo (ej. cupón mayor que el total)
    $totalFinal = max(0.0, $totalFinal); 

    return [$totalFinal, $subtotalConPromos, $descuentoCupon];
}


/**
 * Crea el registro de Pedido y Pedido_Item dentro de una transacción.
 * Esta función AHORA calcula el total real internamente usando calculateServerTotal.
 *
 * @param \PDO $db La instancia de la base de datos (para la transacción)
 * @param array $formData Los datos del formulario (email, nombre, dirección...)
 * @param array $cartItems Los artículos del carrito (desde cart.js)
 * @param ?int $userId ID del usuario logueado (opcional)
 * @param ?int $cuponId ID del cupón usado (opcional)
 *
 * @return array [string $idPedido, float $totalFinalCalculado]
 * @throws \Exception Si la validación de precios falla o la DB falla
 */
function createOrderInTransaction(
    \PDO $db,
    array $formData,
    array $cartItems,
    ?int $userId,
    ?int $cuponId
): array { // Devuelve un array con el ID y el total

    // --- 1. CÁLCULO DE TOTALES (La forma correcta) ---
    list($totalFinal, $subtotalConPromos, $descuentoCupon) = 
        calculateServerTotal($cartItems, $cuponId);

    // --- 2. OBTENER PRECIOS UNITARIOS CORRECTOS (CON PROMOS) ---
    // (Volvemos a hacer esto para guardar CADA item con su precio de promo)
    $productIds = [];
    $quantityMap = [];
    foreach ($cartItems as $item) {
        $productIds[] = (int)$item['id'];
        $quantityMap[(int)$item['id']] = (int)$item['quantity'];
    }

    $productsFromDB = [];
    foreach (array_unique($productIds) as $id) {
        $prod = findProduct((string)$id);
        if (!$prod) throw new Exception("Producto ID $id no encontrado.");
        $productsFromDB[] = $prod;
    }
    
    $productsWithPromos = applyPromotions($productsFromDB);
    
    // Crear un mapa de precios correctos [id => precio_real]
    $priceMap = [];
    foreach ($productsWithPromos as $prod) {
        $priceMap[$prod['id_producto']] = (float)($prod['precio_descuento'] ?? $prod['precio']);
    }

    // --- 3. Insertar el Pedido (padre) ---
    $sqlPedido = <<<SQL
        INSERT INTO pedido (
            id_usuario, id_cupon, precio_subtotal, precio_total, descuento_aplicado, 
            direccion, cod_post, nom_cliente, email_cliente, num_cel, 
            estado_envio
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
        SQL;
    
    $stmtPedido = $db->prepare($sqlPedido);
    
    $params = [
        $userId,
        $cuponId,
        $subtotalConPromos, // Total ANTES de cupón
        $totalFinal,        // Total DESPUÉS de cupón
        $descuentoCupon,    // El valor del cupón
        $formData['direccion'] ?? '',
        $formData['cod_post'] ?? '',
        $formData['nom_cliente'] ?? '',
        $formData['email'] ?? '',
        $formData['num_cel'] ?? '',
    ];
    
    if (!$stmtPedido->execute($params)) {
        $errorInfo = $stmtPedido->errorInfo();
        throw new Exception("Error al guardar pedido: " . ($errorInfo[2] ?? 'Desconocido'));
    }

    $idPedido = $db->lastInsertId();

    // --- 4. Insertar los Items del Pedido (hijos) ---
    $sqlItem = <<<SQL
        INSERT INTO pedido_item(
            id_pedido, id_producto, cantidad, precio_unitario, precio_total) 
        VALUES (?, ?, ?, ?, ?)
    SQL;
    
    $stmtItem = $db->prepare($sqlItem);

    foreach ($cartItems as $item) {
        $productId = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        
        // Usar el precio correcto del mapa que creamos
        $precioUnitario = $priceMap[$productId] ?? 0.0; 
        
        $precioTotalItem = $precioUnitario * $quantity;
        
        if (!$stmtItem->execute([
            $idPedido,
            $productId,
            $quantity,
            $precioUnitario,
            $precioTotalItem
        ])) {
            $errorInfo = $stmtItem->errorInfo();
            throw new Exception("Error al guardar item: " . ($errorInfo[2] ?? 'Desconocido'));
        }
    }

    // 5. Retornar el ID del pedido Y el total calculado
    return [(string)$idPedido, $totalFinal];
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