@php
    /*
    |--------------------------------------------------------------------------
    | DATA DASAR
    |--------------------------------------------------------------------------
    */
    $record = $coaching->atrRecord;

    /*
    |--------------------------------------------------------------------------
    | ATTACHMENT TANDA TANGAN
    |--------------------------------------------------------------------------
    | Mapping yang dipakai sistem saat ini:
    | EMPLOYEE_SIGNATURE = Tanda Tangan Karyawan
    | COACH_SIGNATURE    = Tanda Tangan Pimpinan
    |
    | Collection diambil ulang bila relation belum diload agar halaman print
    | tetap aman walaupun controller berubah.
    */
    $attachments = $coaching->relationLoaded('attachments')
        ? $coaching->attachments
        : $coaching->attachments()->get();

    $findAttachmentByTypes = function (array $types) use ($attachments) {
        $wanted = collect($types)
            ->map(fn ($type) => strtoupper(trim((string) $type)))
            ->all();

        return $attachments->first(function ($attachment) use ($wanted) {
            return in_array(
                strtoupper(trim((string) ($attachment->type ?? ''))),
                $wanted,
                true
            );
        });
    };

    $creatorSignature = $findAttachmentByTypes([
        'EMPLOYEE_SIGNATURE',
        'CREATOR_SIGNATURE',
        'PEMBUAT_SIGNATURE',
    ]);

    $leaderSignature = $findAttachmentByTypes([
        'COACH_SIGNATURE',
        'LEADER_SIGNATURE',
        'PIMPINAN_SIGNATURE',
    ]);

    /*
    |--------------------------------------------------------------------------
    | FILE -> DATA URI
    |--------------------------------------------------------------------------
    | Tanda tangan dibaca langsung dari storage Laravel dan logo dibaca dari
    | public/. Dengan data URI, gambar akan tetap ikut ketika browser memilih
    | "Print" / "Save as PDF".
    */
    $fileToDataUri = function (?string $absolutePath, ?string $mime = null) {
        if (! $absolutePath || ! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return null;
        }

        try {
            $binary = file_get_contents($absolutePath);

            if ($binary === false || $binary === '') {
                return null;
            }

            if (! $mime) {
                $detectedMime = function_exists('mime_content_type')
                    ? @mime_content_type($absolutePath)
                    : null;

                $mime = $detectedMime ?: 'image/png';
            }

            return 'data:' . $mime . ';base64,' . base64_encode($binary);
        } catch (\Throwable $e) {
            return null;
        }
    };

    /*
    |--------------------------------------------------------------------------
    | TANDA TANGAN -> DATA URI
    |--------------------------------------------------------------------------
    | Laravel 12 local disk biasanya berada di storage/app/private.
    | Kita tetap menyediakan beberapa fallback supaya aman terhadap perbedaan
    | konfigurasi filesystem.
    */
    $signatureToDataUri = function ($attachment) use ($fileToDataUri) {
        if (! $attachment) {
            return null;
        }

        $storedPath = trim((string) ($attachment->stored_path ?? ''));

        if ($storedPath === '') {
            return null;
        }

        $mime = trim((string) ($attachment->mime_type ?? '')) ?: 'image/png';

        try {
            $disk = \Illuminate\Support\Facades\Storage::disk('local');

            if ($disk->exists($storedPath)) {
                $binary = $disk->get($storedPath);

                if (is_string($binary) && $binary !== '') {
                    return 'data:' . $mime . ';base64,' . base64_encode($binary);
                }
            }
        } catch (\Throwable $e) {
            // Lanjut ke fallback absolute path di bawah.
        }

        $candidatePaths = [
            $storedPath,
            storage_path('app/' . ltrim($storedPath, '/\\')),
            storage_path('app/private/' . ltrim($storedPath, '/\\')),
            storage_path('app/public/' . ltrim($storedPath, '/\\')),
        ];

        foreach ($candidatePaths as $candidatePath) {
            $dataUri = $fileToDataUri($candidatePath, $mime);

            if ($dataUri) {
                return $dataUri;
            }
        }

        return null;
    };

    $creatorSignatureData = $signatureToDataUri($creatorSignature);
    $leaderSignatureData = $signatureToDataUri($leaderSignature);

    /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    | Sesuai struktur project:
    | public/assets/images/logo_ppa.png
    | public/assets/images/logo_safety.png
    */
    $ppaLogo = $fileToDataUri(
        public_path('assets/images/logo_ppa.png'),
        'image/png'
    );

    $safetyLogo = $fileToDataUri(
        public_path('assets/images/logo_safety.png'),
        'image/png'
    );

    /*
    |--------------------------------------------------------------------------
    | DATA AUDIT SISTEM
    |--------------------------------------------------------------------------
    */
    $histories = $coaching->relationLoaded('histories')
        ? $coaching->histories
        : $coaching->histories()->with('actor')->get();

    $systemDocumentNumber = trim(
        (string) ($coaching->system_document_number ?? '')
    );

    if ($systemDocumentNumber === '') {
        $reference = $coaching->completed_at
            ?: $coaching->created_at
            ?: now();

        $systemDocumentNumber = 'PPA-ATR-CC-'
            . $reference->format('Ym')
            . '-'
            . str_pad(
                (string) $coaching->id,
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    $picRosterName = trim(
        (string) ($coaching->created_by_name ?? '')
    ) ?: '-';

    $leaderName = trim(
        (string) ($coaching->leader_name ?? '')
    ) ?: $picRosterName;

@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Coaching & Counseling - {{ $record->nrp }}
    </title>

    <style>
        @page {
            size: 210mm 297mm;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-toolbar {
            position: fixed;
            z-index: 999;
            top: 14px;
            right: 14px;
        }

        .print-btn {
            border: 0;
            border-radius: 7px;
            padding: 9px 14px;
            color: #fff;
            background: #1677ff;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .meta {
            width: 194mm;
            max-width: calc(100vw - 24px);
            margin: 0 auto 6px;
            color: #555;
            font-size: 9px;
        }

        .sheet {
            width: 194mm;
            max-width: calc(100vw - 24px);
            margin: 0 auto;
            border: 2px solid #111;
            background: #fff;
        }

        /* =====================================================
           HEADER FORM
           ===================================================== */

        .header {
            display: grid;
            grid-template-columns: 31mm minmax(0, 1fr) 31mm;
            min-height: 31mm;
            border-bottom: 2px solid #111;
        }

        .logo-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5mm;
        }

        .logo-cell:first-child {
            border-right: 1.5px solid #111;
        }

        .logo-cell:last-child {
            border-left: 1.5px solid #111;
        }

        .logo-ppa {
            display: block;
            width: auto;
            max-width: 24mm;
            max-height: 25mm;
            object-fit: contain;
        }

        .logo-safety {
            display: block;
            width: auto;
            max-width: 25mm;
            max-height: 25mm;
            object-fit: contain;
        }

        .logo-fallback {
            font-size: 9px;
            font-weight: 800;
            text-align: center;
        }

        .title {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5mm 3mm;
            text-align: center;
        }

        .title-company {
            margin-bottom: 3mm;
            font-size: 15px;
            font-weight: 900;
        }

        .title-form {
            font-size: 22px;
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: .15px;
        }

        /* =====================================================
           IDENTITAS
           ===================================================== */

        .identity-row {
            display: grid;
            grid-template-columns: 41mm minmax(0, 1fr);
            min-height: 9mm;
            border-bottom: 1.5px solid #111;
        }

        .identity-label,
        .identity-value {
            display: flex;
            align-items: center;
            padding: 2mm 3mm;
        }

        .identity-label {
            border-right: 1.5px solid #111;
            font-weight: 800;
        }

        .identity-value {
            font-weight: 600;
        }

        /* =====================================================
           MATERI + TANDA TANGAN KARYAWAN
           ===================================================== */

        .section-head {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            background: #e4e4e4;
            border-bottom: 1.5px solid #111;
            font-weight: 900;
            text-align: center;
        }

        .section-head > div {
            padding: 2.5mm;
        }

        .section-head > div:first-child {
            border-right: 1.5px solid #111;
        }

        .material-signature {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            min-height: 39mm;
            border-bottom: 1.5px solid #111;
        }

        .materials {
            border-right: 1.5px solid #111;
        }

        .material-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 17mm;
            min-height: 12.8mm;
            border-bottom: 1px solid #777;
        }

        .material-row:last-child {
            border-bottom: 0;
        }

        .material-name,
        .material-check {
            display: flex;
            align-items: center;
            padding: 2mm 3mm;
        }

        .material-name {
            font-weight: 700;
        }

        .material-check {
            justify-content: center;
            border-left: 1px solid #777;
        }

        .checkbox {
            display: inline-flex;
            width: 6mm;
            height: 6mm;
            align-items: center;
            justify-content: center;
            border: 1.5px solid #111;
            font-size: 15px;
            font-weight: 900;
            line-height: 1;
        }

        .signature-area {
            display: flex;
            min-height: 39mm;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 2.5mm 4mm 2mm;
            text-align: center;
        }

        .signature-label {
            font-weight: 800;
        }

        .signature-image-wrap {
            display: flex;
            width: 100%;
            min-height: 24mm;
            align-items: center;
            justify-content: center;
        }

        .signature-image {
            display: block;
            width: auto;
            max-width: 58mm;
            max-height: 25mm;
            object-fit: contain;
        }

        .signature-line {
            width: 50mm;
            border-top: 1px solid #111;
        }

        /* =====================================================
           KETERANGAN
           ===================================================== */

        .notes {
            min-height: 64mm;
            padding: 3mm;
            border-bottom: 1.5px solid #111;
            white-space: pre-wrap;
        }

        .notes-title {
            margin-bottom: 3mm;
            font-weight: 900;
        }

        /* =====================================================
           DIBUAT OLEH / TANDA TANGAN PIMPINAN
           ===================================================== */

        .made-head {
            padding: 2.5mm;
            border-bottom: 1.5px solid #111;
            background: #e4e4e4;
            text-align: center;
            font-weight: 900;
        }

        .leader-signature-row {
            display: grid;
            grid-template-columns: 41mm minmax(0, 1fr);
            min-height: 31mm;
            border-bottom: 1.5px solid #111;
        }

        .leader-signature-label {
            display: flex;
            align-items: flex-start;
            padding: 3mm;
            border-right: 1.5px solid #111;
            font-weight: 800;
        }

        .leader-signature-content {
            display: flex;
            min-height: 31mm;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2mm;
            padding: 2mm 4mm;
        }

        .leader-signature-image {
            display: block;
            width: auto;
            max-width: 60mm;
            max-height: 24mm;
            object-fit: contain;
        }

        .footer-name {
            display: grid;
            grid-template-columns: 41mm minmax(0, 1fr);
            min-height: 9mm;
            border-bottom: 1.5px solid #111;
        }

        .footer-name > div {
            display: flex;
            align-items: center;
            padding: 2mm 3mm;
        }

        .footer-name > div:first-child {
            border-right: 1.5px solid #111;
            font-weight: 800;
        }

        .document-number {
            min-height: 8mm;
            padding: 2mm 3mm;
            text-align: right;
            font-size: 9px;
            font-weight: 700;
        }


        /* =====================================================
           INFORMASI SISTEM / AUDIT TRAIL
           ===================================================== */

        .system-audit {
            width: 194mm;
            max-width: calc(100vw - 24px);
            margin: 10mm auto 0;
            border: 1.5px solid #111;
            background: #fff;
        }

        .system-audit-title {
            padding: 3mm;
            border-bottom: 1.5px solid #111;
            background: #e8eef6;
            text-align: center;
            font-size: 13px;
            font-weight: 900;
        }

        .system-audit-grid {
            display: grid;
            grid-template-columns: 45mm minmax(0, 1fr);
        }

        .system-audit-label,
        .system-audit-value {
            min-height: 8mm;
            padding: 2mm 3mm;
            border-bottom: 1px solid #aaa;
        }

        .system-audit-label {
            border-right: 1px solid #aaa;
            background: #f7f9fc;
            font-weight: 800;
        }

        .system-audit-value {
            font-weight: 600;
        }

        .audit-history-title {
            padding: 2.5mm 3mm;
            border-top: 1.5px solid #111;
            border-bottom: 1px solid #111;
            background: #e4e4e4;
            font-weight: 900;
        }

        .audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .audit-table th,
        .audit-table td {
            padding: 2mm;
            border: 1px solid #aaa;
            vertical-align: top;
            text-align: left;
        }

        .audit-table th {
            background: #f3f5f8;
            font-weight: 900;
        }

        .cancel-audit {
            margin: 3mm;
            padding: 3mm;
            border: 1.5px solid #d22f42;
            background: #fff0f2;
        }

        .cancel-audit strong {
            display: block;
            margin-bottom: 1mm;
        }

        @media print {
            html,
            body {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .print-toolbar,
            .meta {
                display: none !important;
            }

            /*
             * Area cetak A4 setelah margin @page 8mm:
             * 210 - 16 = 194mm
             * 297 - 16 = 281mm
             */

            /* PAGE 1 — Form resmi Coaching & Counseling */
            .sheet {
                width: 194mm;
                min-height: 281mm;
                max-width: none;
                margin: 0 auto;
                break-after: page;
                page-break-after: always;
                break-inside: avoid-page;
                page-break-inside: avoid;
            }

            /* PAGE 2 — Informasi Sistem & Audit Trail */
            .system-audit {
                width: 194mm;
                min-height: 281mm;
                max-width: none;
                margin: 0 auto;
                break-before: page;
                page-break-before: always;
                break-inside: avoid-page;
                page-break-inside: avoid;
            }

            .header,
            .identity-row,
            .section-head,
            .material-signature,
            .made-head,
            .leader-signature-row,
            .footer-name,
            .document-number,
            .system-audit-title,
            .system-audit-grid,
            .audit-history-title,
            .audit-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        @media screen and (max-width: 760px) {
            .sheet,
            .meta {
                width: calc(100vw - 16px);
                max-width: none;
            }

            .header {
                grid-template-columns: 25% 50% 25%;
            }

            .title-company {
                font-size: 11px;
            }

            .title-form {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

    <div class="print-toolbar">
        <button
            type="button"
            class="print-btn"
            onclick="window.print()"
        >
            CETAK / SIMPAN PDF
        </button>
    </div>

    <div class="meta">
        Dokumen sistem SYNRGYPRO ·
        Dibuat {{ now()->format('d M Y H:i') }}
    </div>

    <div class="sheet">

        {{-- HEADER --}}
        <div class="header">

            <div class="logo-cell">
                @if($ppaLogo)
                    <img
                        class="logo-ppa"
                        src="{{ $ppaLogo }}"
                        alt="Logo PT Putra Perkasa Abadi"
                    >
                @else
                    <div class="logo-fallback">
                        PT. PPA
                    </div>
                @endif
            </div>

            <div class="title">
                <div class="title-company">
                    PT PUTRA PERKASA ABADI
                </div>

                <div class="title-form">
                    COACHING &amp; COUNSELING
                </div>
            </div>

            <div class="logo-cell">
                @if($safetyLogo)
                    <img
                        class="logo-safety"
                        src="{{ $safetyLogo }}"
                        alt="Logo Safety"
                    >
                @else
                    <div class="logo-fallback">
                        SAFETY
                    </div>
                @endif
            </div>

        </div>

        {{-- IDENTITAS --}}
        <div class="identity-row">
            <div class="identity-label">NAMA KARYAWAN</div>
            <div class="identity-value">
                : {{ $record->employee_name }}
            </div>
        </div>

        <div class="identity-row">
            <div class="identity-label">NRP</div>
            <div class="identity-value">
                : {{ $record->nrp }}
            </div>
        </div>

        <div class="identity-row">
            <div class="identity-label">JABATAN</div>
            <div class="identity-value">
                : {{ $record->job_title ?: '-' }}
                @if(!empty($record->position))
                    / {{ $record->position }}
                @endif
            </div>
        </div>

        <div class="identity-row">
            <div class="identity-label">LOKASI</div>
            <div class="identity-value">
                : {{ $coaching->location ?: '-' }}
            </div>
        </div>

        <div class="identity-row">
            <div class="identity-label">TANGGAL / SHIFT</div>
            <div class="identity-value">
                : {{ $coaching->coaching_date?->format('d-m-Y') ?: '-' }}
                / {{ $coaching->shift ?: '-' }}
            </div>
        </div>

        <div class="identity-row">
            <div class="identity-label">WAKTU PERTEMUAN</div>
            <div class="identity-value">
                :
                {{ $coaching->coaching_time
                    ? substr((string) $coaching->coaching_time, 0, 5)
                    : '-'
                }}
            </div>
        </div>

        {{-- MATERI + TANDA TANGAN KARYAWAN --}}
        <div class="section-head">
            <div>MATERI</div>
            <div>TANDA TANGAN</div>
        </div>

        <div class="material-signature">

            <div class="materials">

                <div class="material-row">
                    <div class="material-name">PRIBADI</div>
                    <div class="material-check">
                        <span class="checkbox">
                            {{ $coaching->material_personal ? '✓' : '' }}
                        </span>
                    </div>
                </div>

                <div class="material-row">
                    <div class="material-name">KELUARGA</div>
                    <div class="material-check">
                        <span class="checkbox">
                            {{ $coaching->material_family ? '✓' : '' }}
                        </span>
                    </div>
                </div>

                <div class="material-row">
                    <div class="material-name">PEKERJAAN</div>
                    <div class="material-check">
                        <span class="checkbox">
                            {{ $coaching->material_work ? '✓' : '' }}
                        </span>
                    </div>
                </div>

            </div>

            <div class="signature-area">

                <div class="signature-label">
                    Tanda Tangan Karyawan
                </div>

                <div class="signature-image-wrap">
                    @if($creatorSignatureData)
                        <img
                            class="signature-image"
                            src="{{ $creatorSignatureData }}"
                            alt="Tanda Tangan Karyawan"
                        >
                    @endif
                </div>

                <div class="signature-line"></div>

            </div>
        </div>

        {{-- KETERANGAN --}}
        <div class="notes">
            <div class="notes-title">
                KETERANGAN :
            </div>

            {{ $coaching->notes }}
        </div>

        {{-- DIBUAT OLEH --}}
        <div class="made-head">
            DIBUAT OLEH
        </div>

        <div class="leader-signature-row">

            <div class="leader-signature-label">
                TANDA TANGAN PIMPINAN
            </div>

            <div class="leader-signature-content">

                @if($leaderSignatureData)
                    <img
                        class="leader-signature-image"
                        src="{{ $leaderSignatureData }}"
                        alt="Tanda Tangan Pimpinan"
                    >
                @endif

                <div class="signature-line"></div>

            </div>
        </div>

        <div class="footer-name">
            <div>NAMA</div>
            <div>
                : {{ $leaderName }}
            </div>
        </div>

        <div class="document-number" style="display:flex;justify-content:space-between;gap:10px;">
            <span>
                NO DOKUMENTASI :
                {{ $systemDocumentNumber }}
            </span>
            <span>
                NO FORM :
                {{ $coaching->document_number }}
            </span>
        </div>

    </div>


    <section class="system-audit">
        <div class="system-audit-title">
            INFORMASI SISTEM &amp; AUDIT TRAIL — SYNRGYPRO
        </div>

        <div class="system-audit-grid">
            <div class="system-audit-label">No. Dokumentasi</div>
            <div class="system-audit-value">{{ $systemDocumentNumber }}</div>

            <div class="system-audit-label">No. Form Resmi</div>
            <div class="system-audit-value">{{ $coaching->document_number ?: '-' }}</div>

            <div class="system-audit-label">Status Dokumen</div>
            <div class="system-audit-value">
                {{ $coaching->status === 'CANCELLED'
                    ? 'PERLU ULANG / CANCELLED'
                    : 'SELESAI / COMPLETED' }}
            </div>

            <div class="system-audit-label">PIC Roster Otomatis</div>
            <div class="system-audit-value">{{ $picRosterName }}</div>

            <div class="system-audit-label">Nama Pimpinan</div>
            <div class="system-audit-value">{{ $leaderName }}</div>

            <div class="system-audit-label">Waktu Selesai</div>
            <div class="system-audit-value">
                {{ $coaching->completed_at?->locale('id')->translatedFormat('d F Y H:i') ?? '-' }}
            </div>

            <div class="system-audit-label">Operator Sistem</div>
            <div class="system-audit-value">
                {{ $coaching->creator?->name
                    ?? $coaching->creator?->email
                    ?? '-' }}
            </div>
        </div>

        @if ($coaching->status === 'CANCELLED')
            <div class="cancel-audit">
                <strong>ALASAN PEMBATALAN / PERLU ULANG</strong>
                {{ $coaching->cancellation_reason ?: '-' }}

                <br><br>

                <strong>WAKTU PEMBATALAN</strong>
                {{ $coaching->cancelled_at?->locale('id')->translatedFormat('d F Y H:i') ?? '-' }}

                <br><br>

                <strong>DIBATALKAN OLEH</strong>
                {{ $coaching->canceller?->name
                    ?? $coaching->canceller?->email
                    ?? '-' }}
            </div>
        @endif

        <div class="audit-history-title">
            RIWAYAT PERUBAHAN
        </div>

        <table class="audit-table">
            <thead>
                <tr>
                    <th style="width:18%;">Waktu</th>
                    <th style="width:15%;">Aksi</th>
                    <th style="width:18%;">Status</th>
                    <th style="width:20%;">Oleh</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($histories as $history)
                    <tr>
                        <td>
                            {{ $history->created_at?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}
                        </td>
                        <td>{{ $history->action }}</td>
                        <td>
                            {{ $history->from_status ?: '-' }}
                            →
                            {{ $history->to_status ?: '-' }}
                        </td>
                        <td>
                            {{ $history->actor?->name
                                ?? $history->actor?->email
                                ?? $history->actor_name
                                ?? '-' }}
                        </td>
                        <td>{{ $history->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Belum ada riwayat perubahan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

</body>
</html>