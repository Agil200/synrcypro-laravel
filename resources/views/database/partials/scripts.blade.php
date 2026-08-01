<script>
document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('databasePage');
    const sidebarToggle =
        document.getElementById('databaseSidebarToggle');
    const groups =
        Array.from(document.querySelectorAll('.db-menu-group'));

    if (sidebarToggle && page) {
        sidebarToggle.addEventListener('click', function () {
            page.classList.toggle('sidebar-collapsed');
        });
    }

    groups.forEach(function (group) {
        const toggle = group.querySelector('.db-menu-toggle');

        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            const willOpen =
                !group.classList.contains('is-open');

            groups.forEach(function (otherGroup) {
                otherGroup.classList.remove('is-open');

                const otherToggle =
                    otherGroup.querySelector('.db-menu-toggle');

                if (otherToggle) {
                    otherToggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }
            });

            if (willOpen) {
                group.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.querySelectorAll('a.db-menu-link, a.db-submenu-link')
        .forEach(function (link) {
            link.addEventListener('click', function () {
                const loading =
                    document.getElementById(
                        'databaseLoadingLayer'
                    );

                if (loading) {
                    loading.classList.add('is-visible');
                    loading.setAttribute(
                        'aria-hidden',
                        'false'
                    );
                }
            });
        });

    const fileInput = document.getElementById('atrRawFile');
    const fileName = document.getElementById('atrRawFileName');

    if (fileInput && fileName) {
        fileInput.addEventListener('change', function () {
            fileName.textContent =
                fileInput.files[0]?.name
                ?? 'Belum ada file dipilih';
        });
    }
});
</script>