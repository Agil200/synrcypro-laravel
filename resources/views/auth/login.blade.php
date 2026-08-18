@extends('layouts.app')

@section('title', 'Login — SYNRCYPRO')
@section('body-class', 'login-page')

@section('content')

<div class="auth-login-background" aria-hidden="true"></div>
<div class="auth-login-overlay" aria-hidden="true"></div>

<header class="auth-login-company">
    <img
        src="{{ asset('assets/images/ppa-logo.png') }}"
        alt="Putra Perkasa Abadi"
    >
</header>

<main class="auth-login-shell">
    <section
        class="auth-login-panel"
        aria-labelledby="login-title"
    >
        <img
            src="{{ asset('assets/images/synrgypro-logo.png') }}"
            class="auth-login-app-logo"
            alt="SYNRGYPRO Production Monitoring"
            id="login-title"
        >

        <p class="auth-login-version">
            VERSION V1.0
        </p>

        @if (session('error'))
            <div class="auth-login-alert" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="auth-login-actions">

            {{-- LOGIN GOOGLE --}}
            <a
                href="{{ route('auth.google') }}"
                class="auth-login-button auth-login-google"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        fill="#4285F4"
                        d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.92h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.75 2.98-4.33 2.98-7.41Z"
                    />
                    <path
                        fill="#34A853"
                        d="M12 22c2.7 0 4.98-.9 6.64-2.36l-3.24-2.54c-.9.6-2.05.96-3.4.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.62A10 10 0 0 0 12 22Z"
                    />
                    <path
                        fill="#FBBC05"
                        d="M6.39 13.93A6 6 0 0 1 6.08 12c0-.67.11-1.32.31-1.93V7.45H3.04A10 10 0 0 0 2 12c0 1.63.39 3.17 1.04 4.55l3.35-2.62Z"
                    />
                    <path
                        fill="#EA4335"
                        d="M12 5.94c1.47 0 2.79.5 3.83 1.5l2.87-2.88A9.64 9.64 0 0 0 12 2a10 10 0 0 0-8.96 5.45l3.35 2.62C7.18 7.7 9.39 5.94 12 5.94Z"
                    />
                </svg>

                <span>LOGIN WITH ACCOUNT GOOGLE</span>
            </a>

            {{-- LOGIN GUEST --}}
            <form
                method="POST"
                action="{{ route('auth.guest') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="auth-login-button auth-login-guest"
                >
                    SIGN IN AS GUEST
                </button>
            </form>

            {{-- AKSES FORM PENGAMBILAN BARANG (TANPA LOGIN) --}}
            <a
                href="{{ route('barang.public.form') }}"
                class="auth-login-button"
                style="background: #0f766e; color: #ffffff; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; border: none; box-shadow: 0 4px 14px rgba(15, 118, 110, 0.28);"
            >
                <span>📦 FORM PENGAMBILAN BARANG</span>
            </a>

        </div>

        <p class="auth-login-help">
            JIKA TERDAPAT PERTANYAAN ATAU MEMBUTUHKAN SESUATU?

            <a
                href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRCYPRO%20Support"
                target="_blank"
                rel="noopener noreferrer"
            >
                CONTACT US
            </a>
        </p>

    </section>
</main>

@endsection