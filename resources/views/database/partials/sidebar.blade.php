@php
    $databaseOpen = in_array(
        $activePage ?? '',
        ['dashboard', 'employees'],
        true
    );

    $atrOpen = in_array(
        $activePage ?? '',
        [
            'atr-summary',
            'atr-upload',
            'atr-history',
            'atr-calls',
            'atr-pic-roster',
        ],
        true
    );
@endphp

<aside class="db-sidebar">
    <div class="db-sidebar-head">
        <div class="db-sidebar-logo">
            <img
                src="{{ asset('assets/images/DATABASE.png') }}"
                alt="Database"
            >
        </div>

        <button
            type="button"
            class="db-sidebar-toggle"
            id="databaseSidebarToggle"
            aria-label="Buka atau tutup sidebar"
        >
            ☰
        </button>
    </div>

    <nav class="db-navigation" aria-label="Menu Database">
        <a
            href="{{ route('database.dashboard') }}"
            class="db-menu-link
                {{ ($activePage ?? '') === 'dashboard'
                    ? 'active'
                    : '' }}"
        >
            <span class="db-menu-icon">▦</span>
            <span class="db-menu-label">Dashboard</span>
        </a>

        <div
            class="db-menu-group
                {{ $databaseOpen ? 'is-open' : '' }}"
        >
            <button
                type="button"
                class="db-menu-toggle"
                aria-expanded="{{ $databaseOpen ? 'true' : 'false' }}"
            >
                <span class="db-menu-icon">
                    <img
                        src="{{ asset(
                            'assets/images/database-submenu.png'
                        ) }}"
                        alt=""
                    >
                </span>

                <span class="db-menu-label">Database Karyawan</span>
                <span class="db-menu-arrow">›</span>
            </button>

            <div class="db-submenu">
                <div class="db-submenu-inner">
                    <a
                        href="{{ route('database.employees') }}"
                        class="db-submenu-link
                            {{ ($activePage ?? '') === 'employees'
                                ? 'active'
                                : '' }}"
                    >
                        Ringkasan &amp; Pencarian
                    </a>
                </div>
            </div>
        </div>

        <div
            class="db-menu-group
                {{ $atrOpen ? 'is-open' : '' }}"
        >
            <button
                type="button"
                class="db-menu-toggle"
                aria-expanded="{{ $atrOpen ? 'true' : 'false' }}"
            >
                <span class="db-menu-icon">
                    <img
                        src="{{ asset(
                            'assets/images/ATR-submenu.png'
                        ) }}"
                        alt=""
                    >
                </span>

                <span class="db-menu-label">ATR Karyawan</span>
                <span class="db-menu-arrow">›</span>
            </button>

            <div class="db-submenu">
                <div class="db-submenu-inner">
                    <a
                        href="{{ route('database.atr.summary') }}"
                        class="db-submenu-link
                            {{ ($activePage ?? '') === 'atr-summary'
                                ? 'active'
                                : '' }}"
                    >

                        <span class="db-submenu-icon" aria-hidden="true">

                                                    <svg viewBox="0 0 24 24" fill="none">

                                                        <path d="M4 19V9" />

                                                        <path d="M10 19V5" />

                                                        <path d="M16 19v-7" />

                                                        <path d="M22 19H2" />

                                                    </svg>

                                                </span>

                                                <span class="db-submenu-text">

                                                    Ringkasan ATR

                                                </span>

                        </a>

                    <a
                        href="{{ route('database.atr.upload') }}"
                        class="db-submenu-link
                            {{ ($activePage ?? '') === 'atr-upload'
                                ? 'active'
                                : '' }}"
                    >

                        <span class="db-submenu-icon" aria-hidden="true">

                                                    <svg viewBox="0 0 24 24" fill="none">

                                                        <path d="M12 16V4" />

                                                        <path d="m7 9 5-5 5 5" />

                                                        <path d="M5 14v5h14v-5" />

                                                    </svg>

                                                </span>

                                                <span class="db-submenu-text">

                                                    Upload Data ATR

                                                </span>

                        </a>

                    <a
                        href="{{ route('database.atr.history') }}"
                        class="db-submenu-link
                            {{ ($activePage ?? '') === 'atr-history'
                                ? 'active'
                                : '' }}"
                    >

                        <span class="db-submenu-icon" aria-hidden="true">

                                                    <svg viewBox="0 0 24 24" fill="none">

                                                        <path d="M3 12a9 9 0 1 0 3-6.7" />

                                                        <path d="M3 4v5h5" />

                                                        <path d="M12 7v5l3 2" />

                                                    </svg>

                                                </span>

                                                <span class="db-submenu-text">

                                                    Riwayat Import

                                                </span>

                        </a>

                    <a
                        href="{{ route('database.atr.calls') }}"
                        class="db-submenu-link
                            {{ ($activePage ?? '') === 'atr-calls'
                                ? 'active'
                                : '' }}"
                    >

                        <span class="db-submenu-icon" aria-hidden="true">

                                                    <svg viewBox="0 0 24 24" fill="none">

                                                        <path d="M7 3h7l4 4v14H7z" />

                                                        <path d="M14 3v5h5" />

                                                        <path d="M9.5 13.5c1.3 2.4 2.6 3.7 5 5" />

                                                        <path d="M10.5 12.5 9 14c-.7-.5-1.2-1.1-1.5-1.8l1.4-1.4" />

                                                        <path d="m15.1 17.1-1.4 1.4c.7.3 1.3.8 1.8 1.5l1.5-1.5" />

                                                    </svg>

                                                </span>

                                                <span class="db-submenu-text">

                                                    Dokumentasi Pemanggilan

                                                </span>

                        </a>
                    <a
                        href="{{ route('database.atr.pic-roster') }}"
                        class="db-submenu-link
                            {{ ($activePage ?? '') === 'atr-pic-roster'
                                ? 'active'
                                : '' }}"
                    >
                        <span class="db-submenu-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="8" cy="8" r="3" />
                                <circle cx="17" cy="9" r="2.5" />
                                <path d="M2.5 20c.4-4 2.3-6 5.5-6s5.1 2 5.5 6" />
                                <path d="M13.5 16c.8-1.4 2-2 3.7-2 2.5 0 4 1.7 4.3 5" />
                                <path d="M18.7 3.5v3" />
                                <path d="M17.2 5h3" />
                            </svg>
                        </span>

                        <span class="db-submenu-text">
                            Pengaturan PIC Roster
                        </span>
                    </a>

                </div>
            </div>
        </div>
    </nav>

    <div class="db-sidebar-bottom">
        <a href="#" class="db-bottom-link">
            <span>⚙</span>
            <span>Pengaturan</span>
        </a>

        <a
            href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRGYPRO%20Support"
            target="_blank"
            rel="noopener noreferrer"
            class="db-bottom-link help"
        >
            <span>?</span>
            <span>Bantuan</span>
        </a>
    </div>
</aside>