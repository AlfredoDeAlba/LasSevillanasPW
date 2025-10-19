<?php
declare(strict_types=1);

// Carga las bibliotecas
require_once __DIR__ . '/../vendor/autoload.php'; // Carga Stripe
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/storage.php';
require_once __DIR__ . '/lib/config.php';

use function App\Lib\findProduct;

// Función de utilidad
function sendJsonError(string $message): void {
    http_response_code(400);
    echo json_encode(['error' => $message]);
    exit;
}

header('Content-Type: application/json');

// *** LLAVE SECRETA ***
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

// 1. Recibir el carrito del cliente
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$cartItems = $data['cartItems'] ?? null;

if (empty($cartItems)) {
    sendJsonError('Carrito vacío.');
}

// 2. Calcular el total del LADO DEL SERVIDOR (¡Muy importante!)
$total = 0;
try {
    foreach ($cartItems as $item) {
        $product = findProduct((string)$item['id']);
        if (!$product) {
            throw new Exception('Producto no encontrado.');
        }
        $total += floatval($product['price']) * intval($item['quantity']);
    }
    // **Aplicar descuentos/cupones aquí**

} catch (Exception $e) {
    sendJsonError($e->getMessage());
}

if ($total <= 0) {
    sendJsonError('El total del pedido es inválido.');
}

// 3. Crear el Payment Intent en Stripe
// Stripe requiere el monto en la unidad más pequeña (centavos)
$totalEnCentavos = (int)($total * 100);

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $totalEnCentavos,
        'currency' => 'mxn', // Moneda (Pesos Mexicanos)
        'automatic_payment_methods' => ['enabled' => true],
        // 'description' => 'Pedido de Dulces Las Sevillanas'
    ]);

    // 4. Enviar el "client secret" de vuelta al frontend
    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret,
        'total' => $total 
    ]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    sendJsonError('Error al crear la intención de pago: ' . $e->getMessage());
}