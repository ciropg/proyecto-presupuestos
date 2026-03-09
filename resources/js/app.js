import './bootstrap';

import Alpine from 'alpinejs';

const themeKey = 'theme';
const darkTheme = 'dark';
const lightTheme = 'light';
const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const getStoredTheme = () => {
    const theme = window.localStorage.getItem(themeKey);

    return theme === darkTheme || theme === lightTheme ? theme : null;
};

const getSystemTheme = () => (mediaQuery.matches ? darkTheme : lightTheme);

const applyTheme = (theme) => {
    document.documentElement.classList.toggle('dark', theme === darkTheme);
    document.documentElement.style.colorScheme = theme;
};

applyTheme(getStoredTheme() ?? getSystemTheme());

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        isDark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.set(this.isDark ? lightTheme : darkTheme);
        },
        set(theme) {
            this.isDark = theme === darkTheme;
            window.localStorage.setItem(themeKey, theme);
            applyTheme(theme);
        },
    });
});

mediaQuery.addEventListener('change', (event) => {
    if (getStoredTheme() !== null) {
        return;
    }

    const theme = event.matches ? darkTheme : lightTheme;

    applyTheme(theme);

    if (window.Alpine?.store('theme')) {
        window.Alpine.store('theme').isDark = theme === darkTheme;
    }
});

Alpine.start();
