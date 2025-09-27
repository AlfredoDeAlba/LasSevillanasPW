<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

use function App\Lib\attemptLogin;
use function App\Lib\isLoggedIn;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (attemptLogin($user, $password)) {
        header('Location: index.php');
        exit;
    }
    $error = 'Credenciales incorrectas.';
} elseif (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrativo - Iniciar sesión</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body { display: grid; place-items: center; min-height: 100vh; }
        .login-card { max-width: 360px; width: 100%; padding: 24px; border: 1px solid var(--color-border); border-radius: var(--radius-base); background: var(--color-surface); box-shadow: var(--shadow-soft); }
        .login-card h1 { margin-top: 0; text-align: center; }
        .login-card form { display: grid; gap: var(--space-md); }
        .error { color: var(--color-error); font-weight: 500; }
    </style>
</head>
<body data-theme="light">
    <main class="login-card">
        <h1>Administración</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="form-field">
                <label for="username">Usuario</label>
                <input id="username" name="username" type="text" required>
            </div>
            <div class="form-field">
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button class="primary" type="submit">Entrar</button>
        </form>
        <p style="text-align:center; margin-top: var(--space-md);"><a href="../index.php">Volver al sitio</a></p>
    </main>
</body>
</html>
