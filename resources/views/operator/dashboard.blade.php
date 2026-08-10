@php
    $photoUrl = $employee['foto_preview_url']
        ?? $employee['foto_url']
        ?? null;

    $initials = collect(preg_split('/\s+/', trim((string) ($employee['nama'] ?? 'OP'))))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $formatDate = function ($value, string $fallback = '-') {
        if (!$value || $value === '-') {
            return $fallback;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>Dashboard Operator — SYNRGYPRO | MINA AI</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #172033;
            background: #eef1f5;
            font-family: Inter, Arial, sans-serif;
        }
        .topbar {
            position: sticky;
            z-index: 20;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 68px;
            padding: 10px clamp(16px, 4vw, 54px);
            color: #fff;
            background: linear-gradient(100deg, #161b24, #d25a2c);
            box-shadow: 0 8px 24px rgba(15,23,42,.16);
        }
        .brand strong { display: block; font-size: 18px; }
        .brand small { color: rgba(255,255,255,.75); }
        .logout {
            min-height: 38px;
            padding: 0 15px;
            border: 1px solid rgba(255,255,255,.42);
            border-radius: 9px;
            color: #fff;
            background: rgba(255,255,255,.10);
            font-weight: 900;
            cursor: pointer;
        }
        .page {
            width: min(1460px, calc(100% - 28px));
            margin: 22px auto 38px;
        }
        .readonly {
            margin-bottom: 14px;
            padding: 11px 14px;
            border: 1px solid #bbd1ff;
            border-radius: 10px;
            color: #1e3a8a;
            background: #eff6ff;
            font-size: 12px;
            font-weight: 800;
        }
        .profile {
            display: grid;
            grid-template-columns: 190px 1fr;
            overflow: hidden;
            border: 1px solid #d8dee7;
            border-radius: 17px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(15,23,42,.07);
        }
        .profile-side {
            display: grid;
            align-content: center;
            justify-items: center;
            gap: 12px;
            min-height: 245px;
            padding: 20px;
            background: #f8fafc;
        }
        .photo {
            display: grid;
            width: 132px;
            height: 162px;
            place-items: center;
            overflow: hidden;
            border: 1px solid #d7dee8;
            border-radius: 13px;
            color: #475569;
            background: #e9eef5;
            font-size: 34px;
            font-weight: 900;
        }
        .photo img { width: 100%; height: 100%; object-fit: cover; }
        .profile-main { min-width: 0; }
        .profile-head {
            padding: 21px 24px;
            color: #fff;
            background: linear-gradient(100deg, #172033, #b64d29);
        }
        .profile-head small { font-weight: 900; letter-spacing: .09em; }
        .profile-head h1 { margin: 6px 0 4px; font-size: 28px; }
        .profile-head p { margin: 0; color: rgba(255,255,255,.8); }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 13px;
            padding: 20px 24px 24px;
        }
        .info {
            min-width: 0;
            padding-bottom: 9px;
            border-bottom: 1px solid #e6eaf0;
        }
        .info.wide { grid-column: span 2; }
        .info small {
            display: block;
            margin-bottom: 5px;
            color: #6b7688;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .info strong { font-size: 12px; word-break: break-word; }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin: 16px 0;
        }
        .stat {
            padding: 16px;
            border: 1px solid #d8dee7;
            border-radius: 12px;
            background: #fff;
        }
        .stat span { color: #667085; font-size: 11px; font-weight: 800; }
        .stat strong { display: block; margin-top: 5px; font-size: 27px; }
        .section {
            margin-top: 15px;
            overflow: hidden;
            border: 1px solid #d8dee7;
            border-radius: 14px;
            background: #fff;
        }
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid #e2e7ed;
        }
        .section-head h2 { margin: 0; font-size: 17px; }
        .section-head span {
            padding: 5px 9px;
            border-radius: 999px;
            color: #475569;
            background: #eef2f6;
            font-size: 10px;
            font-weight: 900;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; min-width: 850px; border-collapse: collapse; }
        th, td {
            padding: 11px 13px;
            border-bottom: 1px solid #e8ebef;
            text-align: left;
            vertical-align: top;
            font-size: 11px;
        }
        th {
            color: #475569;
            background: #f7f9fb;
            font-size: 10px;
            letter-spacing: .035em;
        }
        .badge {
            display: inline-flex;
            margin: 2px 3px 2px 0;
            padding: 4px 7px;
            border-radius: 999px;
            color: #344054;
            background: #eef2f6;
            font-size: 9px;
            font-weight: 900;
        }
        .badge.reject { color: #991b1b; background: #fff1f2; }
        .badge.ready { color: #166534; background: #ecfdf3; }
        .badge.picked { color: #3730a3; background: #eeedff; }
        .empty { padding: 30px; color: #667085; text-align: center; }
        @media (max-width: 900px) {
            .profile { grid-template-columns: 1fr; }
            .profile-side { min-height: auto; }
            .info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            .profile-head h1 { font-size: 22px; }
            .info-grid, .stats { grid-template-columns: 1fr; }
            .info.wide { grid-column: auto; }
        }


        /* =========================================================
           SYNRGY ASSISTANT
           ========================================================= */
        
      #synrgyChatButton {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 9998;

    display: grid;
    width: 60px;
    height: 60px;
    place-items: center;

    border: 0;
    border-radius: 50%;
    overflow: hidden;

    background: linear-gradient(135deg, #172033, #b64d29);
    box-shadow: 0 10px 28px rgba(15,23,42,.28);

    cursor: pointer;
}


#synrgyChatButton img {
    width: 52px;
    height: 52px;
    object-fit: cover;
    border-radius: 50%;
}

        #synrgyChatButton:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 34px rgba(15, 23, 42, .34);
        }
        #synrgyChatButton:focus-visible,
        #synrgyChatSend:focus-visible,
        .synrgy-chat-actions button:focus-visible {
            outline: 3px solid rgba(182, 77, 41, .28);
            outline-offset: 2px;
        }
        #synrgyChatPanel {
            position: fixed;
            right: 24px;
            bottom: 96px;
            z-index: 9999;
            display: none;
            width: 390px;
            height: 520px;
            max-width: calc(100vw - 30px);
            max-height: calc(100vh - 118px);
            overflow: hidden;
            border: 1px solid #d8dee7;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 18px 55px rgba(15, 23, 42, .28);
            flex-direction: column;
        }
        #synrgyChatPanel.show { display: flex; }
        .synrgy-chat-header {
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 15px;
            color: #fff;
            background: linear-gradient(100deg, #172033, #b64d29);
        }
        .synrgy-chat-title {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 8px;
        }
        
        .synrgy-chat-icon {
    display:flex;
    width:42px;
    height:42px;
    flex:0 0 42px;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    border-radius:50%;
    background:transparent;
}

.synrgy-chat-icon img {
    width:42px;
    height:42px;
    object-fit:contain;
}



        
                .synrgy-chat-title strong {
            display:block;
            font-size:15px;
            line-height:1.1;
        }

       
        .synrgy-chat-title small {
    display:block;
    margin-top:3px;
    font-size:9px;
    line-height:1.1;
}

        .synrgy-chat-actions {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .synrgy-chat-actions button {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: transparent;
            font-size: 20px;
            cursor: pointer;
        }
        .synrgy-chat-actions button:hover { background: rgba(255,255,255,.12); }
        #synrgyChatMessages {
            flex: 1;
            overflow-y: auto;
            padding: 17px;
            background: #f4f6f8;
            scroll-behavior: smooth;
        }
        .synrgy-chat-message {
            display: flex;
            margin-bottom: 13px;
        }
        .synrgy-chat-message.user { justify-content: flex-end; }
        .synrgy-chat-bubble {
            max-width: 84%;
            padding: 11px 13px;
            border-radius: 14px;
            font-size: 12px;
            line-height: 1.55;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
        .synrgy-chat-message.bot .synrgy-chat-bubble {
            color: #172033;
            background: #fff;
            border: 1px solid #e1e5eb;
            border-bottom-left-radius: 4px;
        }
        .synrgy-chat-message.user .synrgy-chat-bubble {
            color: #fff;
            background: #b64d29;
            border-bottom-right-radius: 4px;
        }
        .synrgy-chat-input-area {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            padding: 11px;
            border-top: 1px solid #e2e7ed;
            background: #fff;
        }
        #synrgyChatInput {
            flex: 1;
            min-height: 42px;
            max-height: 100px;
            padding: 10px 12px;
            border: 1px solid #d0d5dd;
            border-radius: 11px;
            outline: none;
            resize: none;
            color: #172033;
            background: #fff;
            font: inherit;
            font-size: 12px;
            line-height: 1.45;
        }
        #synrgyChatInput:focus {
            border-color: #b64d29;
            box-shadow: 0 0 0 3px rgba(182,77,41,.10);
        }
        #synrgyChatSend {
            width: 43px;
            height: 43px;
            flex: 0 0 43px;
            border: 0;
            border-radius: 10px;
            color: #fff;
            background: #b64d29;
            font-size: 18px;
            cursor: pointer;
        }
        #synrgyChatSend:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
        @media (max-width: 600px) {
            #synrgyChatButton {
                right: 15px;
                bottom: 15px;
            }
            #synrgyChatPanel {
                right: 10px;
                bottom: 82px;
                width: calc(100vw - 20px);
                height: calc(100vh - 102px);
                max-height: none;
            }
        }
    
        /* ===== MINA UI FIX ===== */
        html, body {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
            zoom: 1;
        }

        .page {
            width: min(1460px, calc(100% - 28px));
            margin-left: auto;
            margin-right: auto;
        }

        #synrgyChatPanel {
            transform: none !important;
            zoom: 1 !important;
        }

        #synrgyChatButton {
            transform: none;
        }

        .synrgy-chat-header {
            min-height: 78px;
        }

        .synrgy-chat-title {
            flex: 1;
        }

        .synrgy-chat-title small {
            max-width: 180px;
            white-space: normal;
        }

        .synrgy-chat-icon img,
        #synrgyChatButton img {
            object-fit: cover;
            border-radius: 50%;
        }

</style>
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <strong>SYNRGYPRO — Dashboard Karyawan</strong>
            <small>Portal pribadi karyawan · akses hanya-baca</small>
        </div>
        <form method="POST" action="{{ route('operator.logout') }}">
            @csrf
            <button class="logout" type="submit">Keluar</button>
        </form>
    </header>

    <main class="page">
        <div class="readonly">
            🔒 Mode tamu aktif. Halaman ini hanya menampilkan data milik NRP
            yang telah diverifikasi.
            @if ($snapshotStale)
                Data karyawan sedang memakai cache cadangan terakhir.
            @endif
        </div>

        <section class="profile">
            <aside class="profile-side">
                <div class="photo">
                    @if ($photoUrl)
                        <img
                            src="{{ $photoUrl }}"
                            alt="Foto {{ $employee['nama'] ?? 'operator' }}"
                            referrerpolicy="no-referrer"
                            onerror="this.remove(); this.parentElement.textContent='{{ $initials ?: 'OP' }}';"
                        >
                    @else
                        {{ $initials ?: 'OP' }}
                    @endif
                </div>
                <strong>NRP {{ $employee['nrp'] ?? '-' }}</strong>
            </aside>

            <div class="profile-main">
                <header class="profile-head">
                    <small>DATA KARYAWAN PPA SITE BUKIT ASAM </small>
                    <h1>{{ $employee['nama'] ?? '-' }}</h1>
                    <p>{{ $employee['jabatan'] ?? '-' }}</p>
                </header>

                <div class="info-grid">
                    <div class="info"><small>Departemen</small><strong>{{ $employee['departemen'] ?? '-' }}</strong></div>
                    <div class="info"><small>Perusahaan</small><strong>{{ $employee['perusahaan'] ?? '-' }}</strong></div>
                    <div class="info"><small>Site</small><strong>{{ $employee['site'] ?? '-' }}</strong></div>
                    <div class="info"><small>Status Karyawan</small><strong>{{ $employee['status_karyawan'] ?? '-' }}</strong></div>
                    <div class="info"><small>Tanggal Lahir</small><strong>{{ $employee['tanggal_lahir'] ?? '-' }}</strong></div>
                    <div class="info"><small>Status Tinggal</small><strong>{{ $employee['status_tinggal'] ?? '-' }}</strong></div>
                    <div class="info"><small>Gedung/Kamar</small><strong>{{ $employee['gedung_kamar'] ?? (($employee['gedung'] ?? '-').' / '.($employee['kamar'] ?? '-')) }}</strong></div>
                    <div class="info"><small>Nomor HP</small><strong>{{ $employee['no_hp'] ?? '-' }}</strong></div>
                    <div class="info"><small>Email</small><strong>{{ $employee['email'] ?? '-' }}</strong></div>
                    <div class="info wide"><small>Alamat Lengkap</small><strong>{{ $employee['alamat_lengkap'] ?? '-' }}</strong></div>
                </div>
            </div>
        </section>

        <section class="stats">
            <article class="stat"><span>Riwayat APD</span><strong>{{ $summary['apd'] }}</strong></article>
            <article class="stat"><span>Coaching &amp; Counselling</span><strong>{{ $summary['coaching'] }}</strong></article>
            <article class="stat"><span>Surat Teguran</span><strong>{{ $summary['teguran'] }}</strong></article>
            <article class="stat"><span>Surat Peringatan</span><strong>{{ $summary['peringatan'] }}</strong></article>
        </section>

        <section class="section">
            <header class="section-head">
                <h2>Riwayat APD</h2>
                <span>{{ $apdRequests->count() }} data</span>
            </header>
            @if ($apdRequests->isEmpty())
                <div class="empty">Belum ada data APD untuk NRP ini.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Tanggal Pengajuan</th><th>Barang dan Posisi</th><th>Ukuran Sepatu</th><th>Pengambilan</th></tr></thead>
                        <tbody>
                            @foreach ($apdRequests as $apd)
                                <tr>
                                    <td>{{ $formatDate($apd->tanggal_pengajuan) }}</td>
                                    <td>
                                        @foreach ($apd->items_with_status as $item)
                                            @php
                                                $status = strtoupper((string) ($item['status'] ?? '-'));
                                                $class = $status === 'REJECT' ? 'reject' : ($status === 'READY' ? 'ready' : ($status === 'DIAMBIL' ? 'picked' : ''));
                                                $rejectDate = $item['tanggal_reject'] ?? null;
                                            @endphp
                                            <span class="badge {{ $class }}">
                                                {{ $item['label'] ?? '-' }} · {{ $status }}
                                                @if ($status === 'REJECT' && $rejectDate)
                                                    · {{ $formatDate($rejectDate) }}
                                                @endif
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>{{ $apd->ukuran_sepatu ?: '-' }}</td>
                                    <td>
                                        @if ($apd->pickup)
                                            {{ $formatDate($apd->pickup->tanggal_pengambilan) }}<br>
                                            Diambil oleh {{ $apd->pickup->diambil_oleh ?: '-' }}<br>
                                            Petugas {{ $apd->pickup->petugas ?: '-' }}
                                        @else
                                            Belum diambil
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="section">
            <header class="section-head">
                <h2>Coaching &amp; Counselling</h2>
                <span>{{ $coachingRecords->count() }} data</span>
            </header>
            @if ($coachingRecords->isEmpty())
                <div class="empty">Belum ada data Coaching &amp; Counselling.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Tanggal</th><th>Materi</th><th>Perihal</th><th>Shift</th><th>Keterangan</th><th>Dibuat Oleh</th></tr></thead>
                        <tbody>
                            @foreach ($coachingRecords as $record)
                                <tr>
                                    <td>{{ $formatDate($record->tanggal) }}</td>
                                    <td>{{ $record->materi ?: '-' }}</td>
                                    <td>{{ $record->perihal ?: '-' }}</td>
                                    <td>{{ $record->shift ?: '-' }}</td>
                                    <td>{{ $record->keterangan ?: '-' }}</td>
                                    <td>{{ $record->dibuat_oleh ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        @foreach ([['title' => 'Surat Teguran', 'records' => $teguranRecords], ['title' => 'Surat Peringatan', 'records' => $peringatanRecords]] as $section)
            <section class="section">
                <header class="section-head">
                    <h2>{{ $section['title'] }}</h2>
                    <span>{{ $section['records']->count() }} data</span>
                </header>
                @if ($section['records']->isEmpty())
                    <div class="empty">Belum ada data {{ $section['title'] }}.</div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Tanggal</th><th>Jenis</th><th>Pelanggaran</th><th>Tempat</th><th>Deskripsi</th><th>Atasan</th><th>Berlaku Sampai</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach ($section['records'] as $record)
                                    <tr>
                                        <td>{{ $formatDate($record->tanggal) }}</td>
                                        <td>{{ $record->jenis ?: '-' }}</td>
                                        <td>{{ $record->jenis_pelanggaran ?: '-' }}</td>
                                        <td>{{ $record->tempat_kejadian ?: '-' }}</td>
                                        <td>{{ $record->deskripsi ?: '-' }}</td>
                                        <td>{{ $record->atasan ?: '-' }}</td>
                                        <td>{{ $formatDate($record->expired_date) }}</td>
                                        <td><span class="badge">{{ $record->status ?: '-' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach
    </main>


    <!-- =====================================================
         SYNRGY ASSISTANT
         ===================================================== -->
    <button
    id="synrgyChatButton"
    type="button"
    title="Buka MINA Assistant"
    aria-label="Buka MINA Assistant"
    aria-controls="synrgyChatPanel"
    aria-expanded="false"
>
    <img 
        src="{{ asset('assets/images/chatminers-logo.png') }}" 
        alt="MINA Logo"
    >
</button>

    <section
        id="synrgyChatPanel"
        role="dialog"
        aria-label="MINA Mining Intelligence Assistant"
        aria-hidden="true"
    >
        <header class="synrgy-chat-header">
            <div class="synrgy-chat-title">
                <div class="synrgy-chat-icon">
        <img src="{{ asset('assets/images/chatminers-logo.png') }}" 
         alt="MINA Logo">
            </div>    

                    <strong>MINA</strong>
                    <small>Mining Intelligence Assistant<br>Powered by SYNRGYPRO PPA SITE BA</small>
                </div>
            </div>

            <div class="synrgy-chat-actions">
                <button id="synrgyChatReset" type="button" title="Percakapan baru" aria-label="Percakapan baru">↻</button>
                <button id="synrgyChatClose" type="button" title="Tutup" aria-label="Tutup chatbot">×</button>
            </div>
        </header>

        <div id="synrgyChatMessages" aria-live="polite">
            <div class="synrgy-chat-message bot">
                <div class="synrgy-chat-bubble">Halo 👋

Saya MINA,
Mining Intelligence Assistant.

Saya siap membantu Anda terkait:
⛑ APD
📋 Coaching &amp; Counselling
⚠ Surat Teguran
📄 Surat Peringatan
👤 Data Karyawan

Silakan tanyakan kebutuhan Anda.</div>
            </div>
        </div>

        <div class="synrgy-chat-input-area">
            <textarea
                id="synrgyChatInput"
                rows="1"
                maxlength="2000"
                placeholder="Tanya MINA tentang APD, coaching, atau data Anda..."
                aria-label="Pesan untuk SYNRGY Assistant"
            ></textarea>
            <button id="synrgyChatSend" type="button" title="Kirim" aria-label="Kirim pesan">➤</button>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const openButton = document.getElementById('synrgyChatButton');
            const panel = document.getElementById('synrgyChatPanel');
            const closeButton = document.getElementById('synrgyChatClose');
            const resetButton = document.getElementById('synrgyChatReset');
            const sendButton = document.getElementById('synrgyChatSend');
            const input = document.getElementById('synrgyChatInput');
            const messages = document.getElementById('synrgyChatMessages');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');

            const chatUrl = @json(route('operator.chatbot'));
            const resetUrl = @json(route('operator.chatbot.reset'));

            if (!csrfMeta) {
                console.error('CSRF token tidak ditemukan pada dashboard operator.');
                return;
            }

            const csrfToken = csrfMeta.getAttribute('content');

            function setPanel(open) {
                panel.classList.toggle('show', open);
                panel.setAttribute('aria-hidden', open ? 'false' : 'true');
                openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    window.setTimeout(function () { input.focus(); }, 80);
                }
            }

            openButton.addEventListener('click', function () {
                setPanel(!panel.classList.contains('show'));
            });

            closeButton.addEventListener('click', function () {
                setPanel(false);
            });

            function addMessage(text, type) {
                const wrapper = document.createElement('div');
                wrapper.className = 'synrgy-chat-message ' + type;

                const bubble = document.createElement('div');
                bubble.className = 'synrgy-chat-bubble';

                // textContent sengaja dipakai agar output AI tidak dieksekusi sebagai HTML/JS.
                bubble.textContent = text;

                wrapper.appendChild(bubble);
                messages.appendChild(wrapper);
                messages.scrollTop = messages.scrollHeight;
                return wrapper;
            }

            async function parseJsonResponse(response) {
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Server tidak mengembalikan JSON. HTTP ' + response.status);
                }
                return response.json();
            }

            async function sendMessage() {
                const message = input.value.trim();
                if (!message || sendButton.disabled) {
                    return;
                }

                addMessage(message, 'user');
                input.value = '';
                sendButton.disabled = true;
                const loading = addMessage('Sedang berpikir...', 'bot');

                try {
                    const response = await fetch(chatUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ message: message })
                    });

                    const data = await parseJsonResponse(response);
                    loading.remove();

                    if (response.ok && data.success) {
                        addMessage(data.reply, 'bot');
                    } else {
                        addMessage(data.message || 'MINA sedang tidak dapat diakses.', 'bot');
                    }
                } catch (error) {
                    loading.remove();
                    console.error('SYNRGY Assistant:', error);
                    addMessage('Tidak dapat terhubung ke SYNRGY Assistant. Silakan coba lagi.', 'bot');
                } finally {
                    sendButton.disabled = false;
                    input.focus();
                }
            }

            sendButton.addEventListener('click', sendMessage);

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    sendMessage();
                }
            });

            resetButton.addEventListener('click', async function () {
                resetButton.disabled = true;
                try {
                    const response = await fetch(resetUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    await parseJsonResponse(response);

                    messages.innerHTML = '';
                    addMessage('Percakapan baru dimulai. Ada yang bisa saya bantu?', 'bot');
                    input.focus();
                } catch (error) {
                    console.error('Reset SYNRGY Assistant:', error);
                    addMessage('Percakapan tidak dapat direset saat ini.', 'bot');
                } finally {
                    resetButton.disabled = false;
                }
            });
        });
    </script>

</body>
</html>