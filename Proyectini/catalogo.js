document.addEventListener('DOMContentLoaded', () => {
    const productList = document.querySelector('.product-list');
    const viewToggleButtons = document.querySelectorAll('.view-toggle button');
    const API_PRODUCTS_URL = 'api/products.php';
    let products = Array.isArray(window.__INITIAL_PRODUCTS__)
        ? window.__INITIAL_PRODUCTS__
        : [];

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
        }).format(value ?? 0);
    }

    function showFeedback(target, message, type = 'info') {
        if (!target) {
            return;
        }
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

    function createProductCard(product) {
        const card = document.createElement('article');
        card.className = 'product-card';

        if (product.image) {
            const figure = document.createElement('figure');
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
        const price = document.createElement('span');
        price.className = 'price';
        price.textContent = formatCurrency(product.price);
        header.append(title, price);
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

    function renderProducts() {
        if (!productList) {
            return;
        }
        productList.innerHTML = '';

        if (!products.length) {
            const empty = document.createElement('p');
            empty.className = 'lead';
            empty.textContent = 'Pronto agregaremos nuevos productos a nuestro catalogo.';
            productList.appendChild(empty);
            return;
        }

        const fragment = document.createDocumentFragment();
        products.forEach((product) => {
            fragment.appendChild(createProductCard(product));
        });
        productList.appendChild(fragment);
    }

    async function fetchProducts() {
        try {
            const response = await fetch(API_PRODUCTS_URL);
            if (!response.ok) {
                throw new Error('Respuesta no valida del servidor');
            }
            const payload = await response.json();
            if (Array.isArray(payload.data)) {
                products = payload.data;
                renderProducts();
            }
        } catch (error) {
            console.warn('No se pudo actualizar el catalogo, se muestran datos locales.', error);
            renderProducts();
        }
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
    fetchProducts();
});
