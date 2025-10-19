<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

// Carga las variables de entorno desde el archivo .env
// __DIR__ . '/..' apunta a la raíz del proyecto (donde está el .env)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();


    
    define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY']);
    define('STRIPE_PUBLIC_KEY', $_ENV['STRIPE_PUBLIC_KEY']);
    define('MAIL_HOST', $_ENV['MAIL_HOST']);
    define('MAIL_PORT', $_ENV['MAIL_PORT']);
    define('MAIL_USERNAME', $_ENV['MAIL_USERNAME']);
    define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD']);
    define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME']);

?>