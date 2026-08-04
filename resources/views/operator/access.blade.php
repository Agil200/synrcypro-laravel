<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Verifikasi Operator — SYNRGYPRO</title>

    <style>
        :root {
            --operator-blue: #6f89ee;
            --operator-blue-dark: #536dd4;
            --operator-white: #ffffff;
            --operator-muted: rgba(255, 255, 255, .76);
            --operator-panel: rgba(9, 17, 31, .82);
            --operator-border: rgba(255, 255, 255, .20);
            --operator-danger: #ffd7dd;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--operator-white);
            background: #030711;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        button,
        input {
            font: inherit;
        }

        .operator-access-background,
        .operator-access-overlay {
            position: fixed;
            inset: 0;
        }

        .operator-access-background {
            z-index: 0;
            background:
                url("{{ asset('assets/images/control-room.jpg') }}")
                center center / cover no-repeat;
            transform: scale(1.01);
        }

        .operator-access-overlay {
            z-index: 1;
            background:
                radial-gradient(
                    circle at 50% 48%,
                    rgba(5, 13, 27, .08) 0%,
                    rgba(3, 9, 20, .30) 48%,
                    rgba(1, 5, 13, .74) 100%
                ),
                linear-gradient(
                    180deg,
                    rgba(1, 5, 12, .18) 0%,
                    rgba(1, 5, 12, .54) 100%
                );
            box-shadow: inset 0 0 150px rgba(0, 0, 0, .48);
        }

        .operator-access-shell {
            position: relative;
            z-index: 2;
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 28px 20px;
        }

        .operator-access-panel {
            width: min(100%, 410px);
            padding: 25px;
            border: 1px solid var(--operator-border);
            border-radius: 18px;
            background: var(--operator-panel);
            box-shadow:
                0 26px 70px rgba(0, 0, 0, .46),
                inset 0 1px 0 rgba(255, 255, 255, .06);
            backdrop-filter: blur(13px);
            -webkit-backdrop-filter: blur(13px);
        }

        .operator-access-heading {
            margin-bottom: 21px;
            text-align: center;
        }

        .operator-access-kicker {
            display: block;
            margin-bottom: 7px;
            color: rgba(255, 255, 255, .78);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .operator-access-heading h1 {
            margin: 0;
            color: #ffffff;
            font-size: clamp(22px, 4vw, 28px);
            font-weight: 900;
            letter-spacing: -.025em;
        }

        .operator-access-heading p {
            max-width: 335px;
            margin: 8px auto 0;
            color: var(--operator-muted);
            font-size: 11px;
            line-height: 1.55;
        }

        .operator-access-alert {
            margin-bottom: 14px;
            padding: 11px 12px;
            border: 1px solid rgba(255, 115, 132, .48);
            border-radius: 9px;
            color: var(--operator-danger);
            background: rgba(91, 12, 30, .88);
            font-size: 11px;
            font-weight: 700;
            line-height: 1.45;
            text-align: center;
        }

        .operator-access-field {
            display: grid;
            gap: 7px;
            margin-bottom: 13px;
        }

        .operator-access-field > span {
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .055em;
        }

        .operator-access-input {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid rgba(255, 255, 255, .30);
            border-radius: 9px;
            outline: none;
            color: #152033;
            background: rgba(255, 255, 255, .97);
            box-shadow: 0 7px 18px rgba(0, 0, 0, .14);
            font-size: 13px;
            font-weight: 750;
            transition:
                border-color .18s ease,
                box-shadow .18s ease,
                transform .18s ease;
        }

        .operator-access-input::placeholder {
            color: #8a94a6;
            font-weight: 650;
        }

        .operator-access-input:focus {
            border-color: #8fa5ff;
            box-shadow:
                0 0 0 4px rgba(111, 137, 238, .22),
                0 9px 22px rgba(0, 0, 0, .20);
            transform: translateY(-1px);
        }

        .operator-access-help {
            margin: -2px 0 15px;
            color: rgba(255, 255, 255, .72);
            font-size: 10px;
            line-height: 1.45;
        }

        .operator-access-help strong {
            color: #ffffff;
        }

        .operator-access-note {
            margin: 5px 0 17px;
            padding: 10px 11px;
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 9px;
            color: rgba(255, 255, 255, .73);
            background: rgba(255, 255, 255, .06);
            font-size: 9px;
            line-height: 1.5;
        }

        .operator-access-actions {
            display: grid;
            gap: 10px;
        }

        .operator-access-button {
            display: grid;
            width: 100%;
            min-height: 48px;
            place-items: center;
            padding: 0 16px;
            border: 0;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .015em;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform .18s ease,
                filter .18s ease,
                box-shadow .18s ease;
        }

        .operator-access-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .operator-access-submit {
            color: #ffffff;
            background: linear-gradient(
                180deg,
                var(--operator-blue),
                var(--operator-blue-dark)
            );
            box-shadow: 0 12px 25px rgba(72, 94, 195, .34);
        }

        .operator-access-back {
            min-height: 41px;
            color: rgba(255, 255, 255, .82);
            background: rgba(255, 255, 255, .09);
        }

        @media (max-width: 560px) {
            .operator-access-shell {
                align-items: end;
                padding: 18px 14px 24px;
            }

            .operator-access-panel {
                padding: 21px 18px;
                border-radius: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="operator-access-background" aria-hidden="true"></div>
    <div class="operator-access-overlay" aria-hidden="true"></div>

    <main class="operator-access-shell">
        <section
            class="operator-access-panel"
            aria-labelledby="operatorAccessTitle"
        >
            <header class="operator-access-heading">
                <span class="operator-access-kicker">
                    PORTAL OPERATOR · READ ONLY
                </span>

                <h1 id="operatorAccessTitle">
                    Verifikasi Data Operator
                </h1>

                <p>
                    Masukkan NRP dan tanggal lahir sesuai
                    MASTER_DATABASE untuk melihat dashboard pribadi.
                </p>
            </header>

            <form method="POST" action="{{ route('operator.verify') }}">
                @csrf

                @if (session('error'))
                    <div class="operator-access-alert" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="operator-access-alert" role="alert">
                        {{ $errors->first('access') ?: $errors->first() }}
                    </div>
                @endif

                <label class="operator-access-field">
                    <span>NRP OPERATOR</span>
                    <input
                        class="operator-access-input"
                        type="text"
                        name="nrp"
                        value="{{ old('nrp') }}"
                        maxlength="50"
                        autocomplete="username"
                        inputmode="numeric"
                        placeholder="Masukkan NRP"
                        required
                        autofocus
                    >
                </label>

                <label class="operator-access-field">
                    <span>TANGGAL LAHIR · 8 ANGKA</span>
                    <input
                        class="operator-access-input"
                        type="text"
                        name="tanggal_lahir"
                        value="{{ old('tanggal_lahir') }}"
                        maxlength="8"
                        minlength="8"
                        inputmode="numeric"
                        autocomplete="bday"
                        placeholder="Contoh: 12071990"
                        pattern="[0-9]{8}"
                        title="Masukkan 8 angka dengan format DDMMYYYY"
                        aria-describedby="operatorBirthDateHelp"
                        required
                    >
                </label>

                <p
                    class="operator-access-help"
                    id="operatorBirthDateHelp"
                >
                    Masukkan <strong>angka saja tanpa spasi atau tanda baca</strong>.
                    Format: <strong>DDMMYYYY</strong>, contoh 12 Juli 1990
                    ditulis <strong>12071990</strong>.
                </p>

                <p class="operator-access-note">
                    Portal hanya menampilkan data operator sendiri,
                    APD, Coaching &amp; Counselling, Surat Teguran, dan
                    Surat Peringatan. Tidak tersedia akses tambah,
                    edit, hapus, atau perubahan status.
                </p>

                <div class="operator-access-actions">
                    <button
                        class="operator-access-button operator-access-submit"
                        type="submit"
                    >
                        TAMPILKAN DATA SAYA
                    </button>

                    <a
                        class="operator-access-button operator-access-back"
                        href="{{ route('login') }}"
                    >
                        KEMBALI KE HALAMAN LOGIN
                    </a>
                </div>
            </form>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const numericFields = document.querySelectorAll(
                'input[name="nrp"], input[name="tanggal_lahir"]'
            );

            numericFields.forEach(function (field) {
                field.addEventListener('input', function () {
                    field.value = field.value.replace(/\D+/g, '');
                });
            });
        });
    </script>
</body>
</html>
