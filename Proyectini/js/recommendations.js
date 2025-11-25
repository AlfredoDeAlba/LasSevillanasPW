// recommendations.js - Recommendations Carousel Handler
document.addEventListener('DOMContentLoaded', () => {
    const track = document.getElementById('recommendations-track');
    const prevBtn = document.querySelector('[data-rec-control="prev"]');
    const nextBtn = document.querySelector('[data-rec-control="next"]');
    const indicatorsContainer = document.getElementById('rec-indicators');
    
    if (!track) {
        return; // No recommendations section
    }

    let currentIndex = 0;
    let cardsPerView = getCardsPerView();
    let totalCards = track.children.length;
    let maxIndex = Math.max(0, totalCards - cardsPerView);

    // Get number of visible cards based on screen width
    function getCardsPerView() {
        const width = window.innerWidth;
        if (width >= 1200) return 4;  // 4. Desktop (>= 1200px)
        if (width > 900) return 2;   // 2. Tablet (> 900px)
        return 1;                      // 1. Mobile (<= 900px)
    }

    // Calculate card width including gap
    function getCardWidth() {
        const firstCard = track.children[0];
        if (!firstCard) return 0;
        
        const cardRect = firstCard.getBoundingClientRect();
        const trackStyle = window.getComputedStyle(track);
        const gap = parseFloat(trackStyle.gap) || 0;
        
        return cardRect.width + gap;
    }

    // Update carousel position
    function updateCarousel() {
        const cardWidth = getCardWidth();
        const translateX = -currentIndex * cardWidth;
        
        track.style.transform = `translateX(${translateX}px)`;
        
        // Update button states
        if (prevBtn) {
            prevBtn.disabled = currentIndex === 0;
        }
        if (nextBtn) {
            nextBtn.disabled = currentIndex >= maxIndex;
        }
        
        updateIndicators();
    }

    // Create and update indicators
    function createIndicators() {
        if (!indicatorsContainer) return;
        
        indicatorsContainer.innerHTML = '';
        
        const numIndicators = Math.ceil(totalCards / cardsPerView);
        
        for (let i = 0; i < numIndicators; i++) {
            const indicator = document.createElement('button');
            indicator.className = 'indicator';
            indicator.setAttribute('aria-label', `Ir a grupo ${i + 1}`);
            indicator.addEventListener('click', () => goToPage(i));
            indicatorsContainer.appendChild(indicator);
        }
        
        updateIndicators();
    }

    function updateIndicators() {
        if (!indicatorsContainer) return;
        
        const indicators = indicatorsContainer.querySelectorAll('.indicator');
        const currentPage = Math.floor(currentIndex / cardsPerView);
        
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentPage);
        });
    }

    function goToPage(pageIndex) {
        currentIndex = Math.min(pageIndex * cardsPerView, maxIndex);
        updateCarousel();
    }

    // Navigation functions
    function movePrev() {
        if (currentIndex > 0) {
            currentIndex = Math.max(0, currentIndex - cardsPerView);
            updateCarousel();
        }
    }

    function moveNext() {
        if (currentIndex < maxIndex) {
            currentIndex = Math.min(maxIndex, currentIndex + cardsPerView);
            updateCarousel();
        }
    }

    // Event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', movePrev);
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', moveNext);
    }

    // Touch/swipe support - improved for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    let touchStartY = 0;
    let touchEndY = 0;
    let isDragging = false;
    
    track.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isDragging = false;
    }, { passive: true });
    
    track.addEventListener('touchmove', (e) => {
        touchEndX = e.touches[0].clientX;
        touchEndY = e.touches[0].clientY;
        
        // Detect if user is swiping horizontally
        const diffX = Math.abs(touchStartX - touchEndX);
        const diffY = Math.abs(touchStartY - touchEndY);
        
        if (diffX > diffY && diffX > 10) {
            isDragging = true;
        }
    }, { passive: true });
    
    track.addEventListener('touchend', () => {
        if (!isDragging) return;
        
        // Lower threshold for mobile (easier to swipe)
        const swipeThreshold = window.innerWidth <= 900 ? 30 : 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                // Swiped left - go to next
                moveNext();
            } else {
                // Swiped right - go to previous
                movePrev();
            }
        }
        
        isDragging = false;
    });

    // Keyboard navigation
    track.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            movePrev();
        } else if (e.key === 'ArrowRight') {
            moveNext();
        }
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const newCardsPerView = getCardsPerView();
            
            if (newCardsPerView !== cardsPerView) {
                cardsPerView = newCardsPerView;
                maxIndex = Math.max(0, totalCards - cardsPerView);
                currentIndex = Math.min(currentIndex, maxIndex);
                createIndicators();
            }
            
            updateCarousel();
        }, 250);
    });

    // Quick add to cart functionality
    const quickAddButtons = track.querySelectorAll('.quick-add-btn');
    
    quickAddButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = button.dataset.productId;
            const productName = button.dataset.productName;
            const productPrice = parseFloat(button.dataset.productPrice);
            const productImage = button.dataset.productImage;
            
            // Check if Cart API is available
            if (typeof window.Cart === 'undefined') {
                console.error('Cart API not available');
                return;
            }
            
            // Add to cart
            try {
                window.Cart.addItem({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage
                }, 1);
                
                // Visual feedback
                button.textContent = '✓ Agregado';
                button.style.background = '#1d9a6c';
                
                setTimeout(() => {
                    button.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Agregar
                    `;
                    button.style.background = '';
                }, 2000);
            } catch (error) {
                console.error('Error adding to cart:', error);
                button.textContent = '✗ Error';
                button.style.background = '#DF3F40';
                
                setTimeout(() => {
                    button.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        Agregar
                    `;
                    button.style.background = '';
                }, 2000);
            }
        });
    });

    // Initialize
    createIndicators();
    updateCarousel();

    // Track carousel interactions for analytics (optional)
    const trackInteraction = (action, productId = null) => {
        // You can send this to your analytics service
        console.log('Recommendation carousel:', action, productId);
    };

    prevBtn?.addEventListener('click', () => trackInteraction('prev_click'));
    nextBtn?.addEventListener('click', () => trackInteraction('next_click'));
    
    quickAddButtons.forEach(button => {
        button.addEventListener('click', () => {
            trackInteraction('quick_add', button.dataset.productId);
        });
    });
});