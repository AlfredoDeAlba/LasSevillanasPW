// promotions.js - Promotions Banner Carousel
document.addEventListener('DOMContentLoaded', () => {
    console.log('Promotions.js loaded');
    
    // FIXED: Use the correct selector that matches your HTML
    const carousel = document.querySelector('[data-promo-slider]');
    
    if (!carousel) {
        console.log('No promotions carousel found on this page');
        return;
    }
    
    console.log('Carousel found:', carousel);
    
    const track = document.getElementById('promotions-track');
    // FIXED: Use data attributes that match your HTML
    const prevBtn = document.querySelector('[data-promo-prev]');
    const nextBtn = document.querySelector('[data-promo-next]');
    const indicatorsContainer = document.getElementById('promo-indicators');
    
    if (!track) {
        console.error('Track not found');
        return;
    }
    
    // FIXED: Get the actual slides from your HTML structure
    const slides = track.querySelectorAll('.promo-banner');
    const totalSlides = slides.length;
    
    console.log('Carousel Stats:');
    console.log('- Total slides:', totalSlides);
    console.log('- Track element:', track);
    console.log('- Slides:', slides);
    console.log('- Prev button:', prevBtn);
    console.log('- Next button:', nextBtn);
    
    if (totalSlides === 0) {
        console.error('No slides found in track');
        return;
    }
    
    let currentIndex = 0;
    let autoPlayTimer = null;
    let isTransitioning = false;
    let isAutoPlaying = true;
    
    // Create indicators dynamically
    if (indicatorsContainer && totalSlides > 1) {
        indicatorsContainer.innerHTML = '';
        for (let i = 0; i < totalSlides; i++) {
            const indicator = document.createElement('button');
            indicator.className = 'promo-indicator';
            indicator.setAttribute('aria-label', `Ir a promoción ${i + 1}`);
            if (i === 0) indicator.classList.add('active');
            indicator.addEventListener('click', () => goToSlide(i));
            indicatorsContainer.appendChild(indicator);
        }
    }
    
    const indicators = indicatorsContainer ? indicatorsContainer.querySelectorAll('.promo-indicator') : [];
    
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
        
        // Update button states
        if (prevBtn && nextBtn) {
            if (totalSlides <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                prevBtn.style.display = '';
                nextBtn.style.display = '';
            }
        }
        
        setTimeout(() => {
            isTransitioning = false;
        }, 800);
        
        console.log(`Now showing slide ${currentIndex + 1}/${totalSlides}`);
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
        if (totalSlides <= 1 || !isAutoPlaying) {
            return;
        }
        
        stopAutoPlay();
        autoPlayTimer = setInterval(() => {
            nextSlide();
        }, 5000); // Change slide every 5 seconds
        
        console.log('Auto-play started');
    }
    
    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
            console.log('Auto-play stopped');
        }
    }
    
    function resetAutoPlay() {
        stopAutoPlay();
        startAutoPlay();
    }
    
    // Toggle auto-play
    const autoPlayToggle = document.getElementById('promo-autoplay');
    if (autoPlayToggle) {
        const playIcon = autoPlayToggle.querySelector('.play-icon');
        const pauseIcon = autoPlayToggle.querySelector('.pause-icon');
        
        autoPlayToggle.addEventListener('click', () => {
            isAutoPlaying = !isAutoPlaying;
            
            if (isAutoPlaying) {
                startAutoPlay();
                if (playIcon) playIcon.style.display = '';
                if (pauseIcon) pauseIcon.style.display = 'none';
            } else {
                stopAutoPlay();
                if (playIcon) playIcon.style.display = 'none';
                if (pauseIcon) pauseIcon.style.display = '';
            }
        });
        
        // Set initial state
        if (playIcon) playIcon.style.display = 'none';
        if (pauseIcon) pauseIcon.style.display = '';
    }
    
    // Event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
        });
    }
    
    // Pause auto-play on hover
    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', () => {
        if (isAutoPlaying) startAutoPlay();
    });
    
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
            if (isAutoPlaying) startAutoPlay();
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
            e.preventDefault();
            prevSlide();
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            nextSlide();
        }
    });
    
    // Pause when page is hidden (tab switch)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoPlay();
        } else {
            if (isAutoPlaying) startAutoPlay();
        }
    });
    
    // Initialize
    updateCarousel(true);
    startAutoPlay();
    
    // Track impressions (optional - for analytics)
    const trackPromoImpression = (slideIndex) => {
        const slide = slides[slideIndex];
        const promoId = slide.dataset.promoId;
        
        console.log('Promotion viewed:', promoId);
        
        // Optional: Send to backend for analytics
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
    
    // Countdown timers for promotions
    const updateCountdowns = () => {
        document.querySelectorAll('.promo-timer[data-end-date]').forEach(timer => {
            const endDate = new Date(timer.dataset.endDate);
            const now = new Date();
            const diff = endDate - now;
            
            if (diff <= 0) {
                timer.textContent = '¡Oferta terminada!';
                timer.style.color = 'var(--color-error)';
            } else {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                
                if (days > 0) {
                    timer.textContent = `Termina en ${days} día${days !== 1 ? 's' : ''}`;
                } else if (hours > 0) {
                    timer.textContent = `Termina en ${hours} hora${hours !== 1 ? 's' : ''}`;
                } else {
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    timer.textContent = `Termina en ${minutes} minuto${minutes !== 1 ? 's' : ''}`;
                }
            }
        });
    };
    
    // Update countdowns every minute
    updateCountdowns();
    setInterval(updateCountdowns, 60000);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        stopAutoPlay();
        observer.disconnect();
    });
    
    console.log('Promotions carousel initialized successfully');
});