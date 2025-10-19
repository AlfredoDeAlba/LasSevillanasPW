<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth_usr.php';

use function App\Lib\verifyResetToken;
use function App\Lib\resetPasswordWithToken;
use function App\Lib\startSecureSession;

startSecureSession();

$token = $_GET['token'] ?? null;
$message = null;
$isError = false;
$isValidToken = false;
$userId = null;

if (!$token) {
    $message = 'Token no proporcionado.';
    $isError = true;
} else {
    $userId = verifyResetToken($token);
    if (!$userId) {
        $message = 'El enlace no es válido o ha expirado. Por favor, solicita uno nuevo.';
        $isError = true;
    } else {
        // El token es válido, mostrar el formulario
        $isValidToken = true;
    }
}

if ($isValidToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_passwd = $_POST['new_password'] ?? '';
    $confirm_passwd = $_POST['confirm_password'] ?? '';

    if (empty($new_passwd) || empty($confirm_passwd)) {
        $message = 'Todos los campos de contraseña son obligatorios.';
        $isError = true;
    } elseif ($new_passwd !== $confirm_passwd) {
        $message = 'Las nuevas contraseñas no coinciden.';
        $isError = true;
    } elseif (strlen($new_passwd) < 8) {
        $message = 'La nueva contraseña debe tener al menos 8 caracteres.';
        $isError = true;
    } elseif (resetPasswordWithToken($userId, $new_passwd)) {
        $message = '¡Contraseña cambiada con éxito! Ya puedes iniciar sesión.';
        $isValidToken = false; // Ocultar el formulario después del éxito
    } else {
        $message = 'Error: No se pudo actualizar la contraseña.';
        $isError = true;
    }
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
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
            <h1>Crear Nueva Contraseña</h1>

            <?php if ($message): ?>
                <p class="message <?php echo $isError ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <?php if ($isValidToken): ?>
                <form method="POST">
                    <div class="form-field">
                        <label for="new_password">Nueva Contraseña</label>
                        <input id="new_password" name="new_password" type="password" required>
                    </div>
                    <div class="form-field">
                        <label for="confirm_password">Confirmar Nueva Contraseña</label>
                        <input id="confirm_password" name="confirm_password" type="password" required>
                    </div>
                    <button type="submit" class="primary">Restablecer Contraseña</button>
                </form>
            <?php endif; ?>

            <div class="form-footer">
                <p><a href="login.php">Volver a Iniciar Sesión</a></p>
            </div>
        </div>
    </main>
</body>
</html>