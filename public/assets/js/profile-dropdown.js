document.addEventListener('DOMContentLoaded', function () {
    const profileWrappers =
        document.querySelectorAll('.syn-profile-wrapper');

    profileWrappers.forEach(function (wrapper) {
        const trigger =
            wrapper.querySelector('.syn-profile-trigger');

        const dropdown =
            wrapper.querySelector('.syn-profile-dropdown');

        if (!trigger || !dropdown) {
            return;
        }

        function openDropdown() {
            dropdown.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            wrapper.classList.add('is-open');
        }

        function closeDropdown() {
            dropdown.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            wrapper.classList.remove('is-open');
        }

        function toggleDropdown() {
            if (dropdown.hidden) {
                openDropdown();
            } else {
                closeDropdown();
            }
        }

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            toggleDropdown();
        });

        dropdown.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeDropdown();
                trigger.focus();
            }
        });
    });
});