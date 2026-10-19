(function () {
    'use strict';
    const KEY = 'theme';
    const root = document.documentElement;
    function initialTheme() {
        const saved = localStorage.getItem(KEY);
        if (saved === 'dark' || saved === 'light') return saved;
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
function syncButtons(theme) {
        const dark = theme === 'dark';
        const main = document.getElementById('themeToggle');
        const mobile = document.getElementById('mobileTheme');
        if (main) {
            const icon = main.querySelector('i');
            if (icon) icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
            main.title = dark ? 'الوضع النهاري' : 'الوضع الليلي';
            main.setAttribute('aria-label', main.title);
        }
        if (mobile) {
            const icon = mobile.querySelector('i');
            if (icon) icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
            mobile.lastChild.textContent = dark ? ' الوضع النهاري' : ' الوضع الليلي';
        }
    }
function apply(theme) {
        theme = theme === 'dark' ? 'dark' : 'light';
        root.dataset.theme = theme;
        root.style.colorScheme = theme;
        if (document.body) {
            document.body.classList.toggle('dark-mode', theme === 'dark');
            document.body.classList.toggle('light-mode', theme === 'light');
        }
        syncButtons(theme);
    }
    apply(initialTheme());
document.addEventListener('DOMContentLoaded', function () {
        apply(root.dataset.theme || initialTheme());
        const toggle = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem(KEY, next);
            apply(next);
        };
        const main = document.getElementById('themeToggle');
        const mobile = document.getElementById('mobileTheme');
        if (main) main.addEventListener('click', toggle);
        if (mobile) mobile.addEventListener('click', toggle);
    });
})();
