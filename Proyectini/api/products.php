<?php

declare(strict_types=1);

// Importamos todas las funciones que usará el script
use function App\Lib\deleteProduct;
use function App\Lib\deleteStoredFile;
use function App\Lib\errorResponse;
use function App\Lib\findProduct;
use function App\Lib\jsonResponse;
use function App\Lib\readProducts;
use function App\Lib\requireAuth;
use function App\Lib\storeUploadedFile;
use function App\Lib\upsertProduct;

// Requerimos los archivos que contienen esas funciones
require_once __DIR__ . '/../lib/storage.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/uploads.php';
require_once __DIR__ . '/../lib/auth.php'; // Este es el auth de Admin

// --- INICIO DE LA LÓGICA ---

// Proteger el API: Solo administradores logueados pueden usar esto
// (Importante: 'auth.php' debe usar el namespace App\Lib\Admin)
try {
    App\Lib\Admin\requireAuth(); 
} catch (\Throwable $e) {
    errorResponse('Acceso no autorizado', 401);
}


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
    // CAPTURA GLOBAL DE ERRORES:
    // Si algo falla (ej. error de BD en storage.php),
    // esto lo atrapará y enviará un JSON válido.
    error_log($e->getMessage()); // Guarda el error real en el log del servidor
    errorResponse('Ocurrió un error inesperado en el servidor.', 500);
}


function handleGet(): void
{
    $id = $_GET['id'] ?? null;
    if ($id) {
        $product = findProduct($id);
        if (!$product) {
            errorResponse('Producto no encontrado', 404);
        }
        jsonResponse(['data' => $product]);
    }

    $products = readProducts();
    jsonResponse(['data' => $products]);
}

function normalizeInput(array $input): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $price = (float) ($input['price'] ?? 0);
    $description = trim((string) ($input['description'] ?? ''));
    $stock = (int) ($input['stock'] ?? 0); 
    $id = $input['id'] ?? null;

    if ($name === '') {
        errorResponse('El nombre es obligatorio.');
    }
    if ($price <= 0) {
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

function handleCreate(): void
{
    $payload = normalizeInput($_POST);

    if (!empty($_FILES['productImage']['name'] ?? '')) {
        $payload['image'] = storeUploadedFile($_FILES['productImage']);
    } else {
        $payload['image'] = null;
    }

    $product = upsertProduct($payload);
    jsonResponse(['message' => 'Producto creado', 'data' => $product], 201);
}

function handleUpdate(): void
{
    $data = $_POST;
    if (empty($data)) {
        parse_str(file_get_contents('php://input') ?: '', $data);
    }

    $id = $data['id'] ?? ($_GET['id'] ?? null);
    if (!$id) {
        errorResponse('Falta el identificador del producto.');
    }

    $existing = findProduct($id);
    if (!$existing) {
        errorResponse('Producto no encontrado.', 404);
    }

    $data['id'] = $id;
    $payload = normalizeInput($data); 
    $payload['image'] = $existing['image'] ?? null;

    if (!empty($_FILES['productImage']['name'] ?? '')) {
        $payload['image'] = storeUploadedFile($_FILES['productImage']);
        if (isset($existing['image']) && str_starts_with((string) $existing['image'], 'uploads/')) {
            deleteStoredFile($existing['image']);
        }
    }

    $product = upsertProduct($payload);
    jsonResponse(['message' => 'Producto actualizado', 'data' => $product]);
}

function handleDelete(): void
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        parse_str(file_get_contents('php://input') ?: '', $parsed);
        $id = $parsed['id'] ?? null;
    }

    if (!$id) {
        errorResponse('Falta el identificador del producto.');
    }

    $existing = findProduct($id);
    if (!$existing) {
        errorResponse('Producto no encontrado.', 404);
    }

    deleteProduct($id);
    if (isset($existing['image']) && str_starts_with((string) $existing['image'], 'uploads/')) {
        deleteStoredFile($existing['image']);
    }

    jsonResponse(['message' => 'Producto eliminado']);
}