<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth_usr.php';

use function App\Lib\verifyAccount;

$token = $_GET['token'] ?? '';
$message = '';
$isSuccess = false;

if (!empty($token)) {
    if (verifyAccount($token)) {
        $message = "¡Tu cuenta ha sido activada con éxito! Ya puedes iniciar sesión.";
        $isSuccess = true;
    } else {
        $message = "El enlace de verificación no es válido o ya ha sido utilizado.";
    }
} else {
    $message = "No se proporcionó un token de verificación.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Verificación de Cuenta</title>
    <link rel="stylesheet" href="../styles.css">
    </head>
<body data-theme="light">
    <main>
        <div class="login-card">
            <h1>Verificación de Cuenta</h1>
            <p style="text-align:center; font-size: 1.1rem; color: <?php echo $isSuccess ? '#2e7d32' : 'var(--color-error)'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <?php if ($isSuccess): ?>
                <a href="login.php" class="primary" style="display:block; text-align:center; text-decoration: none; padding: 12px;">Ir a Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>