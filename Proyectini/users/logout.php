<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth_usr.php';

use function App\Lib\logout;
use function App\Lib\startSecureSession;

startSecureSession();
logout();
header('Location: login.php');
exit;