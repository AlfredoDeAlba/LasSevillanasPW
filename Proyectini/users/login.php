<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/auth_usr.php';
use function App\Lib\attemptLogin;
use function App\Lib\isLoggedIn;
use function App\Lib\startSecureSession;

startSecureSession();

$error = null;

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (attemptLogin($email, $password)) {
        header('Location: ../index.php');
        exit;
    }
    $error = 'Credenciales incorrectas o cuenta no verificada.';
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identifícate Usuario</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body { display: grid; place-items: center; min-height: 100vh; }
        .login-card { max-width: 400px; width: 100%; padding: 24px; border: 1px solid var(--color-border); border-radius: var(--radius-base); background: var(--color-surface); box-shadow: var(--shadow-soft); }
        .login-card h1 { margin-top: 0; text-align: center; }
        .login-card form { display: grid; gap: var(--space-md); }
        .error { color: var(--color-error); font-weight: 500; text-align:center; }
        .form-footer { font-size: 0.9rem; text-align: center; margin-top: var(--space-md); }
    </style>
</head>
<body data-theme="light">
    <main>
        <div class="login-card">
            <h1>Inicia Sesión</h1>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required>
                </div>
                <div class="form-field">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" required>
                </div>
                <button class="primary" type="submit">Entrar</button>
            </form>
            <div class="form-footer">
                <p><a href="forgot_password.php">¿Olvidaste tu contraseña?</a></p>
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                <p style="margin-top: var(--space-lg);"><a href="../index.php">Volver al sitio</a></p>
            </div>
        </div>
    </main>
</body>
</html>