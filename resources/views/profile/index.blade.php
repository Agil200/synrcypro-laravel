@extends('layouts.app')

@section('title', 'Profil Saya — SYNRGYPRO')
@section('body-class', 'syn-profile-page')

@php
    $user = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | DATA SEMENTARA FRONT END
    |--------------------------------------------------------------------------
    | Nilai di bawah menggunakan data pengguna yang sedang login.
    | Jika kolom belum tersedia, Laravel akan menampilkan data fallback.
    | Nanti pada tahap backend data ini dihubungkan ke database karyawan.
    */

    $profile = [
        'name' => $user?->name ?? 'Calvin Anggoro',
        'nrp' => $user?->nrp ?? '10001',
        'email' => $user?->email ?? 'calvin@example.com',
        'jabatan' => $user?->jabatan ?? 'Operator',
        'departemen' => $user?->departemen ?? 'Produksi',
        'role' => $user?->role ?? 'User',
        'status' => $user?->is_active === false ? 'Nonaktif' : 'Aktif',
    ];

    $profilePhoto = $user?->avatar
        ? $user->avatar
        : asset('assets/images/profile.png');
@endphp

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    body.syn-profile-page {
        margin: 0;
        color: #1f2937;
        background: #f3f5f7;
        font-family: Arial, Helvetica, sans-serif;
    }

    .profile-page {
        min-height: 100vh;
        padding: 28px 18px 48px;
    }

    .profile-container {
        width: min(980px, 100%);
        margin: 0 auto;
    }

    /* =====================================================
       HEADER HALAMAN
       ===================================================== */

    .profile-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
    }

    .profile-page-heading h1 {
        margin: 0;
        color: #111827;
        font-size: 25px;
        line-height: 1.2;
    }

    .profile-page-heading p {
        margin: 5px 0 0;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .profile-dashboard-link {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 16px;
        border-radius: 10px;
        color: #ffffff;
        background: #242424;
        box-shadow: 0 7px 18px rgba(15, 23, 42, 0.12);
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition:
            transform 0.18s ease,
            background 0.18s ease;
    }

    .profile-dashboard-link:hover {
        background: #111111;
        transform: translateY(-2px);
    }

    /* =====================================================
       HERO PROFIL
       ===================================================== */

    .profile-hero {
        position: relative;
        display: grid;
        grid-template-columns: 112px minmax(0, 1fr) auto;
        gap: 22px;
        align-items: center;
        padding: 28px;
        overflow: hidden;
        border-radius: 18px 18px 0 0;
        color: #ffffff;
        background:
            linear-gradient(
                120deg,
                #121212 0%,
                #272727 52%,
                #693120 78%,
                #d95d20 100%
            );
    }

    .profile-hero::after {
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(
                circle at 85% 20%,
                rgba(255, 255, 255, 0.16),
                transparent 32%
            );
        content: "";
    }

    .profile-photo-wrapper,
    .profile-identity,
    .profile-status-wrapper {
        position: relative;
        z-index: 1;
    }

    .profile-photo-wrapper {
        width: 108px;
        height: 108px;
        padding: 4px;
        overflow: hidden;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.22);
    }

    .profile-photo-wrapper img {
        display: block;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-identity h2 {
        margin: 0;
        font-size: 24px;
        line-height: 1.2;
    }

    .profile-identity-position {
        display: block;
        margin-top: 7px;
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
        font-weight: 700;
    }

    .profile-identity-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 13px;
    }

    .profile-meta-badge {
        display: inline-flex;
        min-height: 28px;
        align-items: center;
        padding: 0 11px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        color: #ffffff;
        background: rgba(255, 255, 255, 0.1);
        font-size: 10px;
        font-weight: 800;
    }

    .profile-status-wrapper {
        text-align: right;
    }

    .profile-status-label {
        display: block;
        margin-bottom: 7px;
        color: rgba(255, 255, 255, 0.72);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .profile-status {
        display: inline-flex;
        min-height: 32px;
        align-items: center;
        gap: 7px;
        padding: 0 13px;
        border-radius: 999px;
        color: #087443;
        background: #e4f8ee;
        font-size: 11px;
        font-weight: 900;
    }

    .profile-status::before {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #20b26b;
        content: "";
    }

    /* =====================================================
       KONTEN UTAMA
       ===================================================== */

    .profile-content {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(250px, 0.65fr);
        gap: 16px;
        padding: 18px;
        border: 1px solid #dce2e8;
        border-top: 0;
        border-radius: 0 0 18px 18px;
        background: #ffffff;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.08);
    }

    .profile-panel {
        overflow: hidden;
        border: 1px solid #e1e6eb;
        border-radius: 13px;
        background: #ffffff;
    }

    .profile-panel-header {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .profile-panel-header h3 {
        margin: 0;
        color: #111827;
        font-size: 14px;
    }

    .profile-panel-header p {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 11px;
        line-height: 1.45;
    }

    .profile-detail-list {
        display: grid;
        gap: 0;
        padding: 5px 16px;
    }

    .profile-detail-row {
        display: grid;
        grid-template-columns: 155px minmax(0, 1fr);
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid #edf0f3;
    }

    .profile-detail-row:last-child {
        border-bottom: 0;
    }

    .profile-detail-label {
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
    }

    .profile-detail-value {
        color: #111827;
        font-size: 12px;
        font-weight: 900;
        text-align: right;
        overflow-wrap: anywhere;
    }

    /* =====================================================
       MENU CEPAT
       ===================================================== */

    .profile-action-list {
        display: grid;
        gap: 10px;
        padding: 14px;
    }

    .profile-action-link {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) 20px;
        gap: 10px;
        align-items: center;
        min-height: 62px;
        padding: 10px;
        border: 1px solid #e1e6eb;
        border-radius: 10px;
        color: #1f2937;
        background: #ffffff;
        text-decoration: none;
        transition:
            border-color 0.18s ease,
            background 0.18s ease,
            transform 0.18s ease;
    }

    .profile-action-link:hover {
        border-color: #b8c1cc;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .profile-action-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 10px;
        color: #ffffff;
        background: #30363d;
        font-size: 17px;
    }

    .profile-action-link:nth-child(2) .profile-action-icon {
        background: #1478e8;
    }

    .profile-action-information strong {
        display: block;
        color: #111827;
        font-size: 12px;
    }

    .profile-action-information small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 10px;
        line-height: 1.35;
    }

    .profile-action-arrow {
        color: #9ca3af;
        font-size: 20px;
        font-weight: 700;
        text-align: center;
    }

    .profile-frontend-note {
        margin-top: 16px;
        padding: 13px 15px;
        border: 1px solid #cfe3ff;
        border-radius: 11px;
        color: #22558c;
        background: #eef6ff;
        font-size: 11px;
        line-height: 1.55;
    }

    /* =====================================================
       RESPONSIVE
       ===================================================== */

    @media (max-width: 760px) {
        .profile-page {
            padding: 18px 12px 35px;
        }

        .profile-page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .profile-hero {
            grid-template-columns: 1fr;
            justify-items: center;
            text-align: center;
        }

        .profile-identity-meta {
            justify-content: center;
        }

        .profile-status-wrapper {
            text-align: center;
        }

        .profile-content {
            grid-template-columns: 1fr;
        }

        .profile-detail-row {
            grid-template-columns: 1fr;
            gap: 5px;
        }

        .profile-detail-value {
            text-align: left;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-page">
    <div class="profile-container">

        {{-- Header halaman --}}
        <header class="profile-page-header">
            <div class="profile-page-heading">
                <h1>Profil Saya</h1>

                <p>
                    Informasi akun dan identitas pengguna SYNRGYPRO.
                </p>
            </div>

            <a
                href="{{ route('dashboard') }}"
                class="profile-dashboard-link"
            >
                <span aria-hidden="true">←</span>
                <span>Kembali ke Dashboard</span>
            </a>
        </header>

        {{-- Informasi utama profil --}}
        <section class="profile-hero">
            <div class="profile-photo-wrapper">
                <img
                    src="{{ $profilePhoto }}"
                    alt="Foto profil {{ $profile['name'] }}"
                    referrerpolicy="no-referrer"
                >
            </div>

            <div class="profile-identity">
                <h2>{{ $profile['name'] }}</h2>

                <span class="profile-identity-position">
                    {{ $profile['jabatan'] }}
                </span>

                <div class="profile-identity-meta">
                    <span class="profile-meta-badge">
                        NRP {{ $profile['nrp'] }}
                    </span>

                    <span class="profile-meta-badge">
                        {{ $profile['departemen'] }}
                    </span>

                    <span class="profile-meta-badge">
                        {{ $profile['role'] }}
                    </span>
                </div>
            </div>

            <div class="profile-status-wrapper">
                <span class="profile-status-label">
                    Status akun
                </span>

                <span class="profile-status">
                    {{ $profile['status'] }}
                </span>
            </div>
        </section>

        <div class="profile-content">

            {{-- Detail pengguna --}}
            <section class="profile-panel">
                <div class="profile-panel-header">
                    <h3>Informasi Pengguna</h3>

                    <p>
                        Data identitas yang ditampilkan pada aplikasi.
                    </p>
                </div>

                <div class="profile-detail-list">
                    <div class="profile-detail-row">
                        <span class="profile-detail-label">
                            Nama lengkap
                        </span>

                        <strong class="profile-detail-value">
                            {{ $profile['name'] }}
                        </strong>
                    </div>

                    <div class="profile-detail-row">
                        <span class="profile-detail-label">
                            NRP
                        </span>

                        <strong class="profile-detail-value">
                            {{ $profile['nrp'] }}
                        </strong>
                    </div>

                    <div class="profile-detail-row">
                        <span class="profile-detail-label">
                            Email login
                        </span>

                        <strong class="profile-detail-value">
                            {{ $profile['email'] }}
                        </strong>
                    </div>

                    <div class="profile-detail-row">
                        <span class="profile-detail-label">
                            Jabatan
                        </span>

                        <strong class="profile-detail-value">
                            {{ $profile['jabatan'] }}
                        </strong>
                    </div>

                    <div class="profile-detail-row">
                        <span class="profile-detail-label">
                            Departemen
                        </span>

                        <strong class="profile-detail-value">
                            {{ $profile['departemen'] }}
                        </strong>
                    </div>

                    <div class="profile-detail-row">
                        <span class="profile-detail-label">
                            Hak akses
                        </span>

                        <strong class="profile-detail-value">
                            {{ $profile['role'] }}
                        </strong>
                    </div>
                </div>
            </section>

            {{-- Menu cepat --}}
            <aside class="profile-panel">
                <div class="profile-panel-header">
                    <h3>Pengaturan Profil</h3>

                    <p>
                        Kelola preferensi dan informasi akun.
                    </p>
                </div>

                <div class="profile-action-list">
                    <a
                        href="{{ route('profile.settings') }}"
                        class="profile-action-link"
                    >
                        <span class="profile-action-icon">
                            ⚙
                        </span>

                        <span class="profile-action-information">
                            <strong>Pengaturan Akun</strong>

                            <small>
                                Atur preferensi tampilan dan notifikasi.
                            </small>
                        </span>

                        <span class="profile-action-arrow">
                            ›
                        </span>
                    </a>

                    <a
                        href="{{ route('profile.change-email') }}"
                        class="profile-action-link"
                    >
                        <span class="profile-action-icon">
                            ✉
                        </span>

                        <span class="profile-action-information">
                            <strong>Ubah Email</strong>

                            <small>
                                Buka simulasi pengajuan perubahan email.
                            </small>
                        </span>

                        <span class="profile-action-arrow">
                            ›
                        </span>
                    </a>
                </div>
            </aside>

        </div>

        <div class="profile-frontend-note">
            Tampilan ini masih menggunakan data akun login dan data contoh.
            NRP, jabatan, departemen, serta hak akses akan dihubungkan ke
            database karyawan pada tahap backend.
        </div>

    </div>
</div>
@endsection