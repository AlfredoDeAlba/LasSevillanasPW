<?php

declare(strict_types=1);

use function App\Lib\deleteProduct;
use function App\Lib\deleteStoredFile;
use function App\Lib\errorResponse;
use function App\Lib\findProduct;
use function App\Lib\jsonResponse;
use function App\Lib\readProducts;
use function App\Lib\requireAuth;
use function App\Lib\storeUploadedFile;
use function App\Lib\upsertProduct;

require_once __DIR__ . '/../lib/storage.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/uploads.php';
require_once __DIR__ . '/../lib/auth.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper((string) $_POST['_method']);
}

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        requireAuth();
        handleCreate();
        break;
    case 'PUT':
        requireAuth();
        handleUpdate();
        break;
    case 'DELETE':
        requireAuth();
        handleDelete();
        break;
    default:
        errorResponse('Método no permitido', 405);
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
    $stock = trim((string) ($input['stock'] ?? ''));
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
        try {
            $payload['image'] = storeUploadedFile($_FILES['productImage']);
        } catch (\Throwable $exception) {
            errorResponse($exception->getMessage());
        }
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
        try {
            $payload['image'] = storeUploadedFile($_FILES['productImage']);
            if (isset($existing['image']) && str_starts_with((string) $existing['image'], 'uploads/')) {
                deleteStoredFile($existing['image']);
            }
        } catch (\Throwable $exception) {
            errorResponse($exception->getMessage());
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
