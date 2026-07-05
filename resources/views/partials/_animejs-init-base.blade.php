{{-- Anime.js v4 initialization with progressive enhancement.
     Loads via ESM dynamic import.
     Safety nets:
       1) If ESM fails to load -> forceVisible()
       2) If init throws -> forceVisible()
       3) If init stalls > 1500ms -> forceVisible()
       4) If JS itself doesn't run at all -> CSS @keyframes fallback (2s delay) in layout
     The CSS [data-animate] is only hidden when html.pm-js is set (JS confirmed running). --}}

<script>
(function () {
    'use strict';
    // Mark JS as available BEFORE awaiting ESM.
    // This way even if animejs fails, the [data-animate] is only hidden if JS was running.
    document.documentElement.classList.add('pm-js');
})();
</script>

<script type="module">
(async function () {
    'use strict';

    let animate, stagger;
    try {
        const mod = await import('animejs');
        animate = mod.animate;
        stagger = mod.stagger;
    } catch (err) {
        console.error('[animejs] failed to load:', err);
        forceVisible();
        return;
    }

    if (!animate || !stagger) {
        forceVisible();
        return;
    }

    // Respect prefers-reduced-motion
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

    // Safety net: if anything below throws or stalls, force everything visible.
    const safetyTimer = setTimeout(function () {
        console.warn('[animejs] safety net triggered — forcing visibility.');
        forceVisible();
    }, 1500);

    try {
        // 1) Hero entrance — run on page load
        const heroTargets = document.querySelectorAll('.pm-hero [data-animate]');
        if (heroTargets.length > 0) {
            animate(heroTargets, {
                opacity: [0, 1],
                translateY: [30, 0],
                delay: stagger(80, { start: 100 }),
                duration: 700,
                ease: 'outQuart',
                onComplete: function () {
                    heroTargets.forEach(function (el) { el.classList.add('pm-animated'); });
                },
            });
        }

        // 2) Scroll-triggered reveals — IntersectionObserver.
        //    Strategy: per-element observer (not per-section) so any [data-animate]
        //    anywhere on the page gets animated, even if it isn't wrapped in a
        //    [data-reveal] section (e.g. events page uses data-animate on bare elements).
        const revealEls = document.querySelectorAll('[data-animate]:not(.pm-animated):not(.pm-hero [data-animate])');
        if ('IntersectionObserver' in window && revealEls.length > 0) {
            const io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    const el = entry.target;
                    if (el.classList.contains('pm-animated')) return;
                    animate(el, {
                        opacity: [0, 1],
                        translateY: [24, 0],
                        duration: 600,
                        ease: 'outQuart',
                        onComplete: function () { el.classList.add('pm-animated'); },
                    });
                    io.unobserve(el);
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -5% 0px' });

            revealEls.forEach(function (el) { io.observe(el); });
        } else {
            forceVisible();
        }
    } catch (err) {
        console.error('[animejs] init error:', err);
        forceVisible();
    }

    clearTimeout(safetyTimer);
})();
</script>
