<?php
declare(strict_types=1);

header('Content-Type: application/json');

// Incluir las bibliotecas
require_once __DIR__ . '/../vendor/autoload.php'; // Stripe
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/storage.php';
require_once __DIR__ . '/lib/order.php'; // ¡Importante!
require_once __DIR__ . '/lib/config.php';

use function App\Lib\getPDO;
use function App\Lib\createOrderInTransaction; // ¡Importante!
use function App\Lib\markOrderAsPaid;

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
$cuponId = isset($data['cuponId']) && $data['cuponId'] ? (int)$data['cuponId'] : null; // ¡NUEVO!
$userId = null; // TODO: Obtener de la sesión

if (!$formData || !$cartItems) {
    sendError('Faltan datos (formulario o carrito).', 400);
}

// 2. Conexión a la DB
$db = getPDO();

try {
    // 3. Iniciar Transacción
    $db->beginTransaction();
    
    // 4. CREAR EL PEDIDO Y CALCULAR EL TOTAL REAL
    // Esta función AHORA hace todo el cálculo y guardado.
    // Devuelve el ID del pedido y el total final que calculó.
    list($idPedido, $totalServidor) = createOrderInTransaction(
        $db, $formData, $cartItems, $userId, $cuponId
    );
    
    // 5. VERIFICAR PAGO CON STRIPE (usando el total que calculamos)
    $totalServidorEnCentavos = (int)round($totalServidor * 100);

    if ($totalServidorEnCentavos > 0) {
        // --- Pedido Pagado ---
        if (!$paymentIntentId) {
            throw new Exception("Falta el ID de pago para un pedido pagado.");
        }
        
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

        if ($paymentIntent->status !== 'succeeded') {
            throw new Exception("El pago no ha sido completado ($paymentIntent->status).");
        }

        // 6. Comparar el total verificado con el total pagado
        if ($paymentIntent->amount !== $totalServidorEnCentavos) {
            // ¡Alerta! El monto pagado no coincide con el calculado
            // Revertimos el pedido que acabamos de crear
            $db->rollBack(); 
            throw new Exception(
                "Error de validación: El monto del pedido ($totalServidorEnCentavos centavos) " .
                "no coincide con el monto pagado ($paymentIntent->amount centavos)."
            );
        }

        // 7. Guardar el ID de Stripe en el pedido
        $stmt = $db->prepare("UPDATE pedido SET stripe_payment_id = ? WHERE id_pedido = ?");
        $stmt->execute([$paymentIntent->id, $idPedido]);

    } else {
        // --- Pedido Gratis (Total 0) ---
        // No se necesita verificar pago, solo asegurarnos de que el ID sea el de "pedido gratis"
        if (strpos($paymentIntentId, 'free_order_') !== 0) {
             $db->rollBack();
             throw new Exception("Error de validación de pedido gratuito.");
        }
    }

    // 8. Marcar como pagado (dispara el trigger de stock)
    markOrderAsPaid($db, $idPedido);
    
    // 9. ¡Éxito! Confirmar la transacción
    $db->commit();
    
    echo json_encode(['success' => true, 'orderId' => $idPedido]);

} catch (Exception $e) {
    // Si algo falló (verificación de pago, guardado, etc.), revertir todo.
    if ($db->inTransaction()) {
        $db->rollback();
    }
    sendError($e->getMessage(), 500);
}