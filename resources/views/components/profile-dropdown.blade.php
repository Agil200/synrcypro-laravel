@php
    $user = auth()->user();

    $profilePhoto = $user?->avatar
        ? $user->avatar
        : asset('assets/images/profile.png');
@endphp

<div
    class="syn-profile-wrapper"
    id="profileWrapper"
>
    {{-- Tombol foto profil pada navbar --}}
    <button
        type="button"
        class="syn-profile-trigger"
        id="profileTrigger"
        aria-label="Buka menu profil"
        aria-expanded="false"
        aria-controls="profileDropdown"
    >
        <img
            src="{{ $profilePhoto }}"
            alt="Foto profil {{ $user?->name ?? 'Pengguna' }}"
            referrerpolicy="no-referrer"
        >
    </button>

    {{-- Isi dropdown profil --}}
    <div
        class="syn-profile-dropdown"
        id="profileDropdown"
        hidden
    >
        <div class="syn-profile-header">
            <img
                src="{{ $profilePhoto }}"
                alt="Foto profil {{ $user?->name ?? 'Pengguna' }}"
                class="syn-profile-photo"
                referrerpolicy="no-referrer"
            >

            <div class="syn-profile-information">
                <strong>
                    {{ $user?->name ?? 'Calvin Anggoro' }}
                </strong>

                <span>
                    NRP: {{ $user?->nrp ?? '10001' }}
                </span>

                <span>
                    {{
                        $user?->jabatan
                        ?? $user?->role
                        ?? 'Supervisor Produksi'
                    }}
                </span>
            </div>
        </div>

        <div class="syn-profile-line"></div>

        <div class="syn-profile-menu">
            <a
                href="{{ route('profile.index') }}"
                class="syn-profile-item"
            >
                <span aria-hidden="true">👤</span>
                <span>Profil Saya</span>
            </a>

            <a
                href="{{ route('profile.settings') }}"
                class="syn-profile-item"
            >
                <span aria-hidden="true">⚙️</span>
                <span>Pengaturan Akun</span>
            </a>

            <a
                href="{{ route('profile.change-email') }}"
                class="syn-profile-item"
            >
                <span aria-hidden="true">✉️</span>
                <span>Ubah Email</span>
            </a>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="syn-profile-logout-form"
            >
                @csrf

                <button
                    type="submit"
                    class="syn-profile-item syn-profile-signout"
                >
                    <span aria-hidden="true">🚪</span>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>