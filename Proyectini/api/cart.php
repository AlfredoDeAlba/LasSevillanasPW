<?php

declare(strict_types=1);

use function App\Lib\createCartItem;
use function App\Lib\errorResponse;
use function App\Lib\findCartItem;
use function App\Lib\jsonResponse;

require_once __DIR__ . '/../lib/cart.php';
require_once __DIR__ . '/../lib/response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    default:
        errorResponse('Método no permitido', 405);
}

function handleGet(): void
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        errorResponse('Falta el identificador del carrito.', 400);
    }

    $cart = findCartItem((string) $id);
    if (!$cart) {
        errorResponse('Carrito no encontrado.', 404);
    }

    jsonResponse(['data' => $cart]);
}

function handlePost(): void
{
    $productId = $_POST['id_producto'] ?? null;
    $quantity = (int) ($_POST['cantidad'] ?? 0);

    if (!$productId) {
        errorResponse('Falta el identificador del producto.');
    }

    try {
        $cart = createCartItem((string) $productId, $quantity);
    } catch (\Throwable $exception) {
        errorResponse($exception->getMessage());
    }

    jsonResponse(['message' => 'Producto agregado al carrito.', 'data' => $cart], 201);
}

