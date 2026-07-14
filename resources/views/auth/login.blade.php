@extends('layouts.app')

@section('title', 'Login — SYNRCYPRO')
@section('body-class', 'login-page')

@section('content')
<div class="login-background" aria-hidden="true"></div>
<div class="login-overlay" aria-hidden="true"></div>

<header class="login-company">
    <div class="company-mark">
        <svg viewBox="0 0 64 64" aria-hidden="true">
            <circle cx="32" cy="32" r="29" fill="none" stroke="currentColor" stroke-width="3"/>
            <path d="M18 43 30 17h8l9 26h-8l-2-7H27l-3 7Zm12-13h6l-3-10Z" fill="currentColor"/>
        </svg>
    </div>
    <div>
        <strong>PUTRA PERKASA ABADI</strong>
        <span>Mining Contractor & Operations</span>
    </div>
</header>

<main class="login-shell">
    <section class="login-card" aria-labelledby="login-title">
        <div class="brand-symbol" aria-hidden="true">
            <svg viewBox="0 0 110 82">
                <path d="M14 22 37 7l15 12 19-9 25 15-25 16-19-9-15 12-23-15Z" fill="#05d6ef"/>
                <path d="M14 38 37 23l15 12 19-9 25 15-25 16-19-9-15 12-23-15Z" fill="#ff2158"/>
                <path d="M14 54 37 39l15 12 19-9 25 15-25 16-19-9-15 12-23-15Z" fill="#ffd31f"/>
                <path d="M13 10h12v10H13zm15 0h12v5H28z" fill="#08e0f5"/>
            </svg>
        </div>

        <div class="brand-copy">
            <h1 id="login-title">SYNRCYPRO</h1>
            <p>SYNCHRONIZED PROJECT OPERATION</p>
            <span>VERSION V1.0</span>
        </div>

        @if (session('error'))
            <div class="login-alert" role="alert">{{ session('error') }}</div>
        @endif

        <div class="login-actions">
            <a class="login-button login-google" href="{{ route('auth.google') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.92h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.75 2.98-4.33 2.98-7.41Z"/>
                    <path fill="#34A853" d="M12 22c2.7 0 4.98-.9 6.64-2.36l-3.24-2.54c-.9.6-2.05.96-3.4.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.62A10 10 0 0 0 12 22Z"/>
                    <path fill="#FBBC05" d="M6.39 13.93A6 6 0 0 1 6.08 12c0-.67.11-1.32.31-1.93V7.45H3.04A10 10 0 0 0 2 12c0 1.63.39 3.17 1.04 4.55l3.35-2.62Z"/>
                    <path fill="#EA4335" d="M12 5.94c1.47 0 2.79.5 3.83 1.5l2.87-2.88A9.64 9.64 0 0 0 12 2a10 10 0 0 0-8.96 5.45l3.35 2.62C7.18 7.7 9.39 5.94 12 5.94Z"/>
                </svg>
                <span>LOGIN WITH ACCOUNT GOOGLE</span>
            </a>

            <form method="POST" action="{{ route('auth.guest') }}">
                @csrf
                <button class="login-button login-guest" type="submit">
                    <span>SIGN IN AS GUEST</span>
                </button>
            </form>
        </div>

        <p class="login-help">
            JIKA TERDAPAT PERTANYAAN ATAU MEMBUTUHKAN SESUATU?
            <a href="mailto:it.support@ppa.co.id">CONTACT US</a>
        </p>
    </section>
</main>

<footer class="login-footer">© {{ date('Y') }} Putra Perkasa Abadi · Secure Operation Portal</footer>
@endsection
