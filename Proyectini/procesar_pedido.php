<?php
declare(strict_types=1);

header('Content-Type: application/json');

// Incluir las bibliotecas
require_once __DIR__ . '/../vendor/autoload.php'; // Stripe
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/storage.php';
require_once __DIR__ . '/lib/order.php'; // Nuestro nuevo archivo
require_once __DIR__ . '/lib/config.php';

use function App\Lib\getPDO;
use function App\Lib\createOrderInTransaction;
use function App\Lib\markOrderAsPaid;
use function App\Lib\findProduct;

// Función para enviar respuesta de error
function sendError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Método no permitido.', 405);
}

// 1. Obtener y decodificar los datos
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$formData = $data['formData'] ?? null;
$cartItems = $data['cartItems'] ?? null;
$paymentIntentId = $data['paymentIntentId'] ?? null; // ID del pago exitoso
$discount = floatval($data['discount'] ?? 0);
$userId = null; // TODO: Obtener de la sesión
$cuponId = null; // TODO: Obtener de la validación

if (!$formData || !$cartItems || !$paymentIntentId) {
    sendJsonError('Faltan datos (formulario, carrito o ID de pago).');
}

// 2. Conexión a la DB
$db = getPDO();

try {
    // 3. Verificar el Payment Intent con Stripe
    // *** LLAVE SECRETA ***
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    
    $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

    if ($paymentIntent->status !== 'succeeded') {
        throw new Exception("El pago no ha sido completado ($paymentIntent->status).");
    }

    // 4. Recalcular el total del LADO DEL SERVIDOR (Verificación de seguridad)
    $totalServidor = 0;
    foreach ($cartItems as $item) {
        $product = findProduct((string)$item['id']);
        if (!$product) throw new Exception('Producto no encontrado durante la verificación.');
        $totalServidor += floatval($product['price']) * intval($item['quantity']);
    }
    // TODO: Restar $discount aquí
    $totalServidorEnCentavos = (int)($totalServidor * 100);

    // 5. Comparar el total verificado con el total pagado
    if ($paymentIntent->amount !== $totalServidorEnCentavos) {
        // ¡Alerta de fraude! Reembolsar y registrar.
        // Por ahora, solo estaremos lanzando un error.
        throw new Exception(
            "Error de validación: El monto del pedido ($totalServidorEnCentavos) " .
            "no coincide con el monto pagado ($paymentIntent->amount)."
        );
    }

    // 6. ¡Todo es correcto! Guardar en la Base de Datos
    $db->beginTransaction();
    
    // (Esta función la creamos en la respuesta anterior)
    $idPedido = createOrderInTransaction(
        $db, $formData, $cartItems, $discount, $userId, $cuponId
    );
    
    // (Esta función la creamos en la respuesta anterior)
    markOrderAsPaid($db, $idPedido);
    
    // Guardar el ID de Stripe en el pedido
    $stmt = $db->prepare("UPDATE pedido SET stripe_payment_id = ? WHERE id_pedido = ?");
    $stmt->execute([$paymentIntent->id, $idPedido]);

    $db->commit();
    
    echo json_encode(['success' => true, 'orderId' => $idPedido]);

} catch (Exception $e) {
    $db->rollback();
    sendJsonError($e->getMessage(), 500);
}