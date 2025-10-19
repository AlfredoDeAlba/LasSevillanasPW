document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const stripe = Stripe(window.STRIPE_PUBLIC_KEY);

    let elements;
    let cardElement;

    
    const form = document.getElementById('payment-form');
    const paymentStatusEl = document.getElementById('payment-status');
    const submitBtn = document.getElementById('submit-payment-btn');
    const cartListEl = document.getElementById('checkout-cart-list');
    
    const cartEmptyEl = document.getElementById('cart-empty-message');
    const subtotalAmountEl = document.getElementById('subtotal-amount');
    const totalAmountEl = document.getElementById('total-amount');
    const discountAmountEl = document.getElementById('discount-amount');

    // Estado local para descuentos (simulado)
    let currentDiscount = 0;
    let currentClientSecret = null;


    function formatCurrency(amount) {
        return `$${(amount ?? 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;
    }

    /**
     * Calcula y actualiza los totales en el resumen de la derecha.
     */
    function updateCheckoutTotals() {
        if (!window.Cart) return;

        const subtotal = window.Cart.getSubtotal();
        
        // **!!aqui debemos de implementar logica de cupones!! (esto es un placeholder)
        // currentDiscount = ...
        
        const total = subtotal - currentDiscount;

        subtotalAmountEl.textContent = formatCurrency(subtotal);
        discountAmountEl.textContent = formatCurrency(currentDiscount > 0 ? -currentDiscount : 0);
        totalAmountEl.textContent = formatCurrency(total > 0 ? total : 0);
    }

    /**
     * Renderiza los items del carrito en la lista de la izquierda.
     */
    function renderCheckoutCart() {
        if (!window.Cart || !cartListEl || !cartEmptyEl) return;

        const items = window.Cart.getItems();
        cartListEl.innerHTML = ''; 

        if (items.length === 0) {
            cartEmptyEl.style.display = 'block';
            submitBtn.disabled = true;
            return;
        }

        cartEmptyEl.style.display = 'none';
        submitBtn.disabled = false;
        
        const fragment = document.createDocumentFragment();
        items.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'cart-item'; // 
            itemEl.innerHTML = `
                <img src="${item.image || 'placeholder.jpg'}" alt="${item.name}">
                <div class="cart-item-info">
                    <h3>${item.name}</h3>
                    <div class="quantity-selector">
                        <label for="qty-${item.id}">Cant:</label>
                        <input type="number" id="qty-${item.id}" data-id="${item.id}" class="cart-item-qty" value="${item.quantity}" min="1" max="99">
                    </div>
                </div>
                <strong>${formatCurrency(item.price * item.quantity)}</strong>
                <button type="button" class="cart-item-remove" data-id="${item.id}" aria-label="Quitar">&times;</button>
            `;
            fragment.appendChild(itemEl);
        });
        cartListEl.appendChild(fragment);
        updateCheckoutTotals();
    }

    /**
     * Maneja los clics en la lista del carrito (quitar o cambiar cantidad).
     */
    function handleCartListInteraction(event) {
        const target = event.target;
        const id = target.dataset.id;
        if (!id || !window.Cart) return;

        if (target.classList.contains('cart-item-remove')) {
            window.Cart.removeItem(id);
            renderCheckoutCart(); 
        }

        if (target.classList.contains('cart-item-qty')) {
            let newQuantity = parseInt(target.value, 10);
            if (isNaN(newQuantity) || newQuantity < 1) {
                newQuantity = 1;
                target.value = 1;
            }
            
            window.Cart.updateItemQuantity(id, newQuantity);

            renderCheckoutCart(); // Re-renderizar todo
        }
    }

    /**
     * Maneja el envío del formulario de pago.
     */
    async function handlePaymentSubmit(event) {
        event.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';

        // 1. Recolectar datos del formulario
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // 2. Recolectar items del carrito
        const cartItems = window.Cart.getItems();
        if (cartItems.length === 0) {
            paymentStatusEl.textContent = 'Tu carrito está vacío.';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Procesar Pago';
            return;
        }
        

        // 4. Enviar todo al nuevo endpoint
        try {
            const response = await fetch('procesar_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    formData: data,
                    cartItems: cartItems,
                    discount: currentDiscount
                })
            });

            const result = await response.json();

            if (result.success) {
                paymentStatusEl.textContent = `¡Pedido #${result.orderId} creado con éxito! Redirigiendo...`;
                paymentStatusEl.style.color = 'green';
                window.Cart.clear(); // Limpiar carrito
                // Redirigir a una página de "gracias"
                setTimeout(() => {
                    window.location.href = `/gracias.php?order_id=${result.orderId}`;
                }, 2000);
            } else {
                throw new Error(result.error || 'Ocurrió un error desconocido.');
            }

        } catch (error) {
            console.error('Error al procesar el pago:', error);
            paymentStatusEl.textContent = `Error: ${error.message}`;
            paymentStatusEl.style.color = 'var(--color-error)';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Procesar Pago';
        }
    }

    /**
     * Inicializa y monta el formulario de Stripe Elements.
     */
    function initializeStripeElements() {
        if (!cardElement) {
            elements = stripe.elements();
            const style = {
                base: {
                    fontSize: '16px',
                    fontFamily: '"Poppins", system-ui, sans-serif'
                }
            };
            cardElement = elements.create('card', { style: style, hidePostalCode: true });
            cardElement.mount('#card-element');

            cardElement.on('change', (event) => {
                if (event.error) {
                    cardErrorsEl.textContent = event.error.message;
                } else {
                    cardErrorsEl.textContent = '';
                }
            });
        }
    }

    // --- Lógica del Modal de Tarjeta  ---
    const addCardBtn = document.getElementById('add-card-btn');
    const addCardModal = document.getElementById('add-card-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelCardBtn = document.getElementById('cancel-card-btn');
    const submitCardBtn = document.getElementById('submit-card-btn'); // Botón "Pagar" dentro del modal
    const cardHolderName = document.getElementById('cardholder-name');
    const cardErrorsEl = document.getElementById('card-errors');

    function openModal() {
        if (addCardModal) {
            initializeStripeElements(); // Inicializa Stripe al abrir el modal
            addCardModal.style.display = 'flex';
        }
    }    
    function closeModal() { if (addCardModal) addCardModal.style.display = 'none'; }

    addCardBtn?.addEventListener('click', openModal);
    closeModalBtn?.addEventListener('click', closeModal);
    cancelCardBtn?.addEventListener('click', closeModal);
  
    form?.addEventListener('submit', (e) => {
        e.preventDefault(); // Evita que el formulario principal se envíe
        openModal();
    });

/**
     * Paso 1: Llamar al backend para crear un Payment Intent.
     */
    async function createPaymentIntent() {
        setLoading(true, 'Iniciando pago...');
        
        const cartItems = window.Cart.getItems();
        if (cartItems.length === 0) {
            showError('Tu carrito está vacío.');
            setLoading(false);
            return false;
        }

        try {
            const response = await fetch('crear_payment_intent.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cartItems: cartItems })
            });

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }
            
            currentClientSecret = data.clientSecret; // Guarda el secret
            return true;

        } catch (error) {
            showError(`Error: ${error.message}`);
            setLoading(false);
            return false;
        }
    }

    /**
     * Paso 2: Confirmar el pago en el cliente (aquí ocurre 3D Secure).
     */
    async function confirmCardPayment() {
        if (!currentClientSecret) {
            showError('No se pudo inicializar el pago.');
            return null;
        }

        setLoading(true, 'Procesando tarjeta...');
        
        const { paymentIntent, error } = await stripe.confirmCardPayment(currentClientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: cardHolderName.value,
                    email: document.getElementById('email').value, // Tomamos el email del form principal
                },
            },
        });

        if (error) {
            showError(error.message);
            setLoading(false);
            return null;
        }

        if (paymentIntent.status === 'succeeded') {
            return paymentIntent; 
        } else {
            showError(`Estado del pago: ${paymentIntent.status}`);
            setLoading(false);
            return null;
        }
    }

    /**
     * Paso 3: Guardar el pedido en nuestra base de datos.
     */
    async function saveOrderToDatabase(paymentIntent) {
        setLoading(true, 'Guardando pedido...');
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const cartItems = window.Cart.getItems();

        try {
            const response = await fetch('procesar_pedido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    formData: data,
                    cartItems: cartItems,
                    discount: currentDiscount,
                    paymentIntentId: paymentIntent.id // Enviamos el ID del pago exitoso
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(`¡Pedido #${result.orderId} creado! Redirigiendo...`);
                window.Cart.clear();
                setTimeout(() => {
                    // *** CREA ESTA PÁGINA DE "GRACIAS" ***
                    window.location.href = `gracias.php?order_id=${result.orderId}`;
                }, 2000);
            } else {
                throw new Error(result.error || 'No se pudo guardar el pedido en la base de datos.');
            }

        } catch (error) {
            showError(`Error crítico: ${error.message}. Tu pago fue procesado, contacta a soporte.`);
            setLoading(false);
        }
    }

    /**
     * Flujo de pago principal (orquestador).
     */
    async function handlePaymentFlow() {
        // Validar campos de formulario (nombre, email, etc.)
        if (!form.checkValidity()) {
            form.reportValidity();
            showError('Por favor, completa la información de envío y contacto.');
            closeModal();
            return;
        }

        // Paso 1: Crear Payment Intent
        const intentCreated = await createPaymentIntent();
        if (!intentCreated) return;

        // Paso 2: Confirmar Pago (en el cliente)
        const paymentIntent = await confirmCardPayment();
        if (!paymentIntent) return;

        // Paso 3: Guardar Pedido (en nuestro backend)
        await saveOrderToDatabase(paymentIntent);
    }
    
    // Listener para el botón "Pagar" DENTRO del modal
    submitCardBtn?.addEventListener('click', handlePaymentFlow);

    // --- Funciones de Utilidad ---
    function setLoading(isLoading, message = '') {
        submitBtn.disabled = isLoading;
        submitCardBtn.disabled = isLoading;
        if (isLoading) {
            paymentStatusEl.textContent = message;
            paymentStatusEl.className = 'status-loading';
        } else {
            paymentStatusEl.textContent = '';
        }
    }

    function showError(message) {
        setLoading(false);
        paymentStatusEl.textContent = message;
        paymentStatusEl.className = 'error-message';
        // También muestra en los errores de la tarjeta si es relevante
        cardErrorsEl.textContent = message;
    }
    
    function showSuccess(message) {
        setLoading(false);
        paymentStatusEl.textContent = message;
        paymentStatusEl.className = 'success-message';
    }

    // --- Inicialización ---
    renderCheckoutCart();
    
    // Listeners
    cartListEl?.addEventListener('change', handleCartListInteraction);
    cartListEl?.addEventListener('click', handleCartListInteraction);
    form?.addEventListener('submit', handlePaymentSubmit);
    
    // Escuchar eventos de 'cart.js' (si el dropdown sigue existiendo)
    document.addEventListener('cart:updated', () => {
        renderCheckoutCart();
    });
});