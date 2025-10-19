<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/config.php'; // Cargar config primero
require_once __DIR__ . '/../lib/auth_usr.php';

use function App\Lib\isLoggedIn;
use function App\Lib\startSecureSession;
use function App\Lib\getUserById;
use function App\Lib\updateUserDetails;
use function App\Lib\changeUserPassword;
use function App\Lib\deleteUserAccount;
use function App\Lib\logout;

startSecureSession();

// Proteger la página
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$detailMessage = null;
$passwordMessage = null;
$deleteMessage = null;
$detailsError = false;
$passwordError = false;
$deleteError = false;

$user = getUserById($_SESSION['user_id']); //carga la versión simple
if(!$user) {
    header('Location: logout.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- MANEJO DE RECTIFICACIÓN ---
    if($action === 'update_details') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        
        if(empty($nombre) || empty($apellido)) {
            $detailMessage = 'El nombre y apellido no pueden estar vacios.';
            $detailsError = true;
            
        // --- LLAMADA A FUNCIÓN REVERTIDA (con 3 argumentos) ---
        } elseif(updateUserDetails($user['id_usuario'], $nombre, $apellido)) {
            $detailMessage = 'Datos actualizados con exito';
            // Recargar los datos para que se vean reflejados
            $user = getUserById($_SESSION['user_id']);
        } else{
            $detailMessage = 'Error al actualizar los datos.';
            $detailsError = true;
        }
    
    // --- MANEJO DE CAMBIO DE CONTRASEÑA ---
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

    // --- MANEJO DE CANCELACIÓN ---
    } elseif ($action === 'delete_account') {
        $password = $_POST['confirm_delete_password'] ?? '';

        if(empty($password)) {
            $deleteMessage = 'Debes ingresar tu contraseña para confirmar.';
            $deleteError = true;
        } elseif (deleteUserAccount($user['id_usuario'], $password)) {
            logout();
            header('Location: ../index.php?status=account_deleted');
            exit;
        } else {
            $deleteMessage = 'Contraseña incorrecta. No se pudo eliminar la cuenta.';
            $deleteError = true;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mi Cuenta</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        button.danger, .danger {
            background-color: var(--color-error);
            border-color: var(--color-error);
            color: #fff;
            font-weight: 600;
        }
        button.danger:hover {
            background-color: #b02a2a;
            border-color: #b02a2a;
        }
    </style>
</head>
<body data-theme="light">
    <?php include __DIR__ . '/../templates/header.php'; ?>
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
            
            <button type="submit" class="primary" style="justify-self: start;">Actualizar Datos</button>
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

        <hr class="divider">

        <h3>Zona de Peligro (Derecho de Cancelación)</h3>
        <form method="POST" class="contact-form" style="max-width: none; background: var(--color-elevated); border: 1px solid var(--color-error); border-radius: var(--radius-base); padding: var(--space-md);">
            <input type="hidden" name="action" value="delete_account">
            
            <?php if ($deleteMessage): ?>
                <p class="cart-feedback" style="margin-top:0;" data-state="<?php echo $deleteError ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($deleteMessage); ?>
                </p>
            <?php endif; ?>
            
            <p><strong>Eliminar mi cuenta permanentemente</strong></p>
            <p style="color: var(--color-text-muted); margin-top: -10px;">Esta acción es irreversible. Se eliminarán todos tus datos personales. Para confirmar, ingresa tu contraseña.</p>
            
            <div class="form-field">
                <label for="confirm_delete_password">Contraseña Actual</label>
                <input id="confirm_delete_password" name="confirm_delete_password" type="password" required>
            </div>
            
            <button type="submit" class="danger" style="justify-self: start;">Eliminar Mi Cuenta</button>
        </form>

        <a href="logout.php" style="color: var(--color-error); margin-top: 40px; display: inline-block;">Cerrar Sesión</a>
    </main>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>