document.addEventListener('DOMContentLoaded', () => {
    // --- Contenedores ---
    const resultsOverlay = document.getElementById('live-search-overlay');
    const resultsList = document.getElementById('live-search-results');
    
    // --- Triggers Desktop ---
    const desktopSearchTrigger = document.getElementById('desktop-search-trigger');
    const desktopSearchInput = document.getElementById('desktop-search-input');
    const desktopSearchClose = document.getElementById('desktop-search-close');
    const desktopSearchWrapper = document.getElementById('desktop-search-wrapper');
    const searchBarActive = document.getElementById('search-bar-active');
    
    // --- Triggers Mobile ---
    const mobileSearchInput = document.getElementById('search-input');
    const mobileSearchClose = document.getElementById('mobile-search-close');
    
    // --- Data ---
    const allProducts = Array.isArray(window.__INITIAL_PRODUCTS__) ? window.__INITIAL_PRODUCTS__ : [];

    if (!resultsOverlay) return;

    // --- Funciones de UI ---
    
    function openSearchOverlay() {
        document.body.classList.add('live-search-active');
        resultsOverlay.hidden = false;
        
        // Mostrar la barra de búsqueda activa en desktop
        if (searchBarActive && window.innerWidth > 900) {
            searchBarActive.hidden = false;
        }
        
        // Enfocar el input correspondiente
        if (window.innerWidth > 900) {
            desktopSearchInput?.focus();
        } else {
            mobileSearchInput?.focus();
        }
    }

    function closeSearchOverlay() {
        document.body.classList.remove('live-search-active');
        resultsOverlay.hidden = true;
        
        // Ocultar la barra de búsqueda activa en desktop
        if (searchBarActive) {
            searchBarActive.hidden = true;
        }
        
        // Limpiar y desenfocar inputs
        if (desktopSearchInput) {
            desktopSearchInput.value = '';
            desktopSearchInput.blur();
        }
        if (mobileSearchInput) {
            mobileSearchInput.value = '';
            mobileSearchInput.blur();
        }
        
        resultsList.innerHTML = ''; // Limpiar resultados
    }

    function performSearch(query) {
        query = query.trim().toLowerCase();

        if (query.length < 2) {
            resultsList.innerHTML = `<p class="search-empty-message">Escribe 2 o más letras para buscar...</p>`;
            return;
        }

        const filteredProducts = allProducts.filter(p => 
            p.name.toLowerCase().includes(query) ||
            (p.description && p.description.toLowerCase().includes(query))
        );
        renderResults(filteredProducts, query);
    }

    function renderResults(filteredProducts, query) {
        resultsList.innerHTML = '';

        if (filteredProducts.length > 0) {
            const fragment = document.createDocumentFragment();
            filteredProducts.forEach(product => {
                fragment.appendChild(createProductCard(product));
            });
            resultsList.appendChild(fragment);
        } else {
            resultsList.innerHTML = `<p class="search-empty-message">No se encontraron productos para "${query}".</p>`;
        }
    }
    
    // --- Event Listeners ---

    // Abrir (Desktop) - Al escribir en el input
    if (desktopSearchInput) {
        desktopSearchInput.addEventListener('input', (e) => {
            const query = e.target.value;
            
            // Abrir overlay si no está abierto
            if (!document.body.classList.contains('live-search-active')) {
                openSearchOverlay();
            }
            
            // Realizar búsqueda
            performSearch(query);
        });
        
        // También abrir al hacer focus
        desktopSearchInput.addEventListener('focus', () => {
            if (!document.body.classList.contains('live-search-active')) {
                openSearchOverlay();
                performSearch(desktopSearchInput.value);
            }
        });
    }

    // Abrir (Mobile) - Al escribir en el input
    if (mobileSearchInput) {
        mobileSearchInput.addEventListener('input', (e) => {
            const query = e.target.value;
            
            // Abrir overlay si no está abierto
            if (!document.body.classList.contains('live-search-active')) {
                openSearchOverlay();
            }
            
            // Realizar búsqueda
            performSearch(query);
        });
        
        // También abrir al hacer focus
        mobileSearchInput.addEventListener('focus', () => {
            if (!document.body.classList.contains('live-search-active')) {
                openSearchOverlay();
                performSearch(mobileSearchInput.value);
            }
        });
    }

    // Cerrar (Desktop)
    if (desktopSearchClose) {
        desktopSearchClose.addEventListener('click', closeSearchOverlay);
    }
    
    // Cerrar (Mobile)
    if (mobileSearchClose) {
        mobileSearchClose.addEventListener('click', closeSearchOverlay);
    }
    
    // Cerrar con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.body.classList.contains('live-search-active')) {
            closeSearchOverlay();
        }
    });

    // Cerrar al hacer clic en el overlay (fuera de los resultados)
    if (resultsOverlay) {
        resultsOverlay.addEventListener('click', (e) => {
            if (e.target === resultsOverlay) {
                closeSearchOverlay();
            }
        });
    }

    // Escribir (Desktop)
    if (desktopSearchInput) {
        desktopSearchInput.addEventListener('input', () => performSearch(desktopSearchInput.value));
    }
    
    // Escribir (Mobile)
    if (mobileSearchInput) {
        mobileSearchInput.addEventListener('input', () => performSearch(mobileSearchInput.value));
    }

    // ===================================================================
    //  FUNCIONES DE RENDERIZADO (Copiadas de catalogo.js)
    // ===================================================================
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
        }).format(value ?? 0);
    }
    
    function showFeedback(target, message, type = 'info') {
        if (!target) { return; }
        target.textContent = message;
        target.dataset.state = type;
        target.hidden = false;
        window.clearTimeout(Number(target.dataset.timeoutId) || 0);
        const timeoutId = window.setTimeout(() => {
            target.hidden = true;
            target.textContent = '';
        }, 3000);
        target.dataset.timeoutId = String(timeoutId);
    }
    
    function createPromoBadge(product) {
        if (!product.has_promotion || !product.promotion) return null;
        const badge = document.createElement('div');
        badge.className = 'product-promo-badge';
        const promotion = product.promotion;
        if (promotion.tipo_descuento === 'porcentaje') {
            badge.innerHTML = `-${Math.round(promotion.valor_descuento)}%`;
        } else {
            badge.innerHTML = `-$${Math.round(promotion.valor_descuento)}`;
        }
        badge.title = promotion.nombre_promo || 'Oferta';
        return badge;
    }
    
    function createPriceDisplay(product) {
        const priceContainer = document.createElement('div');
        priceContainer.className = 'price-container';
        if (product.has_promotion && product.original_price) {
            const originalPrice = document.createElement('span');
            originalPrice.className = 'price original-price';
            originalPrice.textContent = formatCurrency(product.original_price);
            const discountedPrice = document.createElement('span');
            discountedPrice.className = 'price discounted-price';
            discountedPrice.textContent = formatCurrency(product.price);
            priceContainer.append(originalPrice, discountedPrice);
        } else {
            const price = document.createElement('span');
            price.className = 'price';
            price.textContent = formatCurrency(product.price);
            priceContainer.appendChild(price);
        }
        return priceContainer;
    }
    
    function createProductCard(product) {
        const card = document.createElement('article');
        card.className = 'product-card';
        if (product.has_promotion) card.classList.add('has-promotion');
        
        if (product.image) {
            const figure = document.createElement('figure');
            const promoBadge = createPromoBadge(product);
            if (promoBadge) figure.appendChild(promoBadge);
            const image = document.createElement('img');
            image.src = product.image;
            image.alt = product.name;
            image.loading = 'lazy';
            figure.appendChild(image);
            card.appendChild(figure);
        }
        
        const header = document.createElement('header');
        const title = document.createElement('h3');
        title.textContent = product.name;
        header.appendChild(title);
        header.appendChild(createPriceDisplay(product));
        card.appendChild(header);
        
        if (product.description) {
            const description = document.createElement('p');
            description.textContent = product.description;
            card.appendChild(description);
        }
        
        const footer = document.createElement('footer');
        const feedback = document.createElement('span');
        feedback.className = 'cart-feedback';
        feedback.hidden = true;
        
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.className = 'primary';
        addButton.textContent = 'Agregar al carrito';
        addButton.addEventListener('click', () => {
            const cart = window.Cart;
            if (cart && typeof cart.addItem === 'function') {
                try {
                    cart.addItem(product, 1);
                    showFeedback(feedback, 'Producto agregado.', 'success');
                } catch (error) {
                    showFeedback(feedback, 'No se pudo agregar.', 'error');
                }
            } else {
                window.location.href = `vista_producto.php?id_producto=${encodeURIComponent(product.id)}`;
            }
        });
        
        const moreLink = document.createElement('a');
        moreLink.href = `vista_producto.php?id_producto=${encodeURIComponent(product.id)}`;
        moreLink.textContent = 'Ver detalle';
        
        footer.append(addButton, moreLink);
        card.append(feedback, footer);
        return card;
    }
});