{{-- Anime.js v4 — LANDING-RICH initialization.
     Progressive enhancement with multiple safety nets.
     1) CSS @keyframes fallback (2s delay) in layout if JS doesn't run
     2) ESM import fails -> forceVisible()
     3) Init throws -> forceVisible()
     4) Init stalls > 1800ms -> forceVisible()
     5) prefers-reduced-motion -> forceVisible()
     Only hides [data-animate] when html.pm-js is set (JS confirmed running). --}}

<script>
(function () {
    'use strict';
    document.documentElement.classList.add('pm-js');
})();
</script>

<script type="module">
(async function () {
    'use strict';

    let animate, stagger, inView, animateOnScroll, scroll;
    try {
        const mod = await import('animejs');
        animate = mod.animate;
        stagger = mod.stagger;
        // v4 scroll module — optional
        inView = mod.inView;
        animateOnScroll = mod.animateOnScroll;
        scroll = mod.scroll;
    } catch (err) {
        console.error('[animejs] failed to load:', err);
        forceVisible();
        return;
    }

    if (!animate || !stagger) {
        forceVisible();
        return;
    }

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        forceVisible();
        return;
    }

    function forceVisible() {
        document.querySelectorAll('[data-animate]:not(.pm-animated)').forEach(function (el) {
            el.style.opacity = '1';
            el.style.transform = 'none';
            el.classList.add('pm-animated');
        });
    }

    const safetyTimer = setTimeout(function () {
        console.warn('[animejs] safety net triggered — forcing visibility.');
        forceVisible();
    }, 1800);

    // Map data-animate variant -> initial transform/opacity
    function initialState(variant) {
        switch (variant) {
            case 'fade-up':    return { opacity: 0, translateY: 60 };
            case 'fade-down':  return { opacity: 0, translateY: -40 };
            case 'fade-left':  return { opacity: 0, translateX: 80 };
            case 'fade-right': return { opacity: 0, translateX: -80 };
            case 'zoom-in':    return { opacity: 0, scale: 0.9 };
            case 'zoom-out':   return { opacity: 0, scale: 1.08 };
            default:           return { opacity: 0, translateY: 40 };
        }
    }

    function applyInitialStyles() {
        document.querySelectorAll('[data-animate]:not(.pm-animated)').forEach(function (el) {
            const v = el.getAttribute('data-animate') || 'fade-up';
            const s = initialState(v);
            el.style.opacity = s.opacity != null ? String(s.opacity) : '';
            if (s.translateY != null) el.style.transform = 'translateY(' + s.translateY + 'px)';
            else if (s.translateX != null) el.style.transform = 'translateX(' + s.translateX + 'px)';
            else if (s.scale != null) el.style.transform = 'scale(' + s.scale + ')';
            else el.style.transform = '';
        });
    }

    function animateIn(el) {
        const v = el.getAttribute('data-animate') || 'fade-up';
        const props = { opacity: [0, 1], duration: 900, ease: 'outExpo' };
        if (v === 'fade-up')    props.translateY = [60, 0];
        else if (v === 'fade-down')  props.translateY = [-40, 0];
        else if (v === 'fade-left')  props.translateX = [80, 0];
        else if (v === 'fade-right') props.translateX = [-80, 0];
        else if (v === 'zoom-in')    props.scale = [0.9, 1];
        else if (v === 'zoom-out')   props.scale = [1.08, 1];
        else props.translateY = [40, 0];

        el.classList.add('pm-animated');
        animate(el, props);
    }

    function resetEl(el) {
        // Cancel any running animation, then snap back to hidden state so
        // a future re-entry will animate in again.
        el.classList.remove('pm-animated');
        const v = el.getAttribute('data-animate') || 'fade-up';
        const s = initialState(v);
        el.style.opacity = s.opacity != null ? String(s.opacity) : '0';
        if (s.translateY != null) el.style.transform = 'translateY(' + s.translateY + 'px)';
        else if (s.translateX != null) el.style.transform = 'translateX(' + s.translateX + 'px)';
        else if (s.scale != null) el.style.transform = 'scale(' + s.scale + ')';
        else el.style.transform = '';
    }

    try {
        applyInitialStyles();

        // --- HERO load animation (immediate, no scroll) ---
        const heroSection = document.querySelector('.pm-hero');
        if (heroSection) {
            const heroEls = heroSection.querySelectorAll('[data-animate]');
            animate(heroEls, {
                opacity: [0, 1],
                translateY: [60, 0],
                translateX: function (el) {
                    const v = el.getAttribute('data-animate');
                    if (v === 'fade-left') return [80, 0];
                    if (v === 'fade-right') return [-80, 0];
                    return 0;
                },
                scale: function (el) {
                    const v = el.getAttribute('data-animate');
                    if (v === 'zoom-in') return [0.9, 1];
                    if (v === 'zoom-out') return [1.08, 1];
                    return 1;
                },
                delay: stagger(110, { start: 150 }),
                duration: 1000,
                ease: 'outExpo',
                onComplete: function () {
                    heroEls.forEach(function (el) { el.classList.add('pm-animated'); });
                },
            });
        }

        // --- Number counter on the hero stats ---
        const counters = document.querySelectorAll('[data-counter]');
        if (counters.length > 0 && 'IntersectionObserver' in window) {
            const cio = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-counter'), 10) || 0;
                    const isTwoDigit = target >= 10;
                    const proxy = { v: isTwoDigit ? target - 10 : 0 };
                    animate(proxy, {
                        v: target,
                        duration: 1600,
                        ease: 'outQuart',
                        delay: 600,
                        onUpdate: function () {
                            el.textContent = String(Math.round(proxy.v)).padStart(isTwoDigit ? 2 : 2, '0');
                        },
                    });
                    cio.unobserve(el);
                });
            }, { threshold: 0.4 });
            counters.forEach(function (c) { cio.observe(c); });
        }

        // --- SCROLL reveals (sections that aren't the hero) ---
        // REVERSIBLE: element animates IN when entering viewport, resets OUT
        // when leaving — so scrolling up re-triggers the animation too.
        const revealEls = document.querySelectorAll(
            '[data-animate]:not(.pm-animated):not(.pm-hero [data-animate])'
        );
        if (revealEls.length > 0 && 'IntersectionObserver' in window) {
            const io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    const el = entry.target;
                    if (entry.isIntersecting) {
                        if (el.classList.contains('pm-animated')) return;
                        // Stagger siblings within the same parent that are also pending
                        const siblings = Array.from(el.parentElement.children).filter(function (c) {
                            return c.hasAttribute('data-animate') && !c.classList.contains('pm-animated');
                        });
                        if (siblings.length > 1) {
                            siblings.forEach(function (s) { s.classList.add('pm-animated'); });
                            animate(siblings, {
                                opacity: [0, 1],
                                translateY: [50, 0],
                                duration: 800,
                                delay: stagger(120),
                                ease: 'outExpo',
                            });
                        } else {
                            animateIn(el);
                        }
                    } else {
                        // Element left the viewport — reset so it animates in again on re-entry
                        resetEl(el);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });
            revealEls.forEach(function (el) { io.observe(el); });
        } else {
            forceVisible();
        }

        // --- SMOOTH HERO PARALLAX (background image moves opposite to scroll) ---
        if (heroSection && window.matchMedia('(min-width: 768px)').matches) {
            let ticking = false;
            window.addEventListener('scroll', function () {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(function () {
                    const y = window.pageYOffset;
                    if (y < window.innerHeight) {
                        heroSection.style.backgroundPosition = 'center ' + (y * 0.35) + 'px';
                    }
                    ticking = false;
                });
            }, { passive: true });
        }

        // --- DISCIPLINES CARD 3D TILT on hover ---
        if (window.matchMedia('(hover: hover)').matches) {
            const cards = document.querySelectorAll('.pm-hero ~ section article, section article');
            cards.forEach(function (card) {
                card.style.transformStyle = 'preserve-3d';
                card.style.transition = 'transform 0.4s ease';
                card.addEventListener('mousemove', function (e) {
                    const r = card.getBoundingClientRect();
                    const x = (e.clientX - r.left) / r.width - 0.5;
                    const y = (e.clientY - r.top) / r.height - 0.5;
                    animate(card, {
                        rotateX: y * -8,
                        rotateY: x * 8,
                        translateZ: 0,
                        duration: 400,
                        ease: 'outQuart',
                    });
                });
                card.addEventListener('mouseleave', function () {
                    animate(card, {
                        rotateX: 0,
                        rotateY: 0,
                        duration: 600,
                        ease: 'outElastic(1, .6)',
                    });
                });
            });
        }

        // --- MAGNETIC PRIMARY BUTTONS ---
        if (window.matchMedia('(hover: hover)').matches) {
            const magBtns = document.querySelectorAll('.pm-btn-primary');
            magBtns.forEach(function (btn) {
                btn.addEventListener('mousemove', function (e) {
                    const r = btn.getBoundingClientRect();
                    const x = e.clientX - r.left - r.width / 2;
                    const y = e.clientY - r.top - r.height / 2;
                    animate(btn, {
                        translateX: x * 0.25,
                        translateY: y * 0.35,
                        duration: 300,
                        ease: 'outQuart',
                    });
                });
                btn.addEventListener('mouseleave', function () {
                    animate(btn, {
                        translateX: 0,
                        translateY: 0,
                        duration: 500,
                        ease: 'outElastic(1, .6)',
                    });
                });
            });
        }

        // --- SUBTLE PULSE on the CTA card ---
        const cta = document.querySelector('.border.border-accent\\/40');
        if (cta) {
            cta.addEventListener('mouseenter', function () {
                animate(cta, {
                    scale: 1.02,
                    duration: 400,
                    ease: 'outQuart',
                });
            });
            cta.addEventListener('mouseleave', function () {
                animate(cta, {
                    scale: 1,
                    duration: 500,
                    ease: 'outElastic(1, .6)',
                });
            });
        }

        // --- NAVBAR subtle shadow on scroll ---
        const nav = document.querySelector('header');
        if (nav) {
            window.addEventListener('scroll', function () {
                if (window.pageYOffset > 8) {
                    nav.style.boxShadow = '0 4px 30px rgba(0,0,0,0.4)';
                    nav.style.transition = 'box-shadow 0.3s ease';
                } else {
                    nav.style.boxShadow = 'none';
                }
            }, { passive: true });
        }

    } catch (err) {
        console.error('[animejs] init error:', err);
        forceVisible();
    }

    clearTimeout(safetyTimer);
})();
</script>
