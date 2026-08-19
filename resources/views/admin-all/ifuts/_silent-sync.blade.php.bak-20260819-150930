<script>
(function () {
    'use strict';

    const SUCCESS_DELAY = 5 * 60 * 1000; // 5 menit
    const RETRY_DELAY = 10 * 1000;       // retry saat cold-start/error
    let syncing = false;
    let timer = null;

    function currentPage() {
        return document.querySelector('.if-page');
    }

    function currentHasError() {
        return !!document.querySelector('.if-page .if-status-strip.error');
    }

    function userIsInteracting() {
        const active = document.activeElement;

        if (!active) return false;

        return ['SELECT', 'INPUT', 'TEXTAREA', 'BUTTON'].includes(active.tagName);
    }

    function schedule(delay) {
        window.clearTimeout(timer);
        timer = window.setTimeout(sync, delay);
    }

    async function sync() {
        if (syncing) {
            schedule(currentHasError() ? RETRY_DELAY : SUCCESS_DELAY);
            return;
        }

        if (document.hidden || userIsInteracting()) {
            schedule(currentHasError() ? RETRY_DELAY : 30000);
            return;
        }

        syncing = true;

        try {
            const response = await fetch(window.location.href, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const nextPage = parsed.querySelector('.if-page');

            if (!nextPage) {
                throw new Error('IFUTS snapshot tidak ditemukan.');
            }

            const nextHasError = !!nextPage.querySelector('.if-status-strip.error');
            const page = currentPage();

            /*
             * Kalau halaman sekarang sudah punya data valid tetapi request baru
             * sedang gagal, pertahankan data lama agar user tidak melihat 0/error.
             */
            if (page && !(nextHasError && !currentHasError())) {
                page.innerHTML = nextPage.innerHTML;
            }

            schedule(nextHasError ? RETRY_DELAY : SUCCESS_DELAY);
        } catch (error) {
            /* silent: data terakhir di layar tetap dipertahankan */
            schedule(currentHasError() ? RETRY_DELAY : SUCCESS_DELAY);
        } finally {
            syncing = false;
        }
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            schedule(currentHasError() ? 1000 : 15000);
        }
    });

    schedule(currentHasError() ? RETRY_DELAY : SUCCESS_DELAY);
})();
</script>
