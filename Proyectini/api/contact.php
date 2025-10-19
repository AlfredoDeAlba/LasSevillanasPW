<?php
declare(strict_types=1);

// Establecer cabecera de respuesta JSON
header('Content-Type: application/json');

// Cargar la configuración (para .env) y la función de correo
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/auth_usr.php';

use function App\Lib\sendEmail;

// 1. Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

// 2. Leer los datos JSON enviados desde JavaScript
$input = json_decode(file_get_contents('php://input'), true);

// 3. Sanitizar y validar los datos
$name = trim($input['nombre'] ?? '');
$email = filter_var(trim($input['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
$message = trim($input['mensaje'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Por favor, completa todos los campos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Por favor, ingresa un correo electrónico válido.']);
    exit;
}

// 4. Preparar el correo
$to = $_ENV['MAIL_USERNAME']; 
$subject = "Nuevo Mensaje de Contacto de: " . $name;

// Construir un cuerpo de correo en HTML
$body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
        <h2>Has recibido un nuevo mensaje desde tu sitio web</h2>
        <p><strong>Nombre:</strong> " . htmlspecialchars($name) . "</p>
        <p><strong>Correo (para responder):</strong> " . htmlspecialchars($email) . "</p>
        <hr style='border: 0; border-top: 1px solid #eee;'>
        <h3>Mensaje:</h3>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
    </body>
    </html>
";

// 5. Enviar el correo
try {
    $emailSent = sendEmail($to, $subject, $body);

    if ($emailSent) {
        echo json_encode(['success' => true, 'message' => '¡Mensaje enviado con éxito! Gracias por contactarnos.']);
    } else {
        throw new Exception('PHPMailer falló al enviar el correo.');
    }

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log('Error al enviar correo de contacto: ' . $e->getMessage()); 
    // Enviar un mensaje genérico al usuario
    echo json_encode(['error' => 'No se pudo enviar el mensaje en este momento. Por favor, intenta de nuevo más tarde.']);
}
?>