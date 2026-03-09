<script>
    (() => {
        const key = 'theme';
        const storedTheme = window.localStorage.getItem(key);
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const theme = storedTheme === 'dark' || storedTheme === 'light' ? storedTheme : systemTheme;

        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.style.colorScheme = theme;
    })();
</script>
