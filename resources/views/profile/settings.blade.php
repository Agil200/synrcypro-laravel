@extends('layouts.app')

@section('title', 'Pengaturan Akun — SYNRGYPRO')
@section('body-class', 'syn-settings-page')

@php
    $user = auth()->user();

    $account = [
        'name' => $user?->name ?? 'Pengguna SYNRGYPRO',
        'email' => $user?->email ?? '-',
    ];
@endphp

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    body.syn-settings-page {
        margin: 0;
        color: #1f2937;
        background: #f3f5f7;
        font-family: Arial, Helvetica, sans-serif;
    }

    body.syn-settings-page.settings-dark {
        color: #e5e7eb;
        background: #111827;
    }

    button,
    input,
    select {
        font: inherit;
    }

    button {
        cursor: pointer;
    }

    .settings-page {
        min-height: 100vh;
        padding: 28px 18px 48px;
    }

    .settings-container {
        width: min(980px, 100%);
        margin: 0 auto;
    }

    /* =====================================================
       HEADER
       ===================================================== */

    .settings-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
    }

    .settings-page-heading h1 {
        margin: 0;
        color: #111827;
        font-size: 25px;
        line-height: 1.2;
    }

    .settings-page-heading p {
        margin: 5px 0 0;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .settings-header-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .settings-header-link {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 15px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        color: #374151;
        background: #ffffff;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition:
            transform 0.18s ease,
            border-color 0.18s ease,
            background 0.18s ease;
    }

    .settings-header-link:hover {
        border-color: #9ca3af;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .settings-header-link.dashboard {
        border-color: #242424;
        color: #ffffff;
        background: #242424;
    }

    .settings-header-link.dashboard:hover {
        background: #111111;
    }

    /* =====================================================
       PROFIL RINGKAS
       ===================================================== */

    .settings-account-summary {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 16px;
        padding: 18px;
        border: 1px solid #dce2e8;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .settings-account-icon {
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        place-items: center;
        border-radius: 13px;
        color: #ffffff;
        background:
            linear-gradient(
                135deg,
                #252525 0%,
                #d95d20 100%
            );
        font-size: 21px;
    }

    .settings-account-information {
        min-width: 0;
        flex: 1;
    }

    .settings-account-information strong {
        display: block;
        overflow: hidden;
        color: #111827;
        font-size: 14px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .settings-account-information span {
        display: block;
        margin-top: 5px;
        overflow: hidden;
        color: #6b7280;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .settings-account-status {
        display: inline-flex;
        min-height: 29px;
        align-items: center;
        gap: 7px;
        padding: 0 11px;
        border-radius: 999px;
        color: #087443;
        background: #e4f8ee;
        font-size: 10px;
        font-weight: 900;
    }

    .settings-account-status::before {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #20b26b;
        content: "";
    }

    /* =====================================================
       KONTEN
       ===================================================== */

    .settings-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.35fr)
            minmax(260px, 0.65fr);
        gap: 16px;
    }

    .settings-column {
        display: grid;
        align-content: start;
        gap: 16px;
    }

    .settings-card {
        overflow: hidden;
        border: 1px solid #dce2e8;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .settings-card-header {
        padding: 15px 17px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .settings-card-header h2 {
        margin: 0;
        color: #111827;
        font-size: 14px;
    }

    .settings-card-header p {
        margin: 5px 0 0;
        color: #6b7280;
        font-size: 11px;
        line-height: 1.45;
    }

    .settings-card-body {
        padding: 4px 17px;
    }

    /* =====================================================
       BARIS PENGATURAN
       ===================================================== */

    .settings-row {
        display: flex;
        min-height: 72px;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 13px 0;
        border-bottom: 1px solid #edf0f3;
    }

    .settings-row:last-child {
        border-bottom: 0;
    }

    .settings-row-information {
        min-width: 0;
        flex: 1;
    }

    .settings-row-information strong {
        display: block;
        color: #111827;
        font-size: 12px;
    }

    .settings-row-information small {
        display: block;
        margin-top: 5px;
        color: #6b7280;
        font-size: 10px;
        line-height: 1.45;
    }

    /* =====================================================
       TOGGLE
       ===================================================== */

    .settings-switch {
        position: relative;
        display: inline-flex;
        width: 46px;
        height: 26px;
        flex: 0 0 46px;
    }

    .settings-switch input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .settings-switch-slider {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #d1d5db;
        transition: background 0.2s ease;
    }

    .settings-switch-slider::before {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 5px rgba(15, 23, 42, 0.2);
        content: "";
        transition: transform 0.2s ease;
    }

    .settings-switch input:checked + .settings-switch-slider {
        background: #1478e8;
    }

    .settings-switch input:checked + .settings-switch-slider::before {
        transform: translateX(20px);
    }

    .settings-switch input:focus-visible + .settings-switch-slider {
        outline: 3px solid rgba(20, 120, 232, 0.22);
    }

    /* =====================================================
       SELECT
       ===================================================== */

    .settings-select {
        width: 155px;
        height: 38px;
        padding: 0 34px 0 11px;
        border: 1px solid #ccd3da;
        border-radius: 9px;
        outline: none;
        color: #374151;
        background: #ffffff;
        font-size: 11px;
        font-weight: 700;
    }

    .settings-select:focus {
        border-color: #1478e8;
        box-shadow: 0 0 0 3px rgba(20, 120, 232, 0.12);
    }

    /* =====================================================
       MENU KEAMANAN
       ===================================================== */

    .settings-action-list {
        display: grid;
        gap: 10px;
        padding: 14px;
    }

    .settings-action-link {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) 18px;
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

    .settings-action-link:hover {
        border-color: #b8c1cc;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .settings-action-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 10px;
        color: #ffffff;
        background: #1478e8;
        font-size: 17px;
    }

    .settings-action-link.profile .settings-action-icon {
        background: #30363d;
    }

    .settings-action-information strong {
        display: block;
        color: #111827;
        font-size: 12px;
    }

    .settings-action-information small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 10px;
        line-height: 1.35;
    }

    .settings-action-arrow {
        color: #9ca3af;
        font-size: 20px;
        font-weight: 700;
    }

    /* =====================================================
       BUTTON
       ===================================================== */

    .settings-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 16px;
    }

    .settings-button {
        min-height: 41px;
        padding: 0 17px;
        border: 0;
        border-radius: 9px;
        color: #ffffff;
        background: #1478e8;
        font-size: 12px;
        font-weight: 900;
    }

    .settings-button:hover {
        background: #0d67cb;
    }

    .settings-button.secondary {
        border: 1px solid #ccd3da;
        color: #374151;
        background: #ffffff;
    }

    .settings-button.secondary:hover {
        background: #f8fafc;
    }

    .settings-frontend-note {
        margin-top: 16px;
        padding: 13px 15px;
        border: 1px solid #f3d6a4;
        border-radius: 11px;
        color: #8a5708;
        background: #fff8e8;
        font-size: 11px;
        line-height: 1.55;
    }

    /* =====================================================
       TOAST
       ===================================================== */

    .settings-toast {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 5000;
        max-width: 340px;
        padding: 13px 16px;
        visibility: hidden;
        opacity: 0;
        border-radius: 10px;
        color: #ffffff;
        background: #1f2937;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.25);
        font-size: 12px;
        font-weight: 800;
        transform: translateY(15px);
        transition:
            opacity 0.2s ease,
            transform 0.2s ease,
            visibility 0.2s ease;
    }

    .settings-toast.is-visible {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    /* =====================================================
       DARK PREVIEW
       ===================================================== */

    body.syn-settings-page.settings-dark .settings-page-heading h1,
    body.syn-settings-page.settings-dark .settings-account-information strong,
    body.syn-settings-page.settings-dark .settings-card-header h2,
    body.syn-settings-page.settings-dark .settings-row-information strong,
    body.syn-settings-page.settings-dark .settings-action-information strong {
        color: #f9fafb;
    }

    body.syn-settings-page.settings-dark .settings-page-heading p,
    body.syn-settings-page.settings-dark .settings-account-information span,
    body.syn-settings-page.settings-dark .settings-card-header p,
    body.syn-settings-page.settings-dark .settings-row-information small,
    body.syn-settings-page.settings-dark .settings-action-information small {
        color: #9ca3af;
    }

    body.syn-settings-page.settings-dark .settings-account-summary,
    body.syn-settings-page.settings-dark .settings-card,
    body.syn-settings-page.settings-dark .settings-action-link,
    body.syn-settings-page.settings-dark .settings-select,
    body.syn-settings-page.settings-dark .settings-header-link {
        border-color: #374151;
        color: #e5e7eb;
        background: #1f2937;
    }

    body.syn-settings-page.settings-dark .settings-card-header {
        border-color: #374151;
        background: #18202d;
    }

    body.syn-settings-page.settings-dark .settings-row {
        border-color: #374151;
    }

    body.syn-settings-page.settings-dark .settings-action-link:hover,
    body.syn-settings-page.settings-dark .settings-header-link:hover {
        background: #273244;
    }

    /* =====================================================
       RESPONSIVE
       ===================================================== */

    @media (max-width: 760px) {
        .settings-page {
            padding: 18px 12px 35px;
        }

        .settings-page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .settings-header-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .settings-grid {
            grid-template-columns: 1fr;
        }

        .settings-account-summary {
            align-items: flex-start;
        }

        .settings-account-status {
            margin-left: auto;
        }

        .settings-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .settings-select {
            width: 100%;
        }

        .settings-form-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .settings-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-page">
    <div class="settings-container">

        {{-- Header halaman --}}
        <header class="settings-page-header">
            <div class="settings-page-heading">
                <h1>Pengaturan Akun</h1>

                <p>
                    Atur preferensi tampilan dan notifikasi akun SYNRGYPRO.
                </p>
            </div>

            <div class="settings-header-actions">
                <a
                    href="{{ route('profile.index') }}"
                    class="settings-header-link"
                >
                    <span aria-hidden="true">←</span>
                    <span>Profil Saya</span>
                </a>

                <a
                    href="{{ route('dashboard') }}"
                    class="settings-header-link dashboard"
                >
                    Dashboard
                </a>
            </div>
        </header>

        {{-- Identitas akun --}}
        <section class="settings-account-summary">
            <div class="settings-account-icon">
                👤
            </div>

            <div class="settings-account-information">
                <strong>{{ $account['name'] }}</strong>
                <span>{{ $account['email'] }}</span>
            </div>

            <span class="settings-account-status">
                Aktif
            </span>
        </section>

        <form id="accountSettingsForm">
            <div class="settings-grid">

                <div class="settings-column">

                    {{-- Notifikasi --}}
                    <section class="settings-card">
                        <div class="settings-card-header">
                            <h2>Notifikasi</h2>

                            <p>
                                Tentukan jenis informasi yang ingin ditampilkan.
                            </p>
                        </div>

                        <div class="settings-card-body">
                            <div class="settings-row">
                                <div class="settings-row-information">
                                    <strong>Notifikasi aplikasi</strong>

                                    <small>
                                        Tampilkan pemberitahuan aktivitas
                                        penting di dalam aplikasi.
                                    </small>
                                </div>

                                <label class="settings-switch">
                                    <input
                                        type="checkbox"
                                        id="applicationNotification"
                                        checked
                                    >

                                    <span class="settings-switch-slider"></span>
                                </label>
                            </div>

                            <div class="settings-row">
                                <div class="settings-row-information">
                                    <strong>Notifikasi email</strong>

                                    <small>
                                        Kirim pemberitahuan tertentu ke
                                        email akun.
                                    </small>
                                </div>

                                <label class="settings-switch">
                                    <input
                                        type="checkbox"
                                        id="emailNotification"
                                    >

                                    <span class="settings-switch-slider"></span>
                                </label>
                            </div>

                            <div class="settings-row">
                                <div class="settings-row-information">
                                    <strong>Pengingat pekerjaan</strong>

                                    <small>
                                        Aktifkan pengingat tugas dan agenda
                                        yang belum selesai.
                                    </small>
                                </div>

                                <label class="settings-switch">
                                    <input
                                        type="checkbox"
                                        id="taskReminder"
                                        checked
                                    >

                                    <span class="settings-switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </section>

                    {{-- Tampilan --}}
                    <section class="settings-card">
                        <div class="settings-card-header">
                            <h2>Tampilan</h2>

                            <p>
                                Pengaturan tampilan sementara untuk browser ini.
                            </p>
                        </div>

                        <div class="settings-card-body">
                            <div class="settings-row">
                                <div class="settings-row-information">
                                    <strong>Tema aplikasi</strong>

                                    <small>
                                        Pilih tampilan terang atau gelap.
                                    </small>
                                </div>

                                <select
                                    class="settings-select"
                                    id="themePreference"
                                >
                                    <option value="light">
                                        Terang
                                    </option>

                                    <option value="dark">
                                        Gelap
                                    </option>

                                    <option value="system">
                                        Ikuti sistem
                                    </option>
                                </select>
                            </div>

                            <div class="settings-row">
                                <div class="settings-row-information">
                                    <strong>Bahasa</strong>

                                    <small>
                                        Bahasa utama yang digunakan pada
                                        tampilan aplikasi.
                                    </small>
                                </div>

                                <select
                                    class="settings-select"
                                    id="languagePreference"
                                >
                                    <option value="id">
                                        Bahasa Indonesia
                                    </option>

                                    <option value="en">
                                        English
                                    </option>
                                </select>
                            </div>

                            <div class="settings-row">
                                <div class="settings-row-information">
                                    <strong>Tampilan dashboard ringkas</strong>

                                    <small>
                                        Kurangi jarak dan ukuran komponen
                                        pada dashboard.
                                    </small>
                                </div>

                                <label class="settings-switch">
                                    <input
                                        type="checkbox"
                                        id="compactDashboard"
                                    >

                                    <span class="settings-switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </section>

                </div>

                <aside class="settings-column">

                    {{-- Keamanan akun --}}
                    <section class="settings-card">
                        <div class="settings-card-header">
                            <h2>Informasi Akun</h2>

                            <p>
                                Akses cepat untuk mengelola profil.
                            </p>
                        </div>

                        <div class="settings-action-list">
                            <a
                                href="{{ route('profile.index') }}"
                                class="settings-action-link profile"
                            >
                                <span class="settings-action-icon">
                                    👤
                                </span>

                                <span class="settings-action-information">
                                    <strong>Profil Saya</strong>

                                    <small>
                                        Lihat identitas dan informasi akun.
                                    </small>
                                </span>

                                <span class="settings-action-arrow">
                                    ›
                                </span>
                            </a>

                            <a
                                href="{{ route('profile.change-email') }}"
                                class="settings-action-link"
                            >
                                <span class="settings-action-icon">
                                    ✉
                                </span>

                                <span class="settings-action-information">
                                    <strong>Ubah Email</strong>

                                    <small>
                                        Buka simulasi perubahan email.
                                    </small>
                                </span>

                                <span class="settings-action-arrow">
                                    ›
                                </span>
                            </a>
                        </div>
                    </section>

                    {{-- Penyimpanan --}}
                    <section class="settings-card">
                        <div class="settings-card-header">
                            <h2>Simpan Pengaturan</h2>

                            <p>
                                Simpan preferensi pada browser saat ini.
                            </p>
                        </div>

                        <div style="padding: 16px;">
                            <div class="settings-form-actions">
                                <button
                                    type="button"
                                    class="settings-button secondary"
                                    id="resetSettingsButton"
                                >
                                    Atur Ulang
                                </button>

                                <button
                                    type="submit"
                                    class="settings-button"
                                >
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </div>
                    </section>

                </aside>
            </div>
        </form>

        <div class="settings-frontend-note">
            Pengaturan ini masih merupakan simulasi front end. Data disimpan
            pada browser menggunakan localStorage dan belum tersimpan ke
            database atau akun pengguna.
        </div>

    </div>
</div>

<div
    class="settings-toast"
    id="settingsToast"
    role="status"
    aria-live="polite"
>
    Pengaturan berhasil disimpan pada browser.
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form =
        document.getElementById('accountSettingsForm');

    const applicationNotification =
        document.getElementById('applicationNotification');

    const emailNotification =
        document.getElementById('emailNotification');

    const taskReminder =
        document.getElementById('taskReminder');

    const themePreference =
        document.getElementById('themePreference');

    const languagePreference =
        document.getElementById('languagePreference');

    const compactDashboard =
        document.getElementById('compactDashboard');

    const resetButton =
        document.getElementById('resetSettingsButton');

    const toast =
        document.getElementById('settingsToast');

    const storageKey =
        'synrgyproAccountSettings';

    let toastTimer = null;

    function showToast(message) {
        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.classList.add('is-visible');

        window.clearTimeout(toastTimer);

        toastTimer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2600);
    }

function applyTheme(theme) {
    if (
        window.SynTheme &&
        typeof window.SynTheme.apply === 'function'
    ) {
        window.SynTheme.apply(theme);
        return;
    }

    const useDarkTheme =
        theme === 'dark' ||
        (
            theme === 'system' &&
            window.matchMedia(
                '(prefers-color-scheme: dark)'
            ).matches
        );

    document.documentElement.classList.toggle(
        'syn-theme-dark',
        useDarkTheme
    );

    document.body.classList.toggle(
        'settings-dark',
        useDarkTheme
    );
}

    function getCurrentSettings() {
        return {
            applicationNotification:
                applicationNotification.checked,

            emailNotification:
                emailNotification.checked,

            taskReminder:
                taskReminder.checked,

            theme:
                themePreference.value,

            language:
                languagePreference.value,

            compactDashboard:
                compactDashboard.checked,
        };
    }

    function applySettings(settings) {
        applicationNotification.checked =
            settings.applicationNotification ?? true;

        emailNotification.checked =
            settings.emailNotification ?? false;

        taskReminder.checked =
            settings.taskReminder ?? true;

        themePreference.value =
            settings.theme ?? 'light';

        languagePreference.value =
            settings.language ?? 'id';

        compactDashboard.checked =
            settings.compactDashboard ?? false;

        applyTheme(themePreference.value);
    }

    function loadSettings() {
        const storedSettings =
            localStorage.getItem(storageKey);

        if (!storedSettings) {
            applyTheme(themePreference.value);
            return;
        }

        try {
            applySettings(JSON.parse(storedSettings));
        } catch (error) {
            localStorage.removeItem(storageKey);
        }
    }

    themePreference.addEventListener(
        'change',
        function () {
            applyTheme(themePreference.value);
        }
    );

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        localStorage.setItem(
            storageKey,
            JSON.stringify(getCurrentSettings())
        );

        showToast(
            'Pengaturan berhasil disimpan pada browser.'
        );
    });

    resetButton.addEventListener('click', function () {
        localStorage.removeItem(storageKey);

        applySettings({
            applicationNotification: true,
            emailNotification: false,
            taskReminder: true,
            theme: 'light',
            language: 'id',
            compactDashboard: false,
        });

        showToast(
            'Pengaturan berhasil dikembalikan ke kondisi awal.'
        );
    });

    loadSettings();
});
</script>
@endpush