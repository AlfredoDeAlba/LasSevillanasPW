<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/auth_usr.php';
use function App\Lib\initiatePasswordReset;
use function App\Lib\startSecureSession;

startSecureSession();

$message = null;
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    try {
        initiatePasswordReset($email);
        // Por seguridad, siempre mostramos un mensaje genérico
        $message = 'Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.';
    } catch (\Exception $e) {
        $message = 'Ocurrió un error. Por favor, intenta de nuevo más tarde.';
        $isError = true;
    }
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body { display: grid; place-items: center; min-height: 100vh; }
        .login-card { max-width: 400px; width: 100%; padding: 24px; border: 1px solid var(--color-border); border-radius: var(--radius-base); background: var(--color-surface); box-shadow: var(--shadow-soft); }
        .login-card h1 { margin-top: 0; text-align: center; }
        .login-card form { display: grid; gap: var(--space-md); }
        .message { font-weight: 500; text-align:center; }
        .message.success { color: green; }
        .message.error { color: var(--color-error); }
        .form-footer { font-size: 0.9rem; text-align: center; margin-top: var(--space-md); }
    </style>
</head>
<body data-theme="light">
    <main>
        <div class="login-card">
            <h1>Recuperar Contraseña</h1>
            
            <?php if ($message): ?>
                <p class="message <?php echo $isError ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <form method="post">
                <div class="form-field">
                    <label for="email">Ingresa tu correo electrónico</label>
                    <input id="email" name="email" type="email" required>
                </div>
                <button class="primary" type="submit">Enviar enlace</button>
            </form>
            <div class="form-footer">
                <p><a href="login.php">Volver a Iniciar Sesión</a></p>
            </div>
        </div>
    </main>
</body>
</html>