@extends('layouts.app')

@section('title', 'Ubah Email — SYNRGYPRO')
@section('body-class', 'syn-change-email-page')

@php
    $user = auth()->user();

    $account = [
        'name' => $user?->name ?? 'Pengguna SYNRGYPRO',
        'current_email' => $user?->email ?? '-',
    ];
@endphp

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    body.syn-change-email-page {
        margin: 0;
        color: #1f2937;
        background: #f3f5f7;
        font-family: Arial, Helvetica, sans-serif;
    }

    button,
    input,
    textarea {
        font: inherit;
    }

    button {
        cursor: pointer;
    }

    .email-page {
        min-height: 100vh;
        padding: 28px 18px 48px;
    }

    .email-container {
        width: min(880px, 100%);
        margin: 0 auto;
    }

    /* =====================================================
       HEADER HALAMAN
       ===================================================== */

    .email-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
    }

    .email-page-heading h1 {
        margin: 0;
        color: #111827;
        font-size: 25px;
        line-height: 1.2;
    }

    .email-page-heading p {
        margin: 5px 0 0;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    .email-header-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .email-header-link {
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

    .email-header-link:hover {
        border-color: #9ca3af;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .email-header-link.dashboard {
        border-color: #242424;
        color: #ffffff;
        background: #242424;
    }

    .email-header-link.dashboard:hover {
        background: #111111;
    }

    /* =====================================================
       IDENTITAS AKUN
       ===================================================== */

    .email-account-summary {
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

    .email-account-icon {
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

    .email-account-information {
        min-width: 0;
        flex: 1;
    }

    .email-account-information strong {
        display: block;
        overflow: hidden;
        color: #111827;
        font-size: 14px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .email-account-information span {
        display: block;
        margin-top: 5px;
        overflow: hidden;
        color: #6b7280;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .email-account-status {
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

    .email-account-status::before {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #20b26b;
        content: "";
    }

    /* =====================================================
       LAYOUT UTAMA
       ===================================================== */

    .email-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.25fr)
            minmax(260px, 0.75fr);
        gap: 16px;
    }

    .email-card {
        overflow: hidden;
        border: 1px solid #dce2e8;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .email-card-header {
        padding: 15px 17px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .email-card-header h2 {
        margin: 0;
        color: #111827;
        font-size: 14px;
    }

    .email-card-header p {
        margin: 5px 0 0;
        color: #6b7280;
        font-size: 11px;
        line-height: 1.45;
    }

    .email-card-body {
        padding: 17px;
    }

    /* =====================================================
       FORM
       ===================================================== */

    .email-field {
        display: grid;
        gap: 7px;
        margin-bottom: 15px;
    }

    .email-field:last-child {
        margin-bottom: 0;
    }

    .email-field label {
        color: #374151;
        font-size: 12px;
        font-weight: 800;
    }

    .email-field-description {
        color: #6b7280;
        font-size: 10px;
        line-height: 1.45;
    }

    .email-input,
    .email-textarea {
        width: 100%;
        border: 1px solid #ccd3da;
        border-radius: 9px;
        outline: none;
        color: #111827;
        background: #ffffff;
        font-size: 13px;
        transition:
            border-color 0.18s ease,
            box-shadow 0.18s ease;
    }

    .email-input {
        height: 42px;
        padding: 0 12px;
    }

    .email-textarea {
        min-height: 105px;
        padding: 11px 12px;
        resize: vertical;
    }

    .email-input:focus,
    .email-textarea:focus {
        border-color: #1478e8;
        box-shadow: 0 0 0 3px rgba(20, 120, 232, 0.12);
    }

    .email-input[readonly] {
        color: #6b7280;
        background: #f3f5f7;
        cursor: not-allowed;
    }

    .email-input.is-invalid,
    .email-textarea.is-invalid {
        border-color: #d71920;
        box-shadow: 0 0 0 3px rgba(215, 25, 32, 0.1);
    }

    .email-field-error {
        display: none;
        color: #c51f2b;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.4;
    }

    .email-field-error.is-visible {
        display: block;
    }

    /* =====================================================
       TOMBOL
       ===================================================== */

    .email-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #edf0f3;
    }

    .email-button {
        display: inline-flex;
        min-height: 41px;
        align-items: center;
        justify-content: center;
        padding: 0 17px;
        border: 0;
        border-radius: 9px;
        color: #ffffff;
        background: #1478e8;
        font-size: 12px;
        font-weight: 900;
    }

    .email-button:hover {
        background: #0d67cb;
    }

    .email-button.secondary {
        border: 1px solid #ccd3da;
        color: #374151;
        background: #ffffff;
    }

    .email-button.secondary:hover {
        background: #f8fafc;
    }

    /* =====================================================
       INFORMASI KEAMANAN
       ===================================================== */

    .email-information-list {
        display: grid;
        gap: 10px;
    }

    .email-information-item {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 10px;
        align-items: flex-start;
        padding: 12px;
        border: 1px solid #e1e6eb;
        border-radius: 10px;
        background: #ffffff;
    }

    .email-information-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 10px;
        color: #ffffff;
        background: #30363d;
        font-size: 17px;
    }

    .email-information-item:nth-child(2)
    .email-information-icon {
        background: #1478e8;
    }

    .email-information-item:nth-child(3)
    .email-information-icon {
        background: #e06426;
    }

    .email-information-content strong {
        display: block;
        color: #111827;
        font-size: 12px;
    }

    .email-information-content small {
        display: block;
        margin-top: 5px;
        color: #6b7280;
        font-size: 10px;
        line-height: 1.5;
    }

    /* =====================================================
       STATUS PENGAJUAN
       ===================================================== */

    .email-request-status {
        display: none;
        margin-top: 16px;
        padding: 15px;
        border: 1px solid #f3d6a4;
        border-radius: 11px;
        color: #8a5708;
        background: #fff8e8;
    }

    .email-request-status.is-visible {
        display: block;
    }

    .email-request-status strong {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
    }

    .email-request-status span {
        display: block;
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .email-frontend-note {
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
       TOAST
       ===================================================== */

    .email-toast {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 5000;
        max-width: 360px;
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

    .email-toast.is-visible {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    .email-toast.is-error {
        background: #c71922;
    }

    /* =====================================================
       RESPONSIVE
       ===================================================== */

    @media (max-width: 760px) {
        .email-page {
            padding: 18px 12px 35px;
        }

        .email-page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .email-header-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .email-grid {
            grid-template-columns: 1fr;
        }

        .email-account-summary {
            align-items: flex-start;
        }

        .email-account-status {
            margin-left: auto;
        }

        .email-form-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .email-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="email-page">
    <div class="email-container">

        {{-- Header halaman --}}
        <header class="email-page-header">
            <div class="email-page-heading">
                <h1>Ubah Email</h1>

                <p>
                    Ajukan perubahan alamat email yang digunakan
                    untuk mengakses SYNRGYPRO.
                </p>
            </div>

            <div class="email-header-actions">
                <a
                    href="{{ route('profile.settings') }}"
                    class="email-header-link"
                >
                    <span aria-hidden="true">←</span>
                    <span>Pengaturan Akun</span>
                </a>

                <a
                    href="{{ route('dashboard') }}"
                    class="email-header-link dashboard"
                >
                    Dashboard
                </a>
            </div>
        </header>

        {{-- Ringkasan akun --}}
        <section class="email-account-summary">
            <div class="email-account-icon">
                ✉
            </div>

            <div class="email-account-information">
                <strong>{{ $account['name'] }}</strong>

                <span>
                    Email aktif: {{ $account['current_email'] }}
                </span>
            </div>

            <span class="email-account-status">
                Terverifikasi
            </span>
        </section>

        <div class="email-grid">

            {{-- Form perubahan email --}}
            <section class="email-card">
                <div class="email-card-header">
                    <h2>Form Pengajuan Perubahan Email</h2>

                    <p>
                        Masukkan email baru dan alasan perubahan.
                    </p>
                </div>

                <div class="email-card-body">
                    <form
                        id="changeEmailForm"
                        novalidate
                    >
                        <div class="email-field">
                            <label for="currentEmail">
                                Email saat ini
                            </label>

                            <input
                                type="email"
                                class="email-input"
                                id="currentEmail"
                                value="{{ $account['current_email'] }}"
                                readonly
                            >
                        </div>

                        <div class="email-field">
                            <label for="newEmail">
                                Email baru
                            </label>

                            <input
                                type="email"
                                class="email-input"
                                id="newEmail"
                                placeholder="contoh@perusahaan.com"
                                autocomplete="email"
                            >

                            <span class="email-field-description">
                                Gunakan alamat email aktif yang dapat
                                menerima verifikasi.
                            </span>

                            <span
                                class="email-field-error"
                                id="newEmailError"
                            ></span>
                        </div>

                        <div class="email-field">
                            <label for="confirmEmail">
                                Konfirmasi email baru
                            </label>

                            <input
                                type="email"
                                class="email-input"
                                id="confirmEmail"
                                placeholder="Masukkan kembali email baru"
                                autocomplete="email"
                            >

                            <span
                                class="email-field-error"
                                id="confirmEmailError"
                            ></span>
                        </div>

                        <div class="email-field">
                            <label for="changeReason">
                                Alasan perubahan
                            </label>

                            <textarea
                                class="email-textarea"
                                id="changeReason"
                                placeholder="Tuliskan alasan perubahan email..."
                            ></textarea>

                            <span class="email-field-description">
                                Minimal 10 karakter.
                            </span>

                            <span
                                class="email-field-error"
                                id="changeReasonError"
                            ></span>
                        </div>

                        <div class="email-form-actions">
                            <button
                                type="button"
                                class="email-button secondary"
                                id="resetEmailForm"
                            >
                                Kosongkan Form
                            </button>

                            <button
                                type="submit"
                                class="email-button"
                            >
                                Ajukan Perubahan
                            </button>
                        </div>
                    </form>

                    <div
                        class="email-request-status"
                        id="emailRequestStatus"
                    >
                        <strong>
                            Simulasi pengajuan tersimpan
                        </strong>

                        <span id="emailRequestDescription"></span>
                    </div>
                </div>
            </section>

            {{-- Informasi keamanan --}}
            <aside class="email-card">
                <div class="email-card-header">
                    <h2>Informasi Penting</h2>

                    <p>
                        Ketentuan perubahan email akun.
                    </p>
                </div>

                <div class="email-card-body">
                    <div class="email-information-list">
                        <div class="email-information-item">
                            <span class="email-information-icon">
                                🔒
                            </span>

                            <div class="email-information-content">
                                <strong>
                                    Tidak langsung berubah
                                </strong>

                                <small>
                                    Pada sistem sebenarnya, email baru
                                    perlu divalidasi oleh backend.
                                </small>
                            </div>
                        </div>

                        <div class="email-information-item">
                            <span class="email-information-icon">
                                ✓
                            </span>

                            <div class="email-information-content">
                                <strong>
                                    Memerlukan verifikasi
                                </strong>

                                <small>
                                    Email baru harus aktif dan dapat
                                    menerima proses verifikasi.
                                </small>
                            </div>
                        </div>

                        <div class="email-information-item">
                            <span class="email-information-icon">
                                👤
                            </span>

                            <div class="email-information-content">
                                <strong>
                                    Persetujuan administrator
                                </strong>

                                <small>
                                    Nantinya administrator dapat
                                    menyetujui atau menolak pengajuan.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <div class="email-frontend-note">
            Halaman ini masih berupa simulasi front end. Pengajuan hanya
            disimpan pada localStorage browser dan belum mengubah email
            login, file .env, akun Google, maupun data database.
        </div>

    </div>
</div>

<div
    class="email-toast"
    id="emailToast"
    role="status"
    aria-live="polite"
>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form =
        document.getElementById('changeEmailForm');

    const currentEmail =
        document.getElementById('currentEmail');

    const newEmail =
        document.getElementById('newEmail');

    const confirmEmail =
        document.getElementById('confirmEmail');

    const changeReason =
        document.getElementById('changeReason');

    const newEmailError =
        document.getElementById('newEmailError');

    const confirmEmailError =
        document.getElementById('confirmEmailError');

    const changeReasonError =
        document.getElementById('changeReasonError');

    const resetButton =
        document.getElementById('resetEmailForm');

    const requestStatus =
        document.getElementById('emailRequestStatus');

    const requestDescription =
        document.getElementById('emailRequestDescription');

    const toast =
        document.getElementById('emailToast');

    const storageKey =
        'synrgyproEmailChangeRequest';

    let toastTimer = null;

    if (
        !form ||
        !currentEmail ||
        !newEmail ||
        !confirmEmail ||
        !changeReason
    ) {
        return;
    }

    function showToast(message, type) {
        if (!toast) {
            return;
        }

        toast.textContent = message;

        toast.classList.toggle(
            'is-error',
            type === 'error'
        );

        toast.classList.add('is-visible');

        window.clearTimeout(toastTimer);

        toastTimer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2800);
    }

    function clearFieldError(input, errorElement) {
        input.classList.remove('is-invalid');

        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.remove('is-visible');
        }
    }

    function setFieldError(
        input,
        errorElement,
        message
    ) {
        input.classList.add('is-invalid');

        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('is-visible');
        }
    }

    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function clearAllErrors() {
        clearFieldError(newEmail, newEmailError);

        clearFieldError(
            confirmEmail,
            confirmEmailError
        );

        clearFieldError(
            changeReason,
            changeReasonError
        );
    }

    function validateForm() {
        clearAllErrors();

        const currentEmailValue =
            currentEmail.value.trim().toLowerCase();

        const newEmailValue =
            newEmail.value.trim().toLowerCase();

        const confirmEmailValue =
            confirmEmail.value.trim().toLowerCase();

        const reasonValue =
            changeReason.value.trim();

        let isValid = true;

        if (!newEmailValue) {
            setFieldError(
                newEmail,
                newEmailError,
                'Email baru wajib diisi.'
            );

            isValid = false;
        } else if (!isValidEmail(newEmailValue)) {
            setFieldError(
                newEmail,
                newEmailError,
                'Format email baru tidak valid.'
            );

            isValid = false;
        } else if (
            newEmailValue === currentEmailValue
        ) {
            setFieldError(
                newEmail,
                newEmailError,
                'Email baru tidak boleh sama dengan email saat ini.'
            );

            isValid = false;
        }

        if (!confirmEmailValue) {
            setFieldError(
                confirmEmail,
                confirmEmailError,
                'Konfirmasi email wajib diisi.'
            );

            isValid = false;
        } else if (
            confirmEmailValue !== newEmailValue
        ) {
            setFieldError(
                confirmEmail,
                confirmEmailError,
                'Konfirmasi email tidak sama.'
            );

            isValid = false;
        }

        if (reasonValue.length < 10) {
            setFieldError(
                changeReason,
                changeReasonError,
                'Alasan harus berisi minimal 10 karakter.'
            );

            isValid = false;
        }

        return isValid;
    }

    function displayStoredRequest(request) {
        if (
            !requestStatus ||
            !requestDescription ||
            !request
        ) {
            return;
        }

        requestDescription.textContent =
            'Email baru: ' +
            request.newEmail +
            '. Status: Menunggu persetujuan ' +
            '(simulasi front end).';

        requestStatus.classList.add('is-visible');
    }

    function loadStoredRequest() {
        const storedRequest =
            localStorage.getItem(storageKey);

        if (!storedRequest) {
            return;
        }

        try {
            displayStoredRequest(
                JSON.parse(storedRequest)
            );
        } catch (error) {
            localStorage.removeItem(storageKey);
        }
    }

    newEmail.addEventListener('input', function () {
        clearFieldError(newEmail, newEmailError);
    });

    confirmEmail.addEventListener(
        'input',
        function () {
            clearFieldError(
                confirmEmail,
                confirmEmailError
            );
        }
    );

    changeReason.addEventListener(
        'input',
        function () {
            clearFieldError(
                changeReason,
                changeReasonError
            );
        }
    );

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!validateForm()) {
            showToast(
                'Periksa kembali data yang dimasukkan.',
                'error'
            );

            return;
        }

        const requestData = {
            currentEmail:
                currentEmail.value.trim(),

            newEmail:
                newEmail.value.trim(),

            reason:
                changeReason.value.trim(),

            status:
                'pending',

            submittedAt:
                new Date().toISOString(),
        };

        localStorage.setItem(
            storageKey,
            JSON.stringify(requestData)
        );

        displayStoredRequest(requestData);

        showToast(
            'Simulasi pengajuan perubahan email berhasil disimpan.',
            'success'
        );
    });

    resetButton.addEventListener(
        'click',
        function () {
            form.reset();

            clearAllErrors();

            localStorage.removeItem(storageKey);

            if (requestStatus) {
                requestStatus.classList.remove(
                    'is-visible'
                );
            }

            if (requestDescription) {
                requestDescription.textContent = '';
            }

            showToast(
                'Form dan simulasi pengajuan telah dikosongkan.',
                'success'
            );
        }
    );

    loadStoredRequest();
});
</script>
@endpush