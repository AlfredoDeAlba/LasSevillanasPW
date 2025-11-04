<?php
declare(strict_types=1);

// Carga las bibliotecas
require_once __DIR__ . '/../vendor/autoload.php'; // Carga Stripe
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/storage.php';
require_once __DIR__ . '/lib/order.php'; // ¡Importante!
require_once __DIR__ . '/lib/config.php';

use function App\Lib\calculateServerTotal; // ¡Importante!

// Función de utilidad
function sendJsonError(string $message): void {
    http_response_code(400);
    echo json_encode(['error' => $message]);
    exit;
}

header('Content-Type: application/json');

// *** LLAVE SECRETA ***
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

// 1. Recibir el carrito y cupón del cliente
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$cartItems = $data['cartItems'] ?? null;
$cuponId = isset($data['cuponId']) && $data['cuponId'] ? (int)$data['cuponId'] : null; // ¡NUEVO!

if (empty($cartItems)) {
    sendJsonError('Carrito vacío.');
}

// 2. Calcular el total del LADO DEL SERVIDOR (¡Forma correcta!)
try {
    // ¡Usamos la función de order.php!
    list($totalFinal, $subtotal, $descuento) = calculateServerTotal($cartItems, $cuponId);

} catch (Exception $e) {
    sendJsonError($e->getMessage());
}

// 3. Crear el Payment Intent en Stripe
// Stripe requiere el monto en la unidad más pequeña (centavos)
$totalEnCentavos = (int)round($totalFinal * 100);

// Stripe no permite crear intentos de pago por 0 o menos.
// (Manejo de pedidos gratis)
if ($totalEnCentavos < 1000) { // Asumiendo un mínimo de 10.00 MXN para pago
     // Si es gratis, devolvemos un 'secret' especial
    if ($totalFinal == 0) {
        echo json_encode([
            'clientSecret' => 'free_order_'. uniqid(), // Un ID único para pedidos gratis
            'total' => 0
        ]);
        exit;
    }
    // Si es menos del mínimo pero no es 0, es un error
    sendJsonError("El monto mínimo de pago es $10.00 MXN. Tu total es $totalFinal MXN.");
}


try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $totalEnCentavos,
        'currency' => 'mxn', // Moneda (Pesos Mexicanos)
        'automatic_payment_methods' => ['enabled' => true],
    ]);

    // 4. Enviar el "client secret" de vuelta al frontend
    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret,
        'total' => $totalFinal 
    ]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    sendJsonError('Error al crear la intención de pago: ' . $e->getMessage());
}