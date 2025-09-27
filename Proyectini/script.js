const body = document.body;
const themeToggle = document.querySelector('.theme-toggle');
const productList = document.querySelector('.product-list');
const viewToggleButtons = document.querySelectorAll('.view-toggle button');
const testimonialContainer = document.querySelector('.testimonial-list');

const THEME_STORAGE_KEY = 'dulces-theme-preference';
const API_PRODUCTS_URL = 'api/products.php';

let products = Array.isArray(window.__INITIAL_PRODUCTS__)
    ? window.__INITIAL_PRODUCTS__
    : [];

const testimonials = [
    {
        quote: 'El mejor dulce de leche que he probado, perfecto para regalar y sorprender a la familia.',
        author: 'María González',
        location: 'CDMX',
    },
    {
        quote: 'La atención es impecable y los sabores nos transportan directo a nuestra infancia.',
        author: 'Jorge Ramírez',
        location: 'Guadalajara',
    },
    {
        quote: 'La cajeta envinada es mi favorita, siempre la pido para eventos especiales.',
        author: 'Daniela Torres',
        location: 'Querétaro',
    },
];

function updateThemeToggle(theme) {
    if (!themeToggle) return;
    const sunIcon = themeToggle.querySelector('.icon-sun');
    const moonIcon = themeToggle.querySelector('.icon-moon');
    const label = themeToggle.querySelector('.label');
    const isDark = theme === 'dark';

    if (sunIcon && moonIcon) {
        sunIcon.hidden = isDark;
        moonIcon.hidden = !isDark;
    }

    if (label) {
        label.textContent = isDark ? 'Modo oscuro' : 'Modo claro';
    }

    themeToggle.setAttribute('aria-pressed', String(isDark));
}

function applyTheme(theme) {
    body.dataset.theme = theme;
    updateThemeToggle(theme);
}

function persistTheme(theme) {
    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (error) {
        console.warn('No se pudo guardar la preferencia de tema.', error);
    }
}

function loadThemePreference() {
    try {
        const stored = localStorage.getItem(THEME_STORAGE_KEY);
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
    } catch (error) {
        // Se ignora la preferencia almacenada si falla el acceso.
    }
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    return prefersDark ? 'dark' : 'light';
}

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
    const addButton = document.createElement('button');
    addButton.className = 'primary';
    addButton.type = 'button';
    addButton.textContent = 'Añadir al carrito';

    const moreLink = document.createElement('a');
    moreLink.href = '#contacto';
    moreLink.textContent = 'Solicitar info';
    moreLink.setAttribute('aria-label', `Solicitar información de ${product.name}`);

    footer.append(addButton, moreLink);
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

function renderTestimonials() {
    if (!testimonialContainer) return;
    const fragment = document.createDocumentFragment();

    testimonials.forEach(({ quote, author, location }) => {
        const card = document.createElement('article');
        card.className = 'testimonial-card';

        const text = document.createElement('p');
        text.textContent = `“${quote}”`;

        const authorLine = document.createElement('p');
        authorLine.className = 'author';
        authorLine.textContent = `${author} · ${location}`;

        card.append(text, authorLine);
        fragment.appendChild(card);
    });

    testimonialContainer.innerHTML = '';
    testimonialContainer.appendChild(fragment);
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

function setupSmoothScroll() {
    const scrollTriggers = document.querySelectorAll('[data-scroll]');
    scrollTriggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            const targetSelector = trigger.getAttribute('data-scroll');
            const target = document.querySelector(targetSelector);
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        });
    });
}

function init() {
    const initialTheme = loadThemePreference();
    applyTheme(initialTheme);

    themeToggle?.addEventListener('click', () => {
        const nextTheme = body.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
        persistTheme(nextTheme);
    });

    renderProducts();
    renderTestimonials();
    setupViewToggle();
    setupSmoothScroll();
    fetchProducts();
}

document.addEventListener('DOMContentLoaded', init);
