<header class="manpower-header">

    <div class="manpower-header-brand">

        <img
            src="{{ asset('assets/images/synrgypro-logo.png') }}"
            alt="SYNRGYPRO"
        >

    </div>



    <nav
        class="manpower-header-actions"
        aria-label="Shortcut pengguna"
    >


        {{-- Home --}}

        <a
            href="{{ route('dashboard') }}"
            class="manpower-header-button"
            title="Dashboard"
        >

            <img
                src="{{ asset('assets/images/LOGO HOME.jpeg') }}"
                alt="Dashboard"
            >

        </a>



        {{-- Profile --}}

        <x-profile-dropdown />



        {{-- Logout --}}

        <form
            method="POST"
            action="{{ route('logout') }}"
        >

            @csrf


            <button
                type="submit"
                class="manpower-header-button"
            >

                <img
                    src="{{ asset('assets/images/LOGO LOGOUT.png') }}"
                    alt="Logout"
                >

            </button>


        </form>


    </nav>


</header>