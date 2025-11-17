document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'sevillanas_cart';
    const cartToggle = document.getElementById('cart-toggle');
    const cartDropdown = document.getElementById('cart-dropdown');
    const cartCount = document.querySelector('[data-cart-count]');
    const cartItemsList = document.querySelector('[data-cart-items]');
    const cartTotal = document.querySelector('[data-cart-total]');
    const cartClose = document.querySelector('.cart-close');
    const cartClearButton = document.querySelector('[data-cart-clear]');

    const allCartCounts = document.querySelectorAll('[data-cart-count]');
    let items = loadItems();

    function loadItems() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (!stored) {
                return [];
            }
            const parsed = JSON.parse(stored);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.warn('No se pudo leer el carrito almacenado.', error);
            return [];
        }
    }

    function persist() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        } catch (error) {
            console.warn('No se pudo guardar el carrito.', error);
        }
        render();
        notify();
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
        }).format(value ?? 0);
    }

    function getTotalQuantity() {
        return items.reduce((acc, item) => acc + item.quantity, 0);
    }

    function getSubtotal() {
        return items.reduce((acc, item) => acc + item.price * item.quantity, 0);
    }

    function getTotalSavings(){
        return items.reduce((acc, item) => {
            if(item.has_promotion && item.original_price){
                const savings = (item.original_price - item.price) * item.quantity;
                return acc + savings;
            }
            return acc;
        }, 0);
    }

    function render() {
        const totalQty = getTotalQuantity();
        
        // Update ALL cart count badges (desktop and mobile)
        allCartCounts.forEach(badge => {
            if (badge) {
                badge.textContent = totalQty > 0 ? String(totalQty) : '0';
                // Show/hide badge based on count
                if (totalQty === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.style.display = 'inline-flex';
                }
            }
        });

        // Update mobile bottom nav cart badge
        const mobileNavBadge = document.querySelector('.cart-count-mobile');
        if (mobileNavBadge) {
            mobileNavBadge.textContent = totalQty > 0 ? String(totalQty) : '';
            mobileNavBadge.hidden = totalQty === 0;
        }

        // Render cart items list (only if element exists)
        if (!cartItemsList) {
            return;
        }

        cartItemsList.innerHTML = '';

        if (items.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'cart-empty';
            empty.textContent = 'Aún no has agregado productos.';
            cartItemsList.appendChild(empty);
        } else {
            const fragment = document.createDocumentFragment();
            items.forEach((item) => {
                const row = document.createElement('li');
                row.className = 'cart-item';
                row.dataset.id = item.id;

                if(item.has_promotion){
                    row.classList.add('has_promotion');
                }

                const image = document.createElement('img');
                image.src = item.image || 'placeholder.jpg';
                image.alt = item.name;

                const info = document.createElement('div');
                info.className = 'cart-item-info';

                const title = document.createElement('h3');
                title.textContent = item.name;

                const meta = document.createElement('div');
                meta.className = 'cart-item-meta';

                if(item.has_promotion && item.original_price){
                    meta.innerHTML = `
                        <div class="cart-item-promo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                <path d="M9 12h6"/>
                            </svg>
                            <span class="original-price-small">${formatCurrency(item.original_price)}</span>
                        </div>
                        <span class="promo-price">${item.quantity} x ${formatCurrency(item.price)}</span>
                        <span class="savings-small">Ahorras ${formatCurrency((item.original_price - item.price)*item.quantity)}</span>
                    `;
                }else{
                    meta.innerHTML = `
                        <span>${item.quantity} x ${formatCurrency(item.price)}</span>
                    `;
                }
                info.append(title, meta);

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'cart-item-remove';
                removeButton.setAttribute('data-cart-remove', item.id);
                removeButton.textContent = 'Quitar';

                row.append(image, info, removeButton);
                fragment.appendChild(row);
            });
            cartItemsList.appendChild(fragment);
        }

        // Update total
        if(cartTotal){
            const totalSavings = getTotalSavings();
            const subtotal = getSubtotal();
            if(totalSavings > 0){
                cartTotal.innerHTML = `
                    <div class="cart-total-breakdown">
                        <div class="cart-total-line">
                            <span>Subtotal:</span>
                            <span>${formatCurrency(subtotal)}</span>
                        </div>
                        <div class="cart-total-line savings-line">
                            <span>Ahorros totales:</span>
                            <span>-${formatCurrency(totalSavings)}</span>
                        </div>
                        <div class="cart-total-line final">
                            <strong>Total:</strong>
                            <strong>${formatCurrency(subtotal)}</strong>
                        </div>
                    </div>
                `;
            }else{
                cartTotal.textContent = formatCurrency(subtotal);
            }
        }
    }

    function notify() {
        const detail = {
            items: items.map((item) => ({ ...item })),
            quantity: getTotalQuantity(),
            total: getSubtotal(),
            savings: getTotalSavings()
        };
        document.dispatchEvent(new CustomEvent('cart:updated', { detail }));
    }

    function findItemIndex(id) {
        return items.findIndex((item) => item.id === id);
    }

    function addItem(product, quantity = 1) {
        if (!product || product.id === undefined || product.id === null) {
            throw new Error('Producto invalido.');
        }

        const id = String(product.id);
        const price = Number(product.price) || 0;
        const parsedQuantity = Math.max(1, Number(quantity) || 1);

        const index = findItemIndex(id);
        if (index >= 0) {
        // Si existe, SUMA la cantidad
            items[index].quantity = Math.min(items[index].quantity + parsedQuantity, 99);
        } else {
            //crear nuevo item del carrito con la info del descuento en ello
            const cartItem = {
                id,
                name: product.name || 'Producto',
                price,
                quantity: parsedQuantity,
                image: product.image || 'placeholder.jpg',
            };
            
            // Anadir la info de la promocion relacionada si es que tiene
            if (product.has_promotion) {
                cartItem.has_promotion = true;
                cartItem.original_price = product.original_price;
                cartItem.discount_percentage = product.discount_percentage;
                
                // almacenar informacion de la promocion para referencia
                if (product.promotion) {
                    cartItem.promotion = {
                        id: product.promotion.id_promocion,
                        name: product.promotion.nombre_promo,
                        type: product.promotion.tipo_descuento,
                        value: product.promotion.valor_descuento
                    };
                }
            }
            
            items.push(cartItem);
        }

        persist();
        // Muestra feedback
        showCartFeedback('Producto agregado al carrito', 'success');
    }

    function removeItem(id) {
        const index = findItemIndex(id);
        if (index >= 0) {
            items.splice(index, 1);
            persist();
            showCartFeedback('Producto eliminado', 'success');
        }
    }
    
    function updateItemQuantity(id, quantity) {
        const parsedQuantity = Math.max(1, Number(quantity) || 1);
        const index = findItemIndex(String(id));

        if (index >= 0) {
            items[index].quantity = Math.min(parsedQuantity, 99);
            persist();
        }
    }

    function showCartFeedback(message, state = 'success') {
        const feedback = document.getElementById('cart-feedback');
        if (feedback) {
            feedback.textContent = message;
            feedback.setAttribute('data-state', state);
            setTimeout(() => {
                feedback.textContent = '';
                feedback.removeAttribute('data-state');
            }, 3000);
        }
    }

    function clearCart() {
        items = [];
        persist();
        showCartFeedback('Carrito vaciado', 'success');
    }

    function toggleCart(force) {
        if (!cartDropdown || !cartToggle) {
            return;
        }

        const isOpen = force ?? cartDropdown.hidden;
        cartDropdown.hidden = !isOpen;
        cartToggle.setAttribute('aria-expanded', String(isOpen));
    }

    cartToggle?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleCart();
    });

    cartClose?.addEventListener('click', () => toggleCart(false));

    cartClearButton?.addEventListener('click', () => {
        if (confirm('¿Estás seguro de vaciar el carrito?')) {
            clearCart();
        }
    });

    cartItemsList?.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }
        const id = target.getAttribute('data-cart-remove');
        if (id) {
            removeItem(id);
        }
    });

    // Close cart when clicking outside
    document.addEventListener('click', (event) => {
        if (!cartDropdown || cartDropdown.hidden) {
            return;
        }
        const target = event.target;
        if (!(target instanceof Node)) {
            return;
        }
        if (cartDropdown.contains(target) || cartToggle?.contains(target)) {
            return;
        }
        toggleCart(false);
    });

    // Expose Cart API globally
    window.Cart = {
        getItems: () => items.map((item) => ({ ...item })),
        getSubtotal,
        getTotalQuantity,
        getTotalSavings,
        addItem,
        removeItem,
        updateItemQuantity,
        clear: clearCart,
    };

    render();
    notify();
});