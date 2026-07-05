<button onclick="pmToggleTheme()" aria-label="Toggle light/dark theme"
        class="flex items-center justify-center text-white/65 hover:text-white transition" style="width:32px;height:32px">
    <svg class="pm-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
    </svg>
    <svg class="pm-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="4"/>
        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
    </svg>
</button>
<script>
    function pmToggleTheme() {
        var html = document.documentElement;
        if (html.getAttribute('data-theme') === 'light') {
            html.removeAttribute('data-theme');
            localStorage.setItem('pm-theme', 'dark');
        } else {
            html.setAttribute('data-theme', 'light');
            localStorage.setItem('pm-theme', 'light');
        }
    }
</script>
