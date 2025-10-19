<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

// Carga las variables de entorno desde el archivo .env
// __DIR__ . '/..' apunta a la raíz del proyecto (donde está el .env)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

    define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY']);
    define('STRIPE_PUBLIC_KEY', $_ENV['STRIPE_PUBLIC_KEY']);
?>