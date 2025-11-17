<?php

declare(strict_types=1);

namespace App\Lib\Products;

use PDO;
use PDOException;

// Importamos todas las funciones que usará el script
use function App\Lib\deleteProduct;
use function App\Lib\deleteStoredFile;
use function App\Lib\errorResponse;
use function App\Lib\findProduct;
use function App\Lib\jsonResponse;
use function App\Lib\readProducts;
use function App\Lib\storeUploadedFile;
use function App\Lib\upsertProduct;
use function App\Lib\applyPromotions;
use function App\Lib\getPDO;

// Requerimos los archivos que contienen esas funciones
require_once __DIR__ . '/../lib/storage.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/uploads.php';
require_once __DIR__ . '/../lib/auth.php'; // Este es el auth de Admin
require_once __DIR__ . '/../lib/db.php';
// --- INICIO DE LA LÓGICA ---

// Proteger el API: Solo administradores logueados pueden usar esto
// (Importante: 'auth.php' debe usar el namespace App\Lib\Admin)


$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper((string) $_POST['_method']);
}

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handleCreate();
            break;
        case 'PUT':
            handleUpdate();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            errorResponse('Método no permitido', 405);
    }
} catch (\Throwable $e) {
    // esto lo atrapará y enviará un JSON válido.
    error_log($e->getMessage()); // Guarda el error real en el log del servidor
    errorResponse('Ocurrió un error inesperado en el servidor.', 500);
}


function handleGet() : void {
    $id = $_GET['id'] ?? null;
    if($id){
        $product = findProduct($id);
        if(!$product){
            errorResponse('Producto no encontrado', 404);
        }
        //$productConDescuento = applyPromotions([$product]); //ahora le pasamos la funcion de aplicar promos primero
        jsonResponse(['data' => $product]);
    }else{ //obtener todos los productos
        $products = readProducts();
        //$productConDescuento = applyPromotions($products);
        jsonResponse(['data' => $products]);
    }
}

function normalizeInput(array $input) : array {
    $name = trim((string) ($input['name'] ?? ''));
    $price = (float) ($input['price'] ?? 0);
    $description = trim((string) ($input['description'] ?? ''));
    $stock = (int) ($input['stock'] ?? 0); 
    $id = $input['id'] ?? null;

    if($name === ''){
        errorResponse('El nombre es obligatorio.');
    }
    if($price <= 0){
        errorResponse('El precio debe ser mayor que 0.');
    }

    return [
        'id' => $id,
        'name' => $name,
        'price' => $price,
        'description' => $description,
        'stock' => $stock
    ];
}

function handleCreate() : void {
    //App\Lib\Admin\requireAuth();
    $payload = normalizeInput($_POST);

    if(!empty($_FILES['productImage']['name'] ?? '')){
        $payload['image'] = storeUploadedFile($_FILES['productImage']);
    }else{
        $payload['image'] = null;
    }

    $product = upsertProduct($payload);
    jsonResponse(['message' => 'Producto creado', 'data' => $product], 201);
}

function handleUpdate() : void {
    $data = $_POST;
    if(empty($data)){
        parse_str(file_get_contents('php://input') ?: '', $data);
    }

    $id = $data['id'] ?? ($_GET['id'] ?? null);
    if(!$id){
        errorResponse('Falta el identificador del producto.');
    }

    $existing = findProduct($id);
    if(!$existing){
        errorResponse('Producto no encontrado.', 404);
    }

    $data['id'] = $id;
    $payload = normalizeInput($data); 
    $payload['image'] = $existing['image'] ?? null;

    if(!empty($_FILES['productImage']['name'] ?? '')){
        $payload['image'] = storeUploadedFile($_FILES['productImage']);
        if(isset($existing['image']) && str_starts_with((string) $existing['image'], 'uploads/')){
            deleteStoredFile($existing['image']);
        }
    }

    $product = upsertProduct($payload);
    jsonResponse(['message' => 'Producto actualizado', 'data' => $product]);
}

function handleDelete() : void {
    $id = $_GET['id'] ?? null;
    if(!$id){
        parse_str(file_get_contents('php://input') ?: '', $parsed);
        $id = $parsed['id'] ?? null;
    }

    if(!$id){
        errorResponse('Falta el identificador del producto.');
    }

    $existing = findProduct($id);
    if(!$existing){
        errorResponse('Producto no encontrado.', 404);
    }

    deleteProduct($id);
    if(isset($existing['image']) && str_starts_with((string) $existing['image'], 'uploads/')){
        deleteStoredFile($existing['image']);
    }

    jsonResponse(['message' => 'Producto eliminado']);
}

/**
 * Get active promotion for a product
 * Returns promotion data if product has an active promotion
 */
function getProductPromotion($productId, $categoryId = null){
    $pdo = getPDO();
    //primero se busca si hay alguna promocion relacionada al producto en si
    $stmt = $pdo->prepare("
    SELECT
        promo.id_promocion,
        promo.nombre_promo,
        promo.descripcion,
        promo.valor_descuento,
        promo.tipo_descuento,
        promo.fecha_final,
        'product' as promo_type
    FROM promociones promo
    WHERE promo.id_producto_asociado = :product_id
        AND promo.activa = TRUE
        AND NOW() BETWEEN promo.fecha_inicio AND promo.fecha_final
    LIMIT 1
    ");
    $stmt->execute(['product_id'=>$productId]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);
    if($promo){return $promo;}

    if($categoryId){
        //sino la busca por categoria a la que este asociada
        $stmt = $pdo->prepare("
            SELECT
                promo.id_promocion,
                promo.nombre_promo,
                promo.descripcion,
                promo.valor_descuento,
                promo.tipo_descuento,
                promo.fecha_final,
                'category' as promo_type
            FROM promociones promo
            WHERE promo.id_categoria_asociada = :category_id
                AND promo.activa = TRUE
                AND NOW() BETWEEN promo.fecha_inicio AND promo.fecha_final
            LIMIT 1
        ");
        $stmt->execute(['category_id'=>$categoryId]);
        $promo = $stmt->fetch(PDO::FETCH_ASSOC);
        if($promo){return $promo;}
    }
    return null;
}

/**
 * Calcula el precio descontado en base a la promocion y a su tipo
 */
function calculateDiscountedPrice($originalPrice, $promotion){
    if(!$promotion){return $originalPrice;}

    if($promotion['tipo_descuento'] === 'porcentaje'){
        $discount = ($originalPrice * $promotion['valor_descuento'])/100;
        return max(0, $originalPrice - $discount);
    }else{
        return max(0, $originalPrice - $promotion['valor_descuento']);
    }
}

/**
 * Se le agrega al product la data de la promocion
 */
function addPromotionToAllowedProduct($product){
    $promotion = getProductPromotion(
        $product['id'],
        $product['id_categoria'] ?? null
    );
    if($promotion){
        $product['promotion'] = $promotion;
        $product['original_price'] = $product['price'];
        $product['price'] = calculateDiscountedPrice($product['price'], $promotion);
        $product['has_promotion'] = true;
        $product['discount_amount'] = $product['original_price'] - $product['price'];
        $product['discount_percentage'] = round(($product['discount_amount'] / $product['original_price']) * 100);
    }else{
        $product['has_promotion'] = false;
    }
    return $product;
}

/**
 * Obtiene todos los productos con la data de promocion necesaria
 */
function getAllProductsWithPromotions(){
    $pdo = getPDO();
    $stmt = $pdo->query("
        SELECT 
            p.id_producto as id,
            p.nombre as name,
            p.descripcion as description,
            p.precio as price,
            p.stock,
            p.foto as image,
            p.id_categoria,
            c.nombre_categoria as category_name
        FROM producto p
        LEFT JOIN producto_categoria c ON p.id_categoria = c.id_categoria
        WHERE p.disponible = TRUE
        ORDER BY p.nombre
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map('App\Lib\Products\addPromotionToAllowedProduct', $products);

}
/**
 * Obtener un solo producto con la data de promocion
 */
function getProductWithPromotion($productId) {
    $product = findProduct($productId);
    
    if (!$product) {
        return null;
    }
    
    return addPromotionToAllowedProduct($product);
}