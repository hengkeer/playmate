{{-- Runs before first paint to avoid a flash of the wrong theme. --}}
<script>
    (function () {
        var saved = localStorage.getItem('pm-theme');
        if (saved === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    })();
</script>
