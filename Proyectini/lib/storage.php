<?php
namespace App\Lib;
use PDO;
require_once __DIR__ . '/db.php';

// Mapea una fila de la tabla `producto` al formato usado en el frontend/API
function mapProductRow(array $row) : array {
    return [
        'id' => isset($row['id_producto']) ? (string) $row['id_producto'] : null,
        'name' => (string) ($row['nombre'] ?? ''),
        'price' => isset($row['precio']) ? (float) $row['precio'] : 0.0,
        'description' => (string) ($row['descripcion'] ?? ''),
        'image' => $row['foto'] ?? null,
        'stock' => isset($row['stock']) ? (int) $row['stock'] : 0,
        'id_categoria' => isset($row['id_categoria']) ? (int) $row['id_categoria'] : null // <-- AÑADIDO
    ];
}

/**
 * Lee todas las categorías de productos desde la BDD.
 */
function readCategories() : array {
    $sql = 'SELECT id_categoria, nombre_categoria 
            FROM producto_categoria 
            ORDER BY nombre_categoria ASC';
    $stmt = getPDO()->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function readProducts() : array {
    $sql = 'SELECT id_producto, nombre, descripcion, stock, precio, foto, id_categoria FROM producto ORDER BY id_producto DESC';
    $stmt = getPDO()->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    return array_map(static fn($r) => mapProductRow($r), $rows);
}

function findProduct(string $id) : ?array {
    $sql = 'SELECT id_producto, nombre, descripcion, stock, precio, foto, id_categoria FROM producto WHERE id_producto = :id LIMIT 1';
    $stmt = getPDO()->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? mapProductRow($row) : null;
}

function upsertProduct(array $payload) : array {
    $id = $payload['id'] ?? null;
    $name = (string) ($payload['name'] ?? '');
    $price = (float) ($payload['price'] ?? 0);
    $description = (string) ($payload['description'] ?? '');
    $stock = (int) ($payload['stock'] ?? 0);
    $image = $payload['image'] ?? null;

    if ($id === null || $id === '' ) {
        $sql = 'INSERT INTO producto (nombre, descripcion, stock, precio, foto) VALUES (:n, :d, :s, :p, :f)';
        $stmt = getPDO()->prepare($sql);
        $stmt->execute([
            ':n' => $name,
            ':d' => $description,
            ':s' => $stock,
            ':p' => $price,
            ':f' => $image,
        ]);
        $newId = (string) getPDO()->lastInsertId();
        return findProduct($newId) ?? [
            'id' => (int) $newId,
            'name' => $name,
            'price' => $price,
            'description' => $description,
            'image' => $image,
            'stock' => $stock,
            'date' => null,
        ];
    }

    $sql = 'UPDATE producto SET nombre = :n, descripcion = :d, stock = :s, precio = :p, foto = :f WHERE id_producto = :id';
    $stmt = getPDO()->prepare($sql);
    $stmt->execute([
        ':n' => $name,
        ':d' => $description,
        ':s' => $stock,
        ':p' => $price,
        ':f' => $image,
        ':id' => $id,
    ]);
    return findProduct((string) $id) ?? [
        'id' => (int) $id,
        'name' => $name,
        'price' => $price,
        'description' => $description,
        'image' => $image,
        'stock' => $stock,
        'date' => null,
    ];
}

function deleteProduct(string $id): void
{
    $stmt = getPDO()->prepare('DELETE FROM producto WHERE id_producto = :id');
    $stmt->execute([':id' => $id]);
}

/**
 * funcion para aplicar descuentos: aplica promociones activas a una lista de productos
 * @param array $products - lista de productos de la bd
 * @return array - lista de productos con precios de descuento aplicados
 */

function applyPromotions(array $products) : array {
    if (empty($products)) {
        return $products;
    }
    try{
        $pdo = getPDO();
        //obtener todas las promociones activas
        $stmt = $pdo->query("
            SELECT id_promocion, valor_descuento, tipo_descuento,
            id_producto_asociado, id_categoria_asociada
            FROM promociones
            WHERE activa = TRUE
                AND NOW() BETWEEN fecha_inicio AND fecha_final
        ");
        $promos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(empty($promos)){
            return $products; //si no hay promociones, mandar los productos tal cual
        }
        //crear mapas de busqueda para mejorar eficiencia
        $promoPorProducto = [];
        $promoPorCategoria = [];
        foreach($promos as $promo){
            //normalizar los datos
            $promo['valor_descuento'] = (float)($promo['valor_descuento'] ?? 0);
            $promo['tipo_descuento'] = $promo['tipo_descuento'] ?? 'fijo';
            if($promo['id_producto_asociado']){
                $promoPorProducto[$promo['id_producto_asociado']] = $promo;
            }elseif($promo['id_categoria_asociada']){
                $promoPorCategoria[$promo['id_categoria_asociada']] = $promo;
            }
        }
        //iterar sobre los productos y se aplican descuentos
        foreach($products as &$product){//uso de & para modificar el arreglo original
            $precioOriginal = (float)($product['precio'] ?? 0.0);
            $descuento = 0.0;
            $promoAplicada = null;
            // Obtenemos los IDs de forma segura
            $productId = $product['id_producto'] ?? null;
            $categoryId = $product['id_categoria'] ?? null;

            //prioridad a promocion por producto
            if($productId !== null && isset($promoPorProducto[$productId])){
                $descuento = $promoPorProducto[$productId];
            
            //busqueda de promocion por categorias
            }elseif($categoryId !== null && isset($promoPorCategoria[$categoryId])){
                $descuento = $promoPorCategoria[$categoryId];
            }
            // Aplicar el descuento si encontramos una promoción
            if ($promoAplicada !== null) {
                $valor = $promoAplicada['valor_descuento'];
                $tipo = $promoAplicada['tipo_descuento'];

                if ($tipo === 'porcentaje') {
                    // Cálculo de porcentaje
                    $descuento = $precioOriginal * ($valor / 100);
                } else {
                    // Cálculo fijo (default)
                    $descuento = $valor;
                }
                
                // Aplicar el descuento calculado, asegurando que no sea negativo
                $product['precio_original'] = $precioOriginal;
                $product['precio_descuento'] = max(0.01, $precioOriginal - $descuento);
            }
        }
        return $products;
    }catch(\Throwable $e){
        error_log("error al aplicar las promociones: " . $e->getMessage());
        return $products;
    }
}

 /**
 * Obtiene productos recomendados para un usuario basado en las categorías
 * de sus compras recientes.
 *
 * @param int $userId ID del usuario logueado
 * @param int $limit Número de productos a recomendar
 * @return array Lista de productos
 */
function getRecommendedProductsForUser(int $userId, int $limit = 5): array {
    $pdo = getPDO();

    try {
        // Query completa que busca productos en categorías que el usuario compró
        // PERO que el usuario no ha comprado todavía
        $sql = "
            SELECT DISTINCT
                p.id_producto,
                p.nombre,
                p.descripcion,
                p.stock,
                p.precio,
                p.foto,
                p.id_categoria
            FROM producto p
            WHERE 
                -- 1. El producto debe pertenecer a una categoría que el usuario haya comprado
                p.id_categoria IN (
                    SELECT DISTINCT p_cat.id_categoria
                    FROM pedido pe_cat
                    JOIN pedido_item pi_cat ON pe_cat.id_pedido = pi_cat.id_pedido
                    JOIN producto p_cat ON pi_cat.id_producto = p_cat.id_producto
                    WHERE pe_cat.id_usuario = ?
                      AND pe_cat.pago_completado = TRUE
                      AND p_cat.id_categoria IS NOT NULL
                )
            AND 
                -- 2. El producto no debe ser uno que el usuario ya haya comprado
                p.id_producto NOT IN (
                    SELECT DISTINCT pi_ex.id_producto
                    FROM pedido_item pi_ex
                    JOIN pedido pe_ex ON pi_ex.id_pedido = pe_ex.id_pedido
                    WHERE pe_ex.id_usuario = ?
                )
            AND
                -- 3. Solo productos con stock disponible
                p.stock > 0
            -- 4. Ordenar aleatoriamente y limitar
            ORDER BY RAND()
            LIMIT ?
        ";

        $stmt = $pdo->prepare($sql);
        
        // IMPORTANTE: Bind parameters en el orden correcto
        // Primer ? = userId (para la subconsulta IN)
        // Segundo ? = userId (para la subconsulta NOT IN)
        // Tercer ? = limit
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, \PDO::PARAM_INT);
        
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        
        // Mapear los resultados al formato de producto estándar
        return array_map(static fn($r) => mapProductRow($r), $rows);
        
    } catch (\PDOException $e) {
        // Log the error for debugging
        error_log("Error in getRecommendedProductsForUser: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Return empty array instead of throwing
        return [];
    }
}

