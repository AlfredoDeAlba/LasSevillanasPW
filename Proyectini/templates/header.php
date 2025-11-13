<?php
namespace App\Lib;
// Usamos una ruta absoluta para asegurar que siempre encuentre el archivo
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/storage.php';
require_once __DIR__ . '/../lib/auth_usr.php';

use function App\Lib\readProducts;
use function App\Lib\readCategories;
use function App\Lib\startSecureSession;
use function App\Lib\isLoggedIn;

startSecureSession();

// --- LÓGICA PARA ENLACES DINÁMICOS ---
$isLoggedIn = isLoggedIn();
$currentPage = basename($_SERVER['PHP_SELF']);

// Lógica para el enlace de Perfil
$perfilLink = $isLoggedIn ? 'users/account.php' : 'users/login.php';
$perfilPages = ['account.php', 'login.php', 'register.php']; // Páginas que activan el ícono de "Perfil"
$perfilActiveClass = in_array($currentPage, $perfilPages) ? 'active' : '';
// --- FIN DE LÓGICA ---

// Leemos los productos para que estén disponibles en cualquier página que incluya este header
$products = readProducts();
$categories = readCategories();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dulces de Leche - Tradici&oacute;n Mexicana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY']); ?>"></script>
    <script src="https://js.stripe.com/v3/"></script>

    <script src="app.js" defer></script>
    <script src="users/user.js" defer></script>
    <?php
        // Inyectamos los productos que ya cargamos en PHP al 'window' de JavaScript
        echo "<script>";
        echo "window.__INITIAL_PRODUCTS__ = " . json_encode($products) . ";";
        echo "window.__INITIAL_CATEGORIES__ = " . json_encode($categories) . ";";
        echo "</script>";
    ?>
    
</head>
<body data-theme="light">
    
    <header class="top-bar">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">DL</span>
            <span class="brand-name">Las Sevillanas</span>
        </div>
        
        <div class="desktop-header-items">
            <button class="theme-toggle" type="button" aria-pressed="false" aria-label="Cambiar tema">
                </button>
            <nav class="main-nav" aria-label="Principal (Escritorio)">
                <ul>
                    <li><a href="/LasSevillanas/Proyectini/index.php" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">Inicio</a></li>
                    <li><a href="/LasSevillanas/Proyectini/catalogo.php" class="<?php echo $currentPage == 'catalogo.php' ? 'active' : ''; ?>">Cat&aacute;logo</a></li>
                    <li><a href="/LasSevillanas/Proyectini/historia.php" class="<?php echo $currentPage == 'historia.php' ? 'active' : ''; ?>">Historia</a></li>
                    <li><a href="/LasSevillanas/Proyectini/index.php#valores">Valores</a></li>
                    <li><a href="/LasSevillanas/Proyectini/index.php#contacto">Contacto</a></li>
                    <li><a href="/LasSevillanas/Proyectini/terminos.php" class="<?php echo $currentPage == 'terminos.php' ? 'active' : ''; ?>">T&eacute;rminos y Condiciones</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <button type="button" class="search-toggle" aria-label="Buscar">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="28" height="28">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                </button>
                
                <div class="user-session">
                    <?php if ($isLoggedIn): ?>
                        <a href="/LasSevillanas/Proyectini/users/account.php" class="user-account-link" aria-label="Mi cuenta">
                            <svg fill="currentColor" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </a>
                    <?php else: ?>
                        <button type="button" id="login-user-btn">Iniciar Sesión</button>
                        <button type="button" id="register-user-btn" class="primary">Registrarse</button>
                    <?php endif; ?>
                </div>

                <div class="cart-wrapper" id="desktop-cart">
                    <a href="/LasSevillanas/Proyectini/compra.php" class="cart-toggle" aria-label="Ver Carrito">                    
                        <svg viewBox="0 0 24 24" aria-hidden="true" width="28" height="28">
                            <path d="M19 7h-3V6a4 4 0 10-8 0v1H5a1 1 0 00-1 1v11a3 3 0 003 3h10a3 3 0 003-3V8a1 1 0 00-1 1Zm-9-1a2 2 0 114 0v1H10V6ZM6 9h12v9a1 1 0 01-1 1H7a1 1 0 01-1-1V9Z" />
                        </svg>
                        <span class="desktop-only">Carrito</span>
                        <span class="cart-count" data-cart-count>0</span>
                    </a>
                    <div class="cart-dropdown" id="cart-dropdown" hidden>
                        </div>
                </div>
            </div>

            <div class="social-links" aria-label="Redes sociales">
                </div>
        </div> <div class="mobile-search-row">
            <span class="search-icon-input" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </span>
            <input type="search" id="search-input" placeholder="Buscar productos...">
            
            <div class="cart-wrapper" id="mobile-cart">
                <a href="/LasSevillanas/Proyectini/compra.php" class="cart-toggle" aria-label="Ver Carrito">                    
                    <svg viewBox="0 0 24 24" aria-hidden="true" width="28" height="28">
                        <path d="M19 7h-3V6a4 4 0 10-8 0v1H5a1 1 0 00-1 1v11a3 3 0 003 3h10a3 3 0 003-3V8a1 1 0 00-1 1Zm-9-1a2 2 0 114 0v1H10V6ZM6 9h12v9a1 1 0 01-1 1H7a1 1 0 01-1-1V9Z" />
                    </svg>
                </a>
            </div>
        </div>
    </header>
    <nav class="bottom-nav-bar" aria-label="Navegación principal en móvil">
        <ul>
            <li>
                <a href="/LasSevillanas/Proyectini/index.php" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </span>
                    <span>Inicio</span>
                </a>
            </li>
            <li>
                <a href="/LasSevillanas/Proyectini/catalogo.php" class="<?php echo $currentPage == 'catalogo.php' ? 'active' : ''; ?>">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </span>
                    <span>Catálogo</span>
                </a>
            </li>
            <li>
                <a href="/LasSevillanas/Proyectini/compra.php" class="cart-icon-nav <?php echo $currentPage == 'compra.php' ? 'active' : ''; ?>">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                    </span>
                    <span>Carrito</span>
                    <span class="cart-count-mobile badge" hidden>0</span>
                </a>
            </li>
            <li>
                <a href="/LasSevillanas/Proyectini/<?php echo $perfilLink; ?>" class="<?php echo $perfilActiveClass; ?>">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </span>
                    <span>Perfil</span>
                </a>
            </li>
            <li>
                <a href="/LasSevillanas/Proyectini/mas.php" class="<?php echo $currentPage == 'mas.php' ? 'active' : ''; ?>">
                    <span class="icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </span>
                    <span>Más</span>
                </a>
            </li>
        </ul>
    </nav>