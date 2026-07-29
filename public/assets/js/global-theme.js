(function () {
    const storageKey = 'synrgyproAccountSettings';

    const systemDarkMode = window.matchMedia(
        '(prefers-color-scheme: dark)'
    );

    function readThemePreference() {
        const storedSettings =
            localStorage.getItem(storageKey);

        if (!storedSettings) {
            return 'light';
        }

        try {
            const settings = JSON.parse(storedSettings);

            return settings.theme ?? 'light';
        } catch (error) {
            localStorage.removeItem(storageKey);

            return 'light';
        }
    }

    function shouldUseDarkTheme(theme) {
        if (theme === 'dark') {
            return true;
        }

        if (theme === 'system') {
            return systemDarkMode.matches;
        }

        return false;
    }

    function applyTheme(theme) {
        const selectedTheme =
            theme ?? readThemePreference();

        const useDarkTheme =
            shouldUseDarkTheme(selectedTheme);

        document.documentElement.classList.toggle(
            'syn-theme-dark',
            useDarkTheme
        );

        document.documentElement.dataset.theme =
            useDarkTheme ? 'dark' : 'light';

        /*
         * Tetap mendukung CSS lama pada halaman
         * Pengaturan Akun.
         */
        if (document.body) {
            document.body.classList.toggle(
                'settings-dark',
                useDarkTheme
            );
        }
    }

    window.SynTheme = {
        apply: applyTheme,
        read: readThemePreference,
    };

    /*
     * Terapkan tema secepat mungkin.
     */
    applyTheme();

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            applyTheme();
        }
    );

    /*
     * Memperbarui halaman lain saat localStorage berubah.
     */
    window.addEventListener('storage', function (event) {
        if (event.key === storageKey) {
            applyTheme();
        }
    });

    /*
     * Memperbarui tema ketika pilihan "Ikuti sistem"
     * digunakan dan tema perangkat berubah.
     */
    systemDarkMode.addEventListener(
        'change',
        function () {
            if (readThemePreference() === 'system') {
                applyTheme('system');
            }
        }
    );
})();