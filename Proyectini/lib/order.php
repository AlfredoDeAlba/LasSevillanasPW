<?php
declare(strict_types=1);

namespace App\Lib;

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/storage.php'; // Para findProduct

use function App\Lib\getPDO;
use PDO;

/**
 * Busca un cupón válido y devuelve su valor de descuento.
 * ¡CORREGIDO!
 */
function getCouponDiscount(?int $couponId): float
{
    if ($couponId === null) {
        return 0.0;
    }

    try {
        $pdo = getPDO();
        // Corrección: WHERE id_cupon = :id y SELECT valor_descuento
        $stmt = $pdo->prepare("
            SELECT valor_descuento
            FROM cupones
            WHERE id_cupon = :id
              AND activo = 1
              AND NOW() BETWEEN fecha_inicio AND fecha_final
        ");
        $stmt->execute([':id' => $couponId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            return 0.0; // Cupón no encontrado o no válido
        }

        // Asumimos que todos los cupones son de monto fijo
        return (float)$coupon['valor_descuento'];

    } catch (\PDOException $e) {
        error_log($e->getMessage());
        return 0.0; // Error en la BDD, no aplicar descuento
    }
}

/**
 * ¡NUEVO!
 * Carga todas las promociones activas desde la BDD.
 */
function getActivePromotions(): array
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->query("
            SELECT valor_descuento, id_producto_asociado, id_categoria_asociada
            FROM promociones
            WHERE activa = TRUE
              AND NOW() BETWEEN fecha_inicio AND fecha_final
        ");
        $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Crear mapas de búsqueda para eficiencia
        $promoPorProducto = [];
        $promoPorCategoria = [];

        foreach ($promos as $promo) {
            $valor = (float)$promo['valor_descuento'];
            if ($promo['id_producto_asociado']) {
                $promoPorProducto[$promo['id_producto_asociado']] = $valor;
            }
            if ($promo['id_categoria_asociada']) {
                $promoPorCategoria[$promo['id_categoria_asociada']] = $valor;
            }
        }
        
        return [$promoPorProducto, $promoPorCategoria];

    } catch (\PDOException $e) {
        error_log($e->getMessage());
        return [[], []]; // Devuelve mapas vacíos en caso de error
    }
}


/**
 * Calcula el monto total del pedido.
 * ¡CORREGIDO! (Ahora incluye promociones Y cupones)
 */
function calculateOrderAmount(array $items, ?int $couponId): array
{
    // 1. Cargar todas las promociones activas
    [$promoPorProducto, $promoPorCategoria] = getActivePromotions();

    $subtotal = 0.0;
    $discountAmount = 0.0;

    foreach ($items as $item) {
        $productId = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        
        // Usamos findProduct (de storage.php) para obtener el precio Y la categoría
        $product = findProduct((string)$productId); 

        if ($product) {
            $precioOriginal = (float)$product['price'];
            $categoriaId = $product['id_categoria'] ?? null; // Asegúrate de que findProduct devuelva esto

            // Acumular el subtotal
            $subtotal += $precioOriginal * $quantity;

            // 2. Aplicar descuentos de PROMOCIONES (por producto o categoría)
            $descuentoPromocion = 0.0;
            if (isset($promoPorProducto[$productId])) {
                $descuentoPromocion = $promoPorProducto[$productId];
            } elseif ($categoriaId !== null && isset($promoPorCategoria[$categoriaId])) {
                $descuentoPromocion = $promoPorCategoria[$categoriaId];
            }

            // Acumular el descuento de la promoción
            $discountAmount += $descuentoPromocion * $quantity;
        }
    }

    // 3. Aplicar descuento del CUPÓN (al final)
    $discountCoupon = getCouponDiscount($couponId);
    $discountAmount += $discountCoupon;

    // 4. Calcular Total
    $total = $subtotal - $discountAmount;
    
    // Asegurarse de que el total no sea negativo
    if ($total < 0) {
        $total = 0;
        // Opcional: ajustar el descuento para que no sea mayor al subtotal
        $discountAmount = $subtotal;
    }

    return [
        'subtotal' => $subtotal,
        'discountAmount' => $discountAmount,
        'total' => $total,
        'totalInCents' => (int)round($total * 100) // Usar round() para evitar errores de precisión
    ];
}

/**
 * Crea el registro del pedido en la BDD.
 * ¡CORREGIDO!
 */
function createOrder(
    string $name,
    string $email,
    string $phone,
    string $address,
    // Se eliminan $city y $state
    string $zip,
    array $items,
    string $paymentIntentId,
    ?int $userId,
    ?int $couponId
): bool {
    try {
        $pdo = getPDO();

        // 1. Recalcular el total en el backend (¡Fuente de Verdad!)
        //    Esto asegura que el cliente no manipuló los precios.
        $orderData = calculateOrderAmount($items, $couponId);
        $subtotal = $orderData['subtotal'];
        $discountAmount = $orderData['discountAmount'];
        $total = $orderData['total'];

        // Iniciar transacción
        $pdo->beginTransaction();

        // 2. Insertar en la tabla 'pedido' (singular y con columnas correctas)
        $stmt = $pdo->prepare("
            INSERT INTO pedido (
                id_usuario, id_cupon, 
                precio_subtotal, descuento_aplicado, precio_total, 
                pago_completado, estado_envio, 
                direccion, cod_post, nom_cliente, email_cliente, num_cel,
                stripe_payment_id
            ) VALUES (
                :id_usuario, :id_cupon,
                :precio_subtotal, :descuento_aplicado, :precio_total,
                TRUE, 'pendiente',
                :direccion, :cod_post, :nom_cliente, :email_cliente, :num_cel,
                :stripe_payment_id
            )
        ");

        $stmt->execute([
            ':id_usuario' => $userId,
            ':id_cupon' => $couponId,
            ':precio_subtotal' => $subtotal,
            ':descuento_aplicado' => $discountAmount,
            ':precio_total' => $total,
            ':direccion' => $address,
            ':cod_post' => $zip,
            ':nom_cliente' => $name,
            ':email_cliente' => $email,
            ':num_cel' => $phone,
            ':stripe_payment_id' => $paymentIntentId
        ]);

        $orderId = $pdo->lastInsertId();

        // 3. Insertar en la tabla 'pedido_item' (singular)
        $stmt = $pdo->prepare("
            INSERT INTO pedido_item (id_pedido, id_producto, cantidad, precio_unitario)
            VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)
        ");

        foreach ($items as $item) {
            $product = findProduct((string)$item['id']); // Volver a buscar para precio seguro
            if ($product) {
                $stmt->execute([
                    ':id_pedido' => $orderId,
                    ':id_producto' => $item['id'],
                    ':cantidad' => $item['quantity'],
                    ':precio_unitario' => $product['price'] // Usar el precio de la BDD
                ]);
            }
        }
        
        // 4. Completar transacción
        $pdo->commit();
        
        // Guardar el ID del pedido en la sesión para la página de "gracias"
        $_SESSION['last_order_id'] = $orderId;

        return true;

    } catch (\PDOException $e) {
        $pdo->rollBack();
        error_log("Error al crear pedido: " . $e->getMessage());
        throw $e;
        //return false;
    }
}