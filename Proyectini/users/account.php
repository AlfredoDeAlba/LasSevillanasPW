<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/auth_usr.php';
// ... más includes y lógica para obtener y actualizar datos del usuario

use function App\Lib\isLoggedIn;
use function App\Lib\startSecureSession;
use function App\Lib\getUserById;
use function App\Lib\updateUserDetails;
use function App\Lib\changeUserPassword;

startSecureSession();

// Proteger la página
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$detailMessage = null;
$passwordMessage = null;
$detailsError = false;
$passwordError = false;

$user = getUserById($_SESSION['user_id']);
if(!$user) {
    header('Location: logout.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if($action === 'update_details') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        if(empty($nombre) || empty($apellido)) {
            $detailMessage = 'El nombre y apellido no pueden estar vacios.';
            $detailError = true;
        } elseif(updateUserDetails($user['id_usuario'], $nombre, $apellido)) {
            $detailMessage = 'Datos actualizados con exito';
            $user['nombre'] = $nombre;
            $user['apellido'] = $apellido;
        } else{
            $detailMessage = 'Error al actualizar los datpos.';
            $detailError = true;
        }
    } elseif($action === 'change_password') {
        $old_passwd = $_POST['old_password'] ?? '';
        $new_passwd = $_POST['new_password'] ?? '';
        $confirm_passwd = $_POST['confirm_password'] ?? '';

        if (empty($old_passwd) || empty($new_passwd) || empty($confirm_passwd)) {
            $passwordMessage = 'Todos los campos de contraseña son obligatorios.';
            $passwordError = true;
        } elseif ($new_passwd !== $confirm_passwd) {
            $passwordMessage = 'Las nuevas contraseñas no coinciden.';
            $passwordError = true;
        } elseif (strlen($new_passwd) < 8) {
            $passwordMessage = 'La nueva contraseña debe tener al menos 8 caracteres.';
            $passwordError = true;
        } elseif (changeUserPassword($user['id_usuario'], $old_passwd, $new_passwd)) {
            $passwordMessage = '¡Contraseña cambiada con éxito!';
        } else {
            $passwordMessage = 'Error: La contraseña antigua es incorrecta o hubo un problema al actualizar.';
            $passwordError = true;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mi Cuenta</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body data-theme="light">
    <?php include __DIR__ . '/../templates/header.php'; // Incluir el header normal ?>
    <main class="section" style="max-width: 720px; margin-left: auto; margin-right: auto;">
        <div class="section-header">
            <h2>Mi Cuenta</h2>
            <p>Hola, <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>. Aquí puedes ver y actualizar tus datos personales.</p>
        </div>
        
        <h3>Mis Datos</h3>
        <form method="POST" class="contact-form" style="max-width: none;">
            <input type="hidden" name="action" value="update_details">

            <?php if ($detailMessage): ?>
                <p class="cart-feedback" style="margin-top:0;" data-state="<?php echo $detailsError ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($detailMessage); ?>
                </p>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-field">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                </div>
                <div class="form-field">
                    <label for="apellido">Apellido</label>
                    <input id="apellido" name="apellido" type="text" value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                </div>
            </div>
            <div class="form-field">
                <label for="email">Email (no se puede cambiar)</label>
                <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
            </div>
            <button typeT="submit" class="primary" style="justify-self: start;">Actualizar Datos</button>
        </form>

        <hr class="divider">

        <h3>Cambiar Contraseña</h3>
        <form method="POST" class="contact-form" style="max-width: none;">
            <input type="hidden" name="action" value="change_password">

            <?php if ($passwordMessage): ?>
                <p class="cart-feedback" style="margin-top:0;" data-state="<?php echo $passwordError ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($passwordMessage); ?>
                </p>
            <?php endif; ?>

            <div class="form-field">
                <label for="old_password">Contraseña Antigua</label>
                <input id="old_password" name="old_password" type="password" required>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="new_password">Nueva Contraseña</label>
                    <input id="new_password" name="new_password" type="password" required>
                </div>
                <div class="form-field">
                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                    <input id="confirm_password" name="confirm_password" type="password" required>
                </div>
            </div>
            <button type="submit" class="primary" style="justify-self: start;">Cambiar Contraseña</button>
        </form>

        <a href="logout.php" style="color: var(--color-error); margin-top: 40px; display: inline-block;">Cerrar Sesión</a>
    </main>
    <?php include __DIR__ . '/../templates/footer.php'; // Incluir el footer normal ?>
</body>
</html>