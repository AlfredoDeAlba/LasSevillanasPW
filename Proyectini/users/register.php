<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth_usr.php';

use function App\Lib\registerUser;
use function App\Lib\startSecureSession;

startSecureSession();

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($password !== $password2) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo electrónico no es válido.";
    } else {
        try {
            if (registerUser($nombre, $apellido, $email, $password)) {
                $success = true;
            } else {
                $error = "No se pudo enviar el correo de verificación. Inténtalo más tarde.";
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regístrate</title>
    <link rel="stylesheet" href="../styles.css">
    </head>
<body data-theme="light">
    <main>
        <div class="login-card" style="max-width: 420px;">
            <h1>Crea tu cuenta</h1>
            
            <?php if ($success): ?>
                <div style="padding: 20px; background: #e8f5e9; color: #2e7d32; border-radius: var(--radius-base); text-align: center;">
                    <h3>¡Registro exitoso!</h3>
                    <p>Hemos enviado un correo de verificación a <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
                    <p>Por favor, revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.</p>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <div class="form-field">
                        <label for="nombre">Nombre</label>
                        <input id="nombre" name="nombre" type="text" required>
                    </div>
                    <div class="form-field">
                        <label for="apellido">Apellidos</label>
                        <input id="apellido" name="apellido" type="text" required>
                    </div>
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required>
                    </div>
                    <div class="form-field">
                        <label for="password">Contraseña</label>
                        <input id="password" name="password" type="password" required>
                    </div>
                    <div class="form-field">
                        <label for="password2">Repetir Contraseña</label>
                        <input id="password2" name="password2" type="password" required>
                    </div>
                    <button class="primary" type="submit">Registrarme</button>
                </form>
            <?php endif; ?>
            <p style="text-align:center; margin-top: var(--space-md);"><a href="../index.php">Volver al sitio</a></p>
        </div>
    </main>
</body>
</html>