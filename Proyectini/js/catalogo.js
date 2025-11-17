document.addEventListener('DOMContentLoaded', () => {
    const productList = document.querySelector('.product-list');
    const viewToggleButtons = document.querySelectorAll('.view-toggle button');
    const categoryContainer = document.querySelector('.category-filters');
    const categoryButtonContainer = categoryContainer?.querySelector('.category-filters-desktop');
    const categoryMobileContainer = categoryContainer?.querySelector('.category-filters-mobile');
    const categorySelect = categoryMobileContainer?.querySelector('#category-select-mobile');

    const products = Array.isArray(window.__INITIAL_PRODUCTS__)
        ? window.__INITIAL_PRODUCTS__
        : [];

    const categories = Array.isArray(window.__INITIAL_CATEGORIES__)
        ? window.__INITIAL_CATEGORIES__
        : [];

    // DEBUG: Log promotion data
    console.log('📦 Total products loaded:', products.length);
    const promoProducts = products.filter(p => p.has_promotion);
    console.log('🏷️ Products with promotions:', promoProducts.length);
    if (promoProducts.length > 0) {
        console.log('🎉 First promo product:', promoProducts[0]);
    } else {
        console.log('❌ No products have promotions!');
        console.log('Sample product structure:', products[0]);
    }

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
        if (!product.has_promotion || !product.promotion) {
            return null;
        }

        const badge = document.createElement('div');
        badge.className = 'product-promo-badge';
        
        const promotion = product.promotion;
        
        if (promotion.tipo_descuento === 'porcentaje') {
            badge.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    <path d="M9 12h6"/>
                </svg>
                -${Math.round(promotion.valor_descuento)}%
            `;
        } else {
            badge.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    <path d="M9 12h6"/>
                </svg>
                -$${Math.round(promotion.valor_descuento)}
            `;
        }
        
        badge.title = promotion.nombre_promo;
        
        return badge;
    }

    function createPriceDisplay(product) {
        const priceContainer = document.createElement('div');
        priceContainer.className = 'price-container';
        
        if (product.has_promotion && product.original_price) {
            // Show both original and discounted price
            const originalPrice = document.createElement('span');
            originalPrice.className = 'price original-price';
            originalPrice.textContent = formatCurrency(product.original_price);
            
            const discountedPrice = document.createElement('span');
            discountedPrice.className = 'price discounted-price';
            discountedPrice.textContent = formatCurrency(product.price);
            
            priceContainer.append(originalPrice, discountedPrice);
        } else {
            // Show regular price
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
        
        // Add promotion class if product has promotion
        if (product.has_promotion) {
            card.classList.add('has-promotion');
        }

        if (product.image) {
            const figure = document.createElement('figure');
            
            // Add promotion badge
            const promoBadge = createPromoBadge(product);
            if (promoBadge) {
                figure.appendChild(promoBadge);
            }
            
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
        
        // Add price display
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
            if (!cart || typeof cart.addItem !== 'function') {
                window.location.href = `vista_producto.php?id_producto=${encodeURIComponent(product.id)}`;
                return;
            }

            try {
                // Pass the product with promotion info
                cart.addItem(product, 1);
                showFeedback(feedback, 'Producto agregado.', 'success');
            } catch (error) {
                console.error(error);
                showFeedback(feedback, 'No se pudo agregar el producto.', 'error');
            }
        });

        const moreLink = document.createElement('a');
        moreLink.href = `vista_producto.php?id_producto=${encodeURIComponent(product.id)}`;
        moreLink.textContent = 'Ver detalle';
        moreLink.setAttribute('aria-label', `Ver detalles de ${product.name}`);

        footer.append(addButton, moreLink);
        card.append(feedback, footer);

        return card;
    }

    function handleFilterChange(categoryId) {
        renderProducts(categoryId);
        if (!categorySelect || !categoryButtonContainer) return;
        categorySelect.value = categoryId;
        categoryButtonContainer.querySelectorAll('button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.categoryId == categoryId);
        });
    }

    function renderCategories(categories) {
        if (!categoryButtonContainer || !categorySelect) {
            console.warn('no se encontraron los contenedores de categorias');
            return;
        }
        categoryButtonContainer.innerHTML = '';
        categorySelect.innerHTML = '';

        const group = document.createElement('div');
        group.className = 'toggle-group';
        group.setAttribute('role', 'group');
        group.setAttribute('aria-label', 'Filtrar por categoría');

        const allCategories = [
            { id_categoria: 'Todos', nombre_categoria: 'Todos' },
            ...categories
        ];

        allCategories.forEach((category, index) => {
            const categoryId = category.id_categoria;
            const categoryName = category.nombre_categoria;

            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = categoryName;
            button.dataset.categoryId = categoryId;
            if (index === 0) button.classList.add('active');

            button.addEventListener('click', () => {
                handleFilterChange(categoryId);
            });
            group.appendChild(button);

            const option = document.createElement('option');
            option.value = categoryId;
            option.textContent = categoryName;
            categorySelect.appendChild(option);
        });
        categoryButtonContainer.appendChild(group);
        categorySelect.addEventListener('change', () => {
            handleFilterChange(categorySelect.value);
        });
    }

    function renderProducts(filterCategoryId = 'Todos') {
        if (!productList) {
            return;
        }
        productList.innerHTML = '';

        const filteredProducts = (filterCategoryId === 'Todos')
            ? products
            : products.filter(p => p.id_categoria == filterCategoryId);

        if (!filteredProducts.length) {
            const empty = document.createElement('p');
            empty.className = 'lead';
            empty.textContent = (filterCategoryId === 'Todos')
                ? 'Pronto agregaremos nuevos productos a nuestro catalogo.'
                : `No se encontraron productos en la categoría.`;
            productList.appendChild(empty);
            return;
        }
        const fragment = document.createDocumentFragment();
        filteredProducts.forEach((product) => {
            fragment.appendChild(createProductCard(product));
        });
        productList.appendChild(fragment);
    }

    function setupViewToggle() {
        if (!productList || viewToggleButtons.length === 0) {
            return;
        }

        viewToggleButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const view = button.dataset.view;
                productList.dataset.view = view;
                viewToggleButtons.forEach((btn) => {
                    btn.classList.toggle('active', btn === button);
                });
            });
        });
    }

    setupViewToggle();
    renderCategories(categories);
    renderProducts();
});