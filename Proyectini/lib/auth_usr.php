<?php
declare(strict_types=1);

namespace App\Lib;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

//use PDO;
//require_once __DIR__ . './db.php';


//Funcion para el envio de correo
function sendEmail(string $to, string $subject, string $body) : bool {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aa5974076@gmail.com';
        $mail->Password = 'nrjy inyg qkvs etrm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587;

        $mail->setFrom('aa5974076@gmail.com.mx', 'Las Sevillanas No Oficial');
        $mail->addAddress($to);
        //$mail->addReplyTo('info@SevillanasNoOficial.mx', 'Informacion');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch(Exception $e) {
        //error_log("Error al enviar correo: {$mail->ErrorInfo}");
        throw new \Exception("No se pudo enviar el correo de verificación. Error: {$mail->ErrorInfo}");
    }
}

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path' => $cookieParams['path'],
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true,  // Asumir HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function registerUser(string $nombre, string $apellido, string $email, string $password) {
    //$pdo = getPDO();
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new \Exception('El correo electrónico ya está registrado.');
    }

    $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
    $token = bin2hex(random_bytes(32));

    $stmt = $pdo->prepare("
        INSERT INTO usuario (nombre, apellido, email, password, verification_token) 
        VALUES (:nombre, :apellido, :email, :password, :verification_token)");
    $stmt->execute([
        ':nombre' => $nombre,
        ':apellido' => $apellido,
        ':email' => $email,
        ':password' => $passwordHash,
        ':verification_token' => $token,
    ]);

    $verificationLink = "http://{$_SERVER['HTTP_HOST']}/LasSevillanas/Proyectini/users/verify.php?token={$token}";
    $emailBody = "<h1>Bienvenido a Las Sevillanas</h1>";
    $emailBody .= "<p>Gracias por registrarte. Por favor, haz click en el siguiente enlace para activar tu cuenta:</p>";
    $emailBody .= "<a href='{$verificationLink}'>Activar mi cuenta</a>";

    return sendEmail($email, 'Activa tu cuenta en las Sevillanas', $emailBody);
}

function verifyAccount(string $token) : bool {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        SELECT id_usuario
        FROM usuario
        WHERE verification_token = ?
        AND is_verified = FALSE
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if($user){
        $stmt = $pdo->prepare("
            UPDATE usuario
            SET is_verified = TRUE, verification_token = NULL
            WHERE id_usuario = ?
        ");
        return $stmt->execute([$user['id_usuario']]);
    }
    return false;
}

function isLoggedIn() : bool {
    return isset($_SESSION['user_id']);
}

function attemptLogin(string $email, string $password) : bool {
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        SELECT id_usuario, password, is_verified
        FROM usuario WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && $user['is_verified'] && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id_usuario'];
        return true;
    }
    return false;
}

function logout() : void {
    $_SESSION = [];
    if(ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function getUserById(int $userId): ?array
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT id_usuario, nombre, apellido, email 
            FROM usuario WHERE id_usuario = ?
            ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (\PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Actualiza los detalles (nombre, apellido) de un usuario.
 */
function updateUserDetails(int $userId, string $nombre, string $apellido): bool
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            UPDATE usuario SET nombre = ?, apellido = ? 
            WHERE id_usuario = ?
            ");
        return $stmt->execute([$nombre, $apellido, $userId]);
    } catch (\PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Cambia la contraseña de un usuario verificando la antigua.
 */
function changeUserPassword(int $userId, string $oldPassword, string $newPassword): bool
{
    try {
        $pdo = getPDO();
        
        // 1. Obtener el hash actual
        $stmt = $pdo->prepare("
            SELECT password FROM usuario 
            WHERE id_usuario = ?
            ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($oldPassword, $user['password'])) {
            // La contraseña antigua no coincide
            return false;
        }

        // 2. Si coincide, actualizar con el nuevo hash
        $newPasswordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $stmt = $pdo->prepare("
            UPDATE usuario SET password = ? 
            WHERE id_usuario = ?
            ");
        return $stmt->execute([$newPasswordHash, $userId]);

    } catch (\PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}