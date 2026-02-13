/**
 * Homepage JavaScript
 * Pricetag.co.za
 */

document.addEventListener('DOMContentLoaded', function() {
    initHeroSlider();
    initCategoryCardsScroll();
    initProductCarousels();
    initCountdownTimers();
    initRecentlyViewed();
    initNewsletterForm();
});

// Hero Slider
function initHeroSlider() {
    const slider = document.getElementById('hero-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    const prevBtn = document.querySelector('.hero-slider-prev');
    const nextBtn = document.querySelector('.hero-slider-next');

    if (slides.length <= 1) return;

    let currentSlide = 0;
    let autoplayInterval = null;
    const autoplayDelay = parseInt(slider.dataset.autoplay) || 5000;

    function showSlide(index) {
        slides.forEach(function(slide, i) { slide.classList.toggle('is-active', i === index); });
        dots.forEach(function(dot, i) { dot.classList.toggle('is-active', i === index); });
        currentSlide = index;
    }

    function nextSlide() { showSlide((currentSlide + 1) % slides.length); }
    function prevSlide() { showSlide((currentSlide - 1 + slides.length) % slides.length); }

    function startAutoplay() {
        if (autoplayInterval) clearInterval(autoplayInterval);
        autoplayInterval = setInterval(nextSlide, autoplayDelay);
    }

    function stopAutoplay() {
        if (autoplayInterval) clearInterval(autoplayInterval);
    }

    if (nextBtn) nextBtn.addEventListener('click', function() { nextSlide(); startAutoplay(); });
    if (prevBtn) prevBtn.addEventListener('click', function() { prevSlide(); startAutoplay(); });

    dots.forEach(function(dot, index) {
        dot.addEventListener('click', function() { showSlide(index); startAutoplay(); });
    });

    // Touch support
    var touchStartX = 0;
    slider.addEventListener('touchstart', function(e) {
        touchStartX = e.touches[0].clientX;
        stopAutoplay();
    }, { passive: true });

    slider.addEventListener('touchend', function(e) {
        var touchEndX = e.changedTouches[0].clientX;
        var diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) nextSlide(); else prevSlide();
        }
        startAutoplay();
    }, { passive: true });

    startAutoplay();
    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', startAutoplay);
}

// Category Cards Scroll
function initCategoryCardsScroll() {
    var container = document.querySelector('.category-cards-container');
    if (!container) return;

    var scroll = container.querySelector('.category-cards-scroll');
    var prevBtn = container.querySelector('.category-scroll-prev');
    var nextBtn = container.querySelector('.category-scroll-next');

    if (!scroll) return;

    var scrollAmount = 250;

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            scroll.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            scroll.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }

    // Mouse drag scrolling with momentum (like touch on phone)
    var isDown = false;
    var hasDragged = false;
    var startX = 0;
    var startScrollLeft = 0;
    var lastX = 0;
    var lastTime = 0;
    var velocity = 0;
    var momentumId = null;

    scroll.style.cursor = 'grab';

    // Prevent browser's native image drag from hijacking our scroll
    scroll.addEventListener('dragstart', function(e) {
        e.preventDefault();
    });

    function stopMomentum() {
        if (momentumId) {
            cancelAnimationFrame(momentumId);
            momentumId = null;
        }
    }

    function momentumLoop() {
        if (Math.abs(velocity) < 0.5) {
            momentumId = null;
            return;
        }
        scroll.scrollLeft -= velocity;
        velocity *= 0.95;
        momentumId = requestAnimationFrame(momentumLoop);
    }

    scroll.addEventListener('mousedown', function(e) {
        if (e.button !== 0) return;
        e.preventDefault();
        stopMomentum();
        isDown = true;
        hasDragged = false;
        startX = e.pageX;
        lastX = e.pageX;
        lastTime = Date.now();
        velocity = 0;
        startScrollLeft = scroll.scrollLeft;
        scroll.style.cursor = 'grabbing';
        scroll.style.userSelect = 'none';
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        var dx = e.pageX - startX;
        if (Math.abs(dx) > 5) {
            hasDragged = true;
        }
        var now = Date.now();
        var dt = now - lastTime;
        if (dt > 0) {
            velocity = (e.pageX - lastX) / dt * 16;
        }
        lastX = e.pageX;
        lastTime = now;
        scroll.scrollLeft = startScrollLeft - dx;
    });

    document.addEventListener('mouseup', function(e) {
        if (!isDown) return;
        isDown = false;
        scroll.style.cursor = 'grab';
        scroll.style.removeProperty('user-select');

        if (hasDragged) {
            // Was a drag — apply momentum, don't navigate
            if (Math.abs(velocity) > 1) {
                momentumLoop();
            }
        } else {
            // Was a click — find the link under the cursor and navigate
            var target = document.elementFromPoint(e.clientX, e.clientY);
            if (target) {
                var link = target.closest('a');
                if (link && scroll.contains(link)) {
                    window.location.href = link.href;
                }
            }
        }
    });
}

// Product Carousels
function initProductCarousels() {
    document.querySelectorAll('.product-carousel').forEach(function(carousel) {
        var track = carousel.querySelector('.product-carousel-track');
        var prevBtn = carousel.querySelector('[data-carousel-prev]');
        var nextBtn = carousel.querySelector('[data-carousel-next]');

        if (!track) return;

        var scrollAmount = track.clientWidth * 0.8;

        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }
    });
}

// Countdown Timer
function initCountdownTimers() {
    document.querySelectorAll('[data-countdown]').forEach(function(timer) {
        var endDate = new Date(timer.dataset.countdown).getTime();

        function updateTimer() {
            var now = new Date().getTime();
            var distance = endDate - now;

            if (distance < 0) {
                timer.innerHTML = '<span class="countdown-expired">Sale Ended</span>';
                return;
            }

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            var daysEl = timer.querySelector('[data-days]');
            var hoursEl = timer.querySelector('[data-hours]');
            var minutesEl = timer.querySelector('[data-minutes]');
            var secondsEl = timer.querySelector('[data-seconds]');

            if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    });
}

// Recently Viewed
function initRecentlyViewed() {
    var section = document.getElementById('recently-viewed-section');
    var container = document.getElementById('recently-viewed-products');
    var clearBtn = document.getElementById('clear-recently-viewed');

    if (!section || !container) return;

    function getRecentlyViewed() {
        try { return JSON.parse(localStorage.getItem('recentlyViewed') || '[]'); }
        catch(e) { return []; }
    }

    function renderRecentlyViewed() {
        var products = getRecentlyViewed();
        if (products.length === 0) { section.style.display = 'none'; return; }

        section.style.display = 'block';
        container.innerHTML = products.map(function(product) {
            return '<div class="product-carousel-slide">' +
                '<article class="product-card">' +
                    '<div class="product-card-image">' +
                        '<a href="' + product.url + '">' +
                            (product.image ? '<img src="' + product.image + '" alt="' + product.name + '" class="product-card-img" loading="lazy">' : '<div class="product-card-img" style="background: var(--color-neutral-100);"></div>') +
                        '</a>' +
                    '</div>' +
                    '<div class="product-card-body">' +
                        '<h3 class="product-card-title"><a href="' + product.url + '">' + product.name + '</a></h3>' +
                        '<div class="product-card-price"><span class="product-card-price-current">' + product.price + '</span></div>' +
                    '</div>' +
                '</article>' +
            '</div>';
        }).join('');

        initProductCarousels();
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            localStorage.removeItem('recentlyViewed');
            section.style.display = 'none';
        });
    }

    renderRecentlyViewed();
}

// Newsletter Form
function initNewsletterForm() {
    var form = document.getElementById('newsletter-form');
    if (!form) return;

    var emailInput = document.getElementById('newsletter-email');
    var errorEl = document.getElementById('newsletter-error');
    var successEl = document.getElementById('newsletter-success');
    var btnText = form.querySelector('.newsletter-btn-text');
    var btnLoading = form.querySelector('.newsletter-btn-loading');
    var btnArrow = form.querySelector('.newsletter-btn-arrow');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var email = emailInput.value.trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorEl.textContent = 'Please enter a valid email address';
            errorEl.style.display = 'block';
            successEl.style.display = 'none';
            return;
        }

        btnText.style.display = 'none';
        if (btnArrow) btnArrow.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        errorEl.style.display = 'none';

        var formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                successEl.style.display = 'block';
                emailInput.value = '';
            } else {
                errorEl.textContent = data.message || 'Something went wrong. Please try again.';
                errorEl.style.display = 'block';
            }
        })
        .catch(function() {
            errorEl.textContent = 'Something went wrong. Please try again.';
            errorEl.style.display = 'block';
        })
        .finally(function() {
            btnText.style.display = 'inline';
            if (btnArrow) btnArrow.style.display = 'inline';
            btnLoading.style.display = 'none';
        });
    });
}
