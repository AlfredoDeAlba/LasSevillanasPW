document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const themeToggle = document.querySelector('.theme-toggle');
    const THEME_STORAGE_KEY = 'dulces-theme-preference';

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

    function setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e){
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoVIew({ behavior: 'smooth' });
                }
            });
        });
    }
    /*
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
    */

    const initialTheme = loadThemePreference();
    applyTheme(initialTheme);
    setupSmoothScroll();

    themeToggle?.addEventListener('click', () => {
        const nextTheme = body.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
        persistTheme(nextTheme);
    });
})