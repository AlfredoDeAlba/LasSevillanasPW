document.addEventListener('DOMContentLoaded', () => {
    const testimonialContainer = document.querySelector('.testimonial-list');
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

    renderTestimonials();
});
