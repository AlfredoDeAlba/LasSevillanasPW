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

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
        }).format(value ?? 0);
    }

    function showFeedback(target, message, type = 'info') {
        if (!target) { return ; }
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
            image.src =  product.image;
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

    function handleFilterChange(categoryId){
        renderProducts(categoryId);
        if(!categorySelect || !categoryButtonContainer) return;
        categorySelect.value = categoryId;
        categoryButtonContainer.querySelectorAll('button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.categoryId == categoryId);
        });
    }

    // --- Función para renderizar los botones de filtro ---
    function renderCategories(categories) {
        if (!categoryButtonContainer || !categorySelect) {
            console.warn('no se encontraron los contenedores de categorias');
            return;
        }
        categoryButtonContainer.innerHTML = ''; // Limpiar por si acaso
        categorySelect.innerHTML = '';

        // Usamos una clase similar a 'view-toggle' para reutilizar estilos
        const group = document.createElement('div');
        group.className = 'toggle-group'; 
        group.setAttribute('role', 'group');
        group.setAttribute('aria-label', 'Filtrar por categoría');

        const allCategories = [
            { id_categoria: 'Todos', nombre_categoria: 'Todos'},
            ...categories
        ];

        allCategories.forEach((category, index) => {
            const categoryId = category.id_categoria;
            const categoryName = category.nombre_categoria;

            // --- Crear Botón ---
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = categoryName;
            button.dataset.categoryId = categoryId; 
            if (index === 0) button.classList.add('active');

            button.addEventListener('click', () => {
                // Llama a la nueva función controladora
                handleFilterChange(categoryId);
            });
            group.appendChild(button);

            // --- Crear Opción de Dropdown ---
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

    // --- Funcion: Render Products, muestra tantos los productos como filtrados por categoria ---
    function renderProducts(filterCategoryId = 'Todos') {
        if (!productList) {
            return;
        }
        productList.innerHTML = '';

        // --- Lógica de filtrado ---
        const filteredProducts = (filterCategoryId === 'Todos')
            ? products // Si es 'Todos', usa la lista completa
            : products.filter(p => p.id_categoria == filterCategoryId);
        // --- Fin lógica de filtrado ---

        if (!filteredProducts.length) { // Comprueba la lista filtrada
            const empty = document.createElement('p');
            empty.className = 'lead';
            // Mensaje contextual
            empty.textContent = (filterCategoryId === 'Todos')
                ? 'Pronto agregaremos nuevos productos a nuestro catalogo.'
                : `No se encontraron productos en la categoría.`;
            productList.appendChild(empty);
            return;
        }
        const fragment = document.createDocumentFragment();
        // Itera sobre la lista filtrada
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
    // Pasamos las categorías reales (de window) a la función
    renderCategories(categories); 
    // Renderiza 'Todos' por defecto
    renderProducts();
});
