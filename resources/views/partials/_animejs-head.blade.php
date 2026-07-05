{{-- Anime.js v4 — shared setup. Include once near the end of <head>. --}}
@once
<script>
    // Mark <html> so CSS can show everything if JS is disabled / module fails.
    document.documentElement.classList.add('pm-js');
</script>
<style>
    /* Initial state for anime.js targets (only when JS is available) */
    .pm-js [data-animate] {
        opacity: 0;
        will-change: transform, opacity;
    }
    .pm-js [data-animate="fade-up"]    { transform: translateY(20px); }
    .pm-js [data-animate="fade-down"]  { transform: translateY(-16px); }
    .pm-js [data-animate="fade-left"]  { transform: translateX(-24px); }
    .pm-js [data-animate="fade-right"] { transform: translateX(24px); }
    .pm-js [data-animate="zoom-in"]    { transform: scale(0.94); }
    /* Once anime.js finishes, release the layer to free GPU memory. */
    .pm-js [data-animate].pm-animated { will-change: auto; }
    /* Native fallback: if scripting is disabled, keep everything visible. */
    @media (scripting: none) {
        [data-animate] { opacity: 1 !important; transform: none !important; }
    }
</style>
<script type="importmap">
{
    "imports": {
        "animejs": "https://cdn.jsdelivr.net/npm/animejs@4.4.1/dist/bundles/anime.esm.min.js"
    }
}
</script>
@endonce
