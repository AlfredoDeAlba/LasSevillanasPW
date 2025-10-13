document.addEventListener('DOMContentLoaded', () => {
    const productList = document.querySelector('.product-list');
    const viewToggleButtons = document.querySelectorAll('.view-toggle button');

    const API_PRODUCTS_URL = 'api/products.php';

    let products = Array.isArray(window.__INITIAL_PRODUCTS__)
    ? window.__INITIAL_PRODUCTS__
    : [];

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
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

        const buyBottomHTML =  document.createElement('a');
        buyBottomHTML.href = `vista_producto.php?id_producto=${product.id}`
        buyBottomHTML.className = 'primary'
        buyBottomHTML.role = 'button'
        buyBottomHTML.textContent = 'Añadir al carrito';

        const moreLink = document.createElement('a');
        moreLink.href = '#contacto';
        moreLink.textContent = 'Solicitar info';
        moreLink.setAttribute('aria-label', `Solicitar información de ${product.name}`);

        footer.append(/*addButton*/buyBottomHTML, moreLink);
        card.appendChild(footer);

        return card;
    }

    function renderProducts() {
        if (!productList) return;
        productList.innerHTML = '';

        if (!products.length) {
            const empty = document.createElement('p');
            empty.className = 'lead';
            empty.textContent = 'Pronto añadiremos nuevos productos a nuestro catálogo.';
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
                throw new Error('Respuesta no válida del servidor');
            }
            const payload = await response.json();
            if (Array.isArray(payload.data)) {
                products = payload.data;
                renderProducts();
            }
        } catch (error) {
            console.warn('No se pudo actualizar el catálogo, se muestran datos locales.', error);
            renderProducts();
        }
    }

    function setupViewToggle() {
        if (!productList || viewToggleButtons.length === 0) return;

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