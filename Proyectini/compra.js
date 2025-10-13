document.addEventListener('DOMContentLoaded', function() {
    //Logica para la parte de la tarjeta, archivo de compra.php
    const addCardBtn = document.getElementById('add-card-btn');
    const addCardModal = document.getElementById('add-card-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelCardBtn = document.getElementById('cancel-card-btn');

    const quantityInput = document.getElementById('quantity');
    const productPriceElement = document.getElementById('product-price');
    const subtotalAmountElement = document.getElementById('subtotal-amount');
    const totalAmountElement = document.getElementById('total-amount')
    const discountAmountElement = document.getElementById('discount-amount');
    const stockErrorMessage = document.getElementById('stock-error-message');

    // para abrir y cerrar el modal de la tarjeta
    function openModal() {
        if (addCardModal) addCardModal.style.display = 'flex';
    }
    function closeModal() {
        if (addCardModal) addCardModal.style.display = 'none';
    }

    // eventos de los botones del modal
    if (addCardBtn) {
        addCardBtn.addEventListener('click', openModal);
    }
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    if (cancelCardBtn) {
        cancelCardBtn.addEventListener('click', closeModal);
    }
    // tambien si se clickea al fondo
    if (addCardModal) {
        addCardModal.addEventListener('click', function(event) {
            if (event.target === addCardModal) {
                closeModal();
            }
        });
    }

    // calculo de totales inicial
    function formatCurrency(amount) {
        return `$${amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;
    }
    //funcion para el calculo dinamico al elegir el numero de productos a elegir
    function updateTotals() {
        if (stockErrorMessage) {
            stockErrorMessage.textContent = '';
        }

        let quantity = parseInt(quantityInput.value, 10);
        const maxStock = parseInt(quantityInput.max, 10);

        if(!isNaN(quantity) && quantity > maxStock) {
            quantityInput.value = maxStock;
            quantity = maxStock;
            if(stockErrorMessage) {
                stockErrorMessage.textContent = 'Sobrepasa el stock actual';
            }
        }

        if(isNaN(quantity) || quantity < 1) {
            subtotalAmountElement.innerText = formatCurrency(0);
            totalAmountElement.innerText = formatCurrency(0);
            return;
        }

        const basePrice = parseFloat(productPriceElement.innerText.replace(/[$,]/g, ''));
        const discount = parseFloat(discountAmountElement.innerText.replace(/[$,]/g, '')) || 0;
        const subtotal = basePrice * quantity;
        const total = subtotal - discount;

        subtotalAmountElement.innerText = formatCurrency(subtotal);
        totalAmountElement.innerText = formatCurrency(total > 0 ? total :0);
    }

    if(quantityInput) {
        quantityInput.addEventListener('change', updateTotals);
        quantityInput.addEventListener('keyup', updateTotals);
    }

    // logica de Stripe o tarjeta asociada ira aqui
}); 