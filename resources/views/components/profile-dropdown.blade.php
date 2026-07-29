@php
    $user = auth()->user();
@endphp

<div class="syn-profile-wrapper" id="profileWrapper">

    {{-- Tombol foto di navbar --}}
    <button
        type="button"
        class="syn-profile-trigger"
        id="profileTrigger"
        aria-label="Buka profil"
    >
        <img
            src="{{ asset('assets/profile.png') }}"
            alt="Foto Profil"
        >
    </button>

    {{-- Isi dropdown --}}
    <div class="syn-profile-dropdown" id="profileDropdown">

        <div class="syn-profile-header">
            <img
                src="{{ asset('assets/profile.png') }}"
                alt="Foto Profil"
                class="syn-profile-photo"
            >

            <div class="syn-profile-information">
                <strong>
                    {{ $user->name ?? 'Calvin Anggoro' }}
                </strong>

                <span>
                    NRP: {{ $user->nrp ?? '10001' }}
                </span>

                <span>
                    {{ $user->jabatan ?? 'Supervisor Produksi' }}
                </span>
            </div>
        </div>

        <div class="syn-profile-line"></div>

        <div class="syn-profile-menu">
            <a href="{{ url('/profil') }}" class="syn-profile-item">
                <span>👤</span>
                <span>Profil Saya</span>
            </a>

            <a href="{{ url('/pengaturan') }}" class="syn-profile-item">
                <span>⚙️</span>
                <span>Pengaturan Akun</span>
            </a>

            <a href="{{ url('/ubah-email') }}" class="syn-profile-item">
                <span>🔒</span>
                <span>Ubah Email</span>
            </a>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="syn-profile-logout-form"
            >
                @csrf

                <button type="submit" class="syn-profile-item syn-profile-signout">
                    <span>🚪</span>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>