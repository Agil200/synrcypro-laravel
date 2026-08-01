<header class="db-header">
    <div class="db-header-brand">
        <img
            src="{{ asset('assets/images/synrgypro-logo.png') }}"
            alt="SYNRGYPRO"
        >
    </div>

    <nav
        class="db-header-actions"
        aria-label="Shortcut pengguna"
    >
        {{-- Shortcut lintas modul --}}
        <x-module-shortcut />

        {{-- Dashboard --}}
        <a
            href="{{ route('dashboard') }}"
            class="db-header-button"
            title="Dashboard"
            aria-label="Dashboard"
        >
            <img
                class="db-home-icon"
                src="{{ asset('assets/images/LOGO HOME.jpeg') }}"
                alt=""
            >
        </a>

        {{-- Dropdown profil yang sama dengan menu Manpower --}}
        <x-profile-dropdown />

        {{-- Logout --}}
        <form
            method="POST"
            action="{{ route('logout') }}"
            class="db-logout-form"
        >
            @csrf

            <button
                type="submit"
                class="db-header-button db-logout-button"
                title="Logout"
                aria-label="Logout"
            >
                <img
                    src="{{ asset('assets/images/LOGO LOGOUT.png') }}"
                    alt=""
                >
            </button>
        </form>
    </nav>
</header>