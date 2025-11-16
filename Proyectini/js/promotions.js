// promotions.js - Promotions Banner Carousel
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.getElementById('promotions-carousel');
    
    if (!carousel) {
        return; // No promotions carousel on this page
    }
    
    const track = document.getElementById('promotions-track');
    const prevBtn = document.getElementById('promo-prev');
    const nextBtn = document.getElementById('promo-next');
    const indicators = document.querySelectorAll('.promo-indicator');
    
    if (!track) {
        return;
    }
    
    const slides = track.querySelectorAll('.promotion-slide');
    const totalSlides = slides.length;
    
    if (totalSlides === 0) {
        return;
    }
    
    let currentIndex = 0;
    let autoPlayTimer = null;
    let isTransitioning = false;
    
    // Update carousel position
    function updateCarousel(instant = false) {
        if (isTransitioning && !instant) {
            return;
        }
        
        isTransitioning = true;
        
        // Update track position
        const translateX = -currentIndex * 100;
        
        if (instant) {
            track.style.transition = 'none';
        } else {
            track.style.transition = 'transform 800ms cubic-bezier(0.4, 0, 0.2, 1)';
        }
        
        track.style.transform = `translateX(${translateX}%)`;
        
        // Update indicators
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentIndex);
        });
        
        // Update button states (if only one slide, hide controls)
        if (prevBtn && nextBtn) {
            if (totalSlides <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            }
        }
        
        setTimeout(() => {
            isTransitioning = false;
        }, 800);
    }
    
    // Navigate to specific slide
    function goToSlide(index) {
        if (index < 0 || index >= totalSlides || index === currentIndex) {
            return;
        }
        
        currentIndex = index;
        updateCarousel();
        resetAutoPlay();
    }
    
    // Navigate to next slide
    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        updateCarousel();
        resetAutoPlay();
    }
    
    // Navigate to previous slide
    function prevSlide() {
        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        updateCarousel();
        resetAutoPlay();
    }
    
    // Auto-play functionality
    function startAutoPlay() {
        if (totalSlides <= 1) {
            return;
        }
        
        stopAutoPlay();
        autoPlayTimer = setInterval(() => {
            nextSlide();
        }, 5000); // Change slide every 5 seconds
    }
    
    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
    }
    
    function resetAutoPlay() {
        stopAutoPlay();
        startAutoPlay();
    }
    
    // Event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', prevSlide);
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', nextSlide);
    }
    
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => goToSlide(index));
    });
    
    // Pause auto-play on hover
    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);
    
    // Touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    let touchStartY = 0;
    let touchEndY = 0;
    
    carousel.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        stopAutoPlay();
    }, { passive: true });
    
    carousel.addEventListener('touchmove', (e) => {
        touchEndX = e.touches[0].clientX;
        touchEndY = e.touches[0].clientY;
    }, { passive: true });
    
    carousel.addEventListener('touchend', () => {
        const diffX = touchStartX - touchEndX;
        const diffY = Math.abs(touchStartY - touchEndY);
        
        // Only trigger if horizontal swipe (not vertical scroll)
        if (Math.abs(diffX) > 50 && Math.abs(diffX) > diffY) {
            if (diffX > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        } else {
            resetAutoPlay();
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        // Only handle if carousel is in viewport
        const rect = carousel.getBoundingClientRect();
        const inViewport = rect.top < window.innerHeight && rect.bottom > 0;
        
        if (!inViewport) {
            return;
        }
        
        if (e.key === 'ArrowLeft') {
            prevSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
        }
    });
    
    // Pause when page is hidden (tab switch)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoPlay();
        } else {
            startAutoPlay();
        }
    });
    
    // Initialize
    updateCarousel(true);
    startAutoPlay();
    
    // Track impressions (optional - for analytics)
    const trackPromoImpression = (slideIndex) => {
        const slide = slides[slideIndex];
        const promoId = slide.dataset.index;
        
        // You can send this to your analytics service
        console.log('Promotion viewed:', promoId);
        
        // Example: Send to backend
        // fetch('/api/track_promo_view.php', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify({ promo_id: promoId })
        // });
    };
    
    // Track initial impression
    trackPromoImpression(0);
    
    // Track when slide changes
    let previousIndex = 0;
    const observer = new MutationObserver(() => {
        if (currentIndex !== previousIndex) {
            trackPromoImpression(currentIndex);
            previousIndex = currentIndex;
        }
    });
    
    // Observe track for transform changes
    observer.observe(track, {
        attributes: true,
        attributeFilter: ['style']
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        stopAutoPlay();
        observer.disconnect();
    });
});