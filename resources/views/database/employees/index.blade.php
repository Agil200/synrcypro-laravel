@php
    $syncStatus = (string) (
        $syncMeta['status'] ?? 'error'
    );

    $syncError = trim(
        (string) (
            $syncMeta['error'] ?? ''
        )
    );

    $syncedAt = null;

    if (!empty($syncMeta['synced_at'])) {
        try {
            $syncedAt = \Illuminate\Support\Carbon::parse(
                $syncMeta['synced_at']
            )
                ->timezone(
                    config(
                        'app.timezone',
                        'Asia/Jakarta'
                    )
                )
                ->translatedFormat(
                    'd M Y, H:i'
                );
        } catch (\Throwable) {
            $syncedAt = (string) $syncMeta['synced_at'];
        }
    }

    $statusLabel = match ($syncStatus) {
        'synced' => 'Baru Disinkronkan',
        'cached' => 'Menggunakan Cache',
        'stale' => 'Menggunakan Backup Terakhir',
        default => 'Data Belum Tersedia',
    };

    $statusClass = match ($syncStatus) {
        'synced', 'cached' => 'success',
        'stale' => 'warning',
        default => 'danger',
    };

    $fallbackSource = match (
        (string) (
            $syncMeta['fallback_source'] ?? ''
        )
    ) {
        'storage' => 'backup lokal tahan lama',
        'cache' => 'backup cache',
        default => 'backup terakhir',
    };

    $hasUsableData =
        (int) (
            $syncMeta['mapped_rows'] ?? 0
        ) > 0;
@endphp

<div class="db-page-title employee-page-head">
    <div>
        <h1>Database Karyawan</h1>
        <p>
            Data real MASTER_DATABASE dengan cache dan sinkronisasi.
        </p>
    </div>

    <div class="employee-head-actions">
        @if (
            ($googleConnected ?? false) &&
            in_array(
                $syncStatus,
                ['synced', 'cached'],
                true
            )
        )
            <span class="employee-connection connected">
                ● Google Terhubung
            </span>
        @elseif ($syncStatus === 'stale')
            <a
                href="{{ route('google.oauth.redirect') }}"
                class="employee-connection warning"
                title="Hubungkan ulang Google Sheets"
            >
                ⚠ CACHE AKTIF
            </a>
        @else
            <a
                href="{{ route('google.oauth.redirect') }}"
                class="employee-connection disconnected"
            >
                ● HUBUNGKAN ULANG GOOGLE
            </a>
        @endif

        @if (!empty($sourceUrl))
            <a
                href="{{ $sourceUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="db-button dark"
            >
                SOURCE DATA
            </a>
        @endif

        <a
            href="{{ route(
                'database.employees.mapping-diagnostics'
            ) }}"
            class="db-button secondary"
        >
            CEK MAPPING
        </a>

        <form
            method="POST"
            action="{{ route('database.employees.sync') }}"
            id="employeeSyncForm"
        >
            @csrf

            <button
                type="submit"
                class="db-button employee-sync-button"
                id="employeeSyncButton"
            >
                <span id="employeeSyncButtonText">
                    ↻ SINKRONKAN DATA
                </span>
            </button>
        </form>
    </div>
</div>

@if (session('success'))
    <div class="employee-alert success">
        {{ session('success') }}
    </div>
@endif

@if (session('warning'))
    <div class="employee-alert warning">
        {{ session('warning') }}
    </div>
@endif

@if (session('error'))
    <div class="employee-alert danger">
        {{ session('error') }}
    </div>
@endif

<div class="employee-sticky-zone" id="employeeStickyZone">
<section class="employee-sync-panel {{ $statusClass }}">
    <div class="employee-sync-main">
        <span class="employee-sync-dot"></span>

        <div>
            <strong>{{ $statusLabel }}</strong>

            <small>
                @if ($syncedAt)
                    Terakhir sinkron:
                    {{ $syncedAt }} WIB
                @else
                    Belum ada sinkronisasi berhasil
                @endif
            </small>
        </div>
    </div>

    <div class="employee-sync-meta">
        <span>
            Data:
            <strong>
                {{ number_format(
                    (int) (
                        $syncMeta['mapped_rows'] ?? 0
                    )
                ) }}
            </strong>
        </span>

        <span>
            Duplikat NRP:
            <strong>
                {{ number_format(
                    (int) (
                        $syncMeta['duplicate_rows'] ?? 0
                    )
                ) }}
            </strong>
        </span>

        <span>
            Range:
            <strong>
                {{ $syncMeta['range'] ?? '-' }}
            </strong>
        </span>
    </div>

    @if ($syncStatus === 'stale')
        <p class="employee-sync-error">
            Google Sheets sedang tidak dapat diakses:
            <strong>{{ $syncError }}</strong>.

            Sistem tetap menampilkan
            <strong>
                {{ number_format(
                    (int) (
                        $syncMeta['mapped_rows'] ?? 0
                    )
                ) }}
                karyawan
            </strong>
            dari {{ $fallbackSource }}.
        </p>
    @elseif ($syncError !== '')
        <p class="employee-sync-error">
            {{ $syncError }}

            @unless ($hasUsableData)
                Backup belum tersedia. Hubungkan ulang Google,
                lalu lakukan sinkronisasi satu kali.
            @endunless
        </p>
    @endif
</section>

@if (!empty($syncMeta['missing_fields']))
    @php
        $missingFieldLabels = [
            'jabatan' => 'Jabatan/Posisi',
            'departemen' => 'Departemen',
            'perusahaan' => 'Perusahaan',
            'site' => 'Site',
            'kamar' => 'Kamar',
            'gedung_kamar' => 'Gedung/Kamar Gabungan',
        ];

        $missingLabels = collect(
            $syncMeta['missing_fields']
        )
            ->map(
                fn ($field) =>
                    $missingFieldLabels[$field] ??
                    strtoupper(
                        str_replace(
                            '_',
                            ' ',
                            (string) $field
                        )
                    )
            )
            ->implode(', ');
    @endphp

    <div class="employee-mapping-warning">
        <strong>Kolom belum tersedia pada MASTER_DATABASE:</strong>
        {{ $missingLabels }}.

        Nilai tersebut ditampilkan sebagai tanda “-” sampai
        tersedia dari sumber lain atau ditambahkan ke spreadsheet.
    </div>
@endif

<form
    method="GET"
    class="db-panel employee-filter-grid"
>
    <div class="db-field">
        <label for="employeeSearch">
            Nama / NRP / Jabatan
        </label>

        <input
            id="employeeSearch"
            name="search"
            type="search"
            class="db-input"
            value="{{ $search ?? '' }}"
            placeholder="Cari nama, NRP, atau jabatan…"
        >
    </div>

    <div class="db-field">
        <label for="residence">
            Tempat Tinggal
        </label>

        <select
            id="residence"
            name="residence"
            class="db-select"
        >
            <option value="all">
                Semua Status
            </option>

            <option
                value="mess"
                @selected(
                    ($residence ?? 'all') === 'mess'
                )
            >
                Mess
            </option>

            <option
                value="non-mess"
                @selected(
                    ($residence ?? 'all') === 'non-mess'
                )
            >
                Non Mess
            </option>
        </select>
    </div>

    <div class="db-field">
        <label for="perPage">
            Data per Halaman
        </label>

        <select
            id="perPage"
            name="per_page"
            class="db-select"
        >
            @foreach ([25, 50, 100] as $size)
                <option
                    value="{{ $size }}"
                    @selected(
                        ($perPage ?? 25) === $size
                    )
                >
                    {{ $size }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="db-actions">
        <button
            class="db-button"
            type="submit"
        >
            CARI
        </button>

        <a
            href="{{ route('database.employees') }}"
            class="db-button secondary"
        >
            RESET
        </a>
    </div>
</form>

<div class="employee-kpi-grid">
    <article class="db-kpi-card">
        <span class="db-kpi-icon">👥</span>
        <div>
            <small>Total Karyawan</small>
            <strong>
                {{ number_format(
                    $employeeStats['total'] ?? 0
                ) }}
            </strong>
        </div>
    </article>

    <article class="db-kpi-card">
        <span class="db-kpi-icon">🏠</span>
        <div>
            <small>Tinggal di Mess</small>
            <strong>
                {{ number_format(
                    $employeeStats['mess'] ?? 0
                ) }}
            </strong>
        </div>
    </article>

    <article class="db-kpi-card">
        <span class="db-kpi-icon">🚶</span>
        <div>
            <small>Tinggal Non Mess</small>
            <strong>
                {{ number_format(
                    $employeeStats['non_mess'] ?? 0
                ) }}
            </strong>
        </div>
    </article>

    <article class="db-kpi-card">
        <span class="db-kpi-icon">?</span>
        <div>
            <small>Status Belum Lengkap</small>
            <strong>
                {{ number_format(
                    $employeeStats['unknown'] ?? 0
                ) }}
            </strong>
        </div>
    </article>
</div>
</div>

<section class="db-table-card employee-table-card">
    <div class="db-card-header">
        <div>
            <h2>Data Ringkasan Karyawan</h2>
            <small>
                Menampilkan
                {{ number_format($employees->firstItem() ?? 0) }}
                –
                {{ number_format($employees->lastItem() ?? 0) }}
                dari
                {{ number_format($employees->total()) }}
                hasil
            </small>
        </div>
    </div>

    <div class="db-table-wrap employee-table-scroll">
        <table class="db-table employee-real-table">
            <thead>
                <tr>
                    <th>NRP</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Status Karyawan</th>
                    <th>Status Tinggal</th>
                    <th>Gedung/Kamar</th>
                    <th>No. HP</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($employees as $employee)
                    @php
                        $phoneRaw = (string) (
                            $employee['no_hp'] ?? ''
                        );

                        $phoneDigits = preg_replace(
                            '/\D+/',
                            '',
                            $phoneRaw
                        ) ?? '';

                        if (
                            str_starts_with(
                                $phoneDigits,
                                '0'
                            )
                        ) {
                            $phoneDigits =
                                '62' .
                                substr(
                                    $phoneDigits,
                                    1
                                );
                        }

                        $employmentStatus =
                            strtoupper(
                                trim(
                                    (string) (
                                        $employee[
                                            'status_karyawan'
                                        ] ?? '-'
                                    )
                                )
                            );

                        $employmentBadge =
                            in_array(
                                $employmentStatus,
                                [
                                    'AKTIF',
                                    'ACTIVE',
                                ],
                                true
                            )
                                ? 'green'
                                : 'orange';


                        $completionPercentage = max(
                            0,
                            min(
                                100,
                                (int) (
                                    $employee[
                                        'completion_percentage'
                                    ] ?? 0
                                )
                            )
                        );

                        $isComplete = (bool) (
                            $employee['is_complete'] ?? false
                        );

                        $completenessStatus = (string) (
                            $employee['kelengkapan_status'] ??
                            (
                                $isComplete
                                    ? 'LENGKAP'
                                    : 'BELUM LENGKAP'
                            )
                        );

                        $missingFieldLabels = array_values(
                            array_filter(
                                (array) (
                                    $employee[
                                        'missing_field_labels'
                                    ] ?? []
                                ),
                                fn ($value): bool =>
                                    trim((string) $value) !== ''
                            )
                        );

                        $whatsappUrl = trim(
                            (string) (
                                $employee['whatsapp_url'] ?? ''
                            )
                        );

                        if (
                            $whatsappUrl === '' &&
                            $phoneDigits !== ''
                        ) {
                            $whatsappUrl =
                                'https://wa.me/' .
                                $phoneDigits;
                        }

                        $emailUrl = trim(
                            (string) (
                                $employee['email_url'] ?? ''
                            )
                        );

                        if (
                            $emailUrl === '' &&
                            !empty($employee['email']) &&
                            $employee['email'] !== '-'
                        ) {
                            $emailUrl =
                                'mailto:' .
                                $employee['email'];
                        }

                        $employeeModalData = [
                            'nrp' =>
                                $employee['nrp'] ?? '-',
                            'nama' =>
                                $employee['nama'] ?? '-',
                            'jabatan' =>
                                $employee['jabatan'] ?? '-',
                            'departemen' =>
                                $employee['departemen'] ?? '-',
                            'perusahaan' =>
                                $employee['perusahaan'] ?? '-',
                            'site' =>
                                $employee['site'] ?? '-',
                            'tanggalLahir' =>
                                $employee['tanggal_lahir'] ?? '-',
                            'statusKaryawan' =>
                                $employmentStatus,
                            'statusTinggal' =>
                                $employee['status_tinggal'] ?? '-',
                            'gedung' =>
                                $employee['gedung'] ?? '-',
                            'kamar' =>
                                $employee['kamar'] ?? '-',
                            'gedungKamar' =>
                                $employee['gedung_kamar'] ?? (
                                    ($employee['gedung'] ?? '-') .
                                    ' / ' .
                                    ($employee['kamar'] ?? '-')
                                ),
                            'noHp' =>
                                $employee['no_hp'] ?? '-',
                            'email' =>
                                $employee['email'] ?? '-',
                            'fotoUrl' =>
                                $employee['foto_url'] ?? null,
                            'fotoOpenUrl' =>
                                $employee[
                                    'foto_open_url'
                                ] ?? (
                                    $employee['foto_url'] ?? null
                                ),
                            'fotoPreviewUrl' =>
                                $employee[
                                    'foto_preview_url'
                                ] ?? null,
                            'fotoPreviewCandidates' =>
                                array_values(
                                    array_filter(
                                        (array) (
                                            $employee[
                                                'foto_preview_candidates'
                                            ] ?? []
                                        ),
                                        fn ($value): bool =>
                                            trim((string) $value) !== ''
                                    )
                                ),
                            'fotoAvailable' =>
                                (bool) (
                                    $employee[
                                        'foto_available'
                                    ] ?? false
                                ),
                            'fotoSourceType' =>
                                $employee[
                                    'foto_source_type'
                                ] ?? 'missing',
                            'whatsappUrl' =>
                                $whatsappUrl !== ''
                                    ? $whatsappUrl
                                    : null,
                            'emailUrl' =>
                                $emailUrl !== ''
                                    ? $emailUrl
                                    : null,
                            'isComplete' => $isComplete,
                            'completenessStatus' =>
                                $completenessStatus,
                            'completionPercentage' =>
                                $completionPercentage,
                            'missingFieldLabels' =>
                                $missingFieldLabels,
                        ];

                        $employeeModalJson = (string) json_encode(
                            $employeeModalData,
                            JSON_HEX_APOS |
                            JSON_HEX_QUOT |
                            JSON_HEX_AMP |
                            JSON_HEX_TAG |
                            JSON_UNESCAPED_UNICODE |
                            JSON_UNESCAPED_SLASHES |
                            JSON_INVALID_UTF8_SUBSTITUTE
                        );
                    @endphp

                    <tr>
                        <td>
                            {{ $employee['nrp'] ?? '-' }}
                        </td>

                        <td>
                            <strong>
                                {{ $employee['nama'] ?? '-' }}
                            </strong>
                        </td>

                        <td>
                            {{ $employee['jabatan'] ?? '-' }}
                        </td>

                        <td>
                            <span
                                class="db-badge {{ $employmentBadge }}"
                            >
                                {{ $employmentStatus }}
                            </span>
                        </td>

                        <td>
                            {{ $employee['status_tinggal'] ?? '-' }}
                        </td>

                        <td>
                            {{ $employee['gedung'] ?? '-' }}
                            /
                            {{ $employee['kamar'] ?? '-' }}
                        </td>

                        <td>
                            {{ $employee['no_hp'] ?? '-' }}
                        </td>

                        <td>
                            {{ $employee['email'] ?? '-' }}
                        </td>

                        <td>
                            <button
                                type="button"
                                class="db-button employee-detail-trigger"
                                data-employee="{{ $employeeModalJson }}"
                                aria-haspopup="dialog"
                                aria-controls="employeeDetailModal"
                            >
                                DETAIL
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="9"
                            class="db-empty"
                        >
                            @if ($syncError !== '')
                                Data belum dapat dimuat:
                                {{ $syncError }}
                            @else
                                Data tidak ditemukan.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($employees->hasPages())
        <div class="employee-pagination">
            <div>
                Halaman
                <strong>{{ $employees->currentPage() }}</strong>
                dari
                <strong>{{ $employees->lastPage() }}</strong>
            </div>

            <div class="employee-pagination-actions">
                @if ($employees->onFirstPage())
                    <span class="employee-page-button disabled">
                        ‹ Sebelumnya
                    </span>
                @else
                    <a
                        href="{{ $employees->previousPageUrl() }}"
                        class="employee-page-button"
                    >
                        ‹ Sebelumnya
                    </a>
                @endif

                @if ($employees->hasMorePages())
                    <a
                        href="{{ $employees->nextPageUrl() }}"
                        class="employee-page-button"
                    >
                        Berikutnya ›
                    </a>
                @else
                    <span class="employee-page-button disabled">
                        Berikutnya ›
                    </span>
                @endif
            </div>
        </div>
    @endif
</section>

<div
    class="employee-modal"
    id="employeeDetailModal"
    hidden
>
    <div
        class="employee-modal-backdrop"
        data-employee-modal-close
    ></div>

    <section
        class="employee-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="employeeModalName"
        aria-describedby="employeeModalSummary"
        tabindex="-1"
    >
        <header class="employee-modal-header">
            <div>
                <span class="employee-modal-eyebrow">
                    DETAIL KARYAWAN
                </span>

                <h2 id="employeeModalName">-</h2>

                <p id="employeeModalSummary">
                    NRP: <strong id="employeeModalNrp">-</strong>
                </p>
            </div>

            <button
                type="button"
                class="employee-modal-close"
                data-employee-modal-close
                aria-label="Tutup detail karyawan"
            >
                ×
            </button>
        </header>

        <div class="employee-modal-body">
            <aside class="employee-modal-profile">
                <div
                    class="employee-photo-frame"
                    id="employeeModalPhotoFrame"
                    data-photo-state="missing"
                >
                    <img
                        id="employeeModalPhoto"
                        class="employee-photo-image"
                        src=""
                        alt="Pas foto karyawan"
                        decoding="async"
                        referrerpolicy="no-referrer"
                        hidden
                    >

                    <div
                        id="employeeModalPhotoLoader"
                        class="employee-photo-loader"
                        hidden
                    >
                        <span aria-hidden="true"></span>
                        <small>MEMUAT FOTO...</small>
                    </div>

                    <div
                        id="employeeModalPhotoPlaceholder"
                        class="employee-photo-placeholder"
                    >
                        <span id="employeeModalInitials">--</span>
                        <small id="employeeModalPhotoPlaceholderText">
                            FOTO BELUM TERSEDIA
                        </small>
                    </div>
                </div>

                <div
                    id="employeeModalPhotoNotice"
                    class="employee-photo-notice"
                    hidden
                >
                    <strong>Preview foto tidak dapat dimuat</strong>
                    <p>
                        Pastikan file Google Drive dapat diakses oleh
                        “Siapa saja yang memiliki link”.
                    </p>

                    <button
                        type="button"
                        id="employeeModalPhotoRetry"
                        class="employee-photo-retry"
                    >
                        COBA MUAT ULANG
                    </button>
                </div>

                <div class="employee-completeness-card">
                    <div class="employee-completeness-heading">
                        <span>Kelengkapan Data</span>

                        <strong
                            id="employeeModalCompletenessBadge"
                            class="employee-completeness-badge incomplete"
                        >
                            BELUM LENGKAP
                        </strong>
                    </div>

                    <div class="employee-progress-track">
                        <span
                            id="employeeModalProgressBar"
                            class="employee-progress-bar"
                        ></span>
                    </div>

                    <p>
                        <strong id="employeeModalPercentage">0%</strong>
                        data utama telah terisi.
                    </p>
                </div>

                <div
                    id="employeeModalMissingBox"
                    class="employee-missing-box"
                >
                    <strong>Data yang belum tersedia</strong>
                    <p id="employeeModalMissingFields">-</p>
                </div>
            </aside>

            <div class="employee-modal-information">
                <div class="employee-information-section">
                    <h3>Informasi Pekerjaan</h3>

                    <dl class="employee-information-grid">
                        <div>
                            <dt>Jabatan</dt>
                            <dd id="employeeModalJabatan">-</dd>
                        </div>

                        <div>
                            <dt>Departemen</dt>
                            <dd id="employeeModalDepartemen">-</dd>
                        </div>

                        <div>
                            <dt>Perusahaan</dt>
                            <dd id="employeeModalPerusahaan">-</dd>
                        </div>

                        <div>
                            <dt>Site</dt>
                            <dd id="employeeModalSite">-</dd>
                        </div>

                        <div>
                            <dt>Status Karyawan</dt>
                            <dd id="employeeModalStatusKaryawan">-</dd>
                        </div>

                        <div>
                            <dt>Tanggal Lahir</dt>
                            <dd id="employeeModalTanggalLahir">-</dd>
                        </div>
                    </dl>
                </div>

                <div class="employee-information-section">
                    <h3>Tempat Tinggal & Kontak</h3>

                    <dl class="employee-information-grid">
                        <div>
                            <dt>Status Tinggal</dt>
                            <dd id="employeeModalStatusTinggal">-</dd>
                        </div>

                        <div>
                            <dt>Gedung/Kamar</dt>
                            <dd id="employeeModalGedungKamar">-</dd>
                        </div>

                        <div>
                            <dt>Nomor HP</dt>
                            <dd id="employeeModalNoHp">-</dd>
                        </div>

                        <div>
                            <dt>Email</dt>
                            <dd id="employeeModalEmail">-</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <footer class="employee-modal-footer">
            <a
                id="employeeModalPhotoLink"
                href="#"
                target="_blank"
                rel="noopener noreferrer"
                class="db-button secondary"
                hidden
            >
                BUKA FOTO
            </a>

            <a
                id="employeeModalWhatsapp"
                href="#"
                target="_blank"
                rel="noopener noreferrer"
                class="db-button green"
                hidden
            >
                WHATSAPP
            </a>

            <a
                id="employeeModalEmailAction"
                href="#"
                class="db-button dark"
                hidden
            >
                KIRIM EMAIL
            </a>

            <button
                type="button"
                class="db-button secondary"
                data-employee-modal-close
            >
                TUTUP
            </button>
        </footer>
    </section>
</div>

<style>
.employee-page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.employee-head-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
}

.employee-head-actions form {
    margin: 0;
}

.employee-connection {
    display: inline-flex;
    min-height: 35px;
    align-items: center;
    padding: 0 11px;
    border-radius: 8px;
    font-size: 9px;
    font-weight: 900;
}

.employee-connection.connected {
    color: #087a45;
    background: #dcfce7;
}

.employee-sync-button {
    background: #147df5;
}

.employee-alert,
.employee-sync-panel {
    margin-bottom: 10px;
    border: 1px solid;
    border-radius: 10px;
}

.employee-alert {
    padding: 10px 12px;
    font-size: 10px;
    font-weight: 800;
}

.employee-alert.success {
    border-color: #86efac;
    color: #087a45;
    background: #f0fdf4;
}

.employee-alert.danger {
    border-color: #fca5a5;
    color: #b91c1c;
    background: #fef2f2;
}

.employee-sync-panel {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 13px;
}

.employee-sync-panel.success {
    border-color: #86efac;
    background: #f0fdf4;
}

.employee-sync-panel.warning {
    border-color: #fcd34d;
    background: #fffbeb;
}

.employee-sync-panel.danger {
    border-color: #fca5a5;
    background: #fef2f2;
}

.employee-sync-main {
    display: flex;
    align-items: center;
    gap: 9px;
}

.employee-sync-main strong,
.employee-sync-main small {
    display: block;
}

.employee-sync-main strong {
    font-size: 11px;
}

.employee-sync-main small {
    margin-top: 2px;
    color: #64748b;
    font-size: 9px;
}

.employee-sync-dot {
    width: 10px;
    height: 10px;
    flex: 0 0 10px;
    border-radius: 50%;
    background: #16a34a;
}

.employee-sync-panel.warning .employee-sync-dot {
    background: #f59e0b;
}

.employee-sync-panel.danger .employee-sync-dot {
    background: #dc2626;
}

.employee-sync-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 13px;
    color: #64748b;
    font-size: 9px;
}

.employee-sync-meta strong {
    color: #172033;
}

.employee-sync-error {
    width: 100%;
    margin: 0;
    color: #b45309;
    font-size: 9px;
    font-weight: 700;
}

.employee-sync-panel.danger .employee-sync-error {
    color: #b91c1c;
}

.employee-filter-grid {
    display: grid;
    grid-template-columns:
        minmax(240px, 1fr)
        185px
        150px
        auto;
    gap: 9px;
    align-items: end;
}

.employee-kpi-grid {
    display: grid;
    grid-template-columns: repeat(
        4,
        minmax(0, 1fr)
    );
    gap: 10px;
    margin-bottom: 11px;
}

.employee-real-table {
    min-width: 1200px;
}

.employee-detail-trigger {
    white-space: nowrap;
}

body.employee-modal-open {
    overflow: hidden;
}

.employee-modal[hidden],
.employee-modal [hidden] {
    display: none !important;
}

.employee-modal {
    position: fixed;
    z-index: 1200;
    inset: 0;
    display: grid;
    place-items: center;
    padding: 18px;
}

.employee-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, .72);
    backdrop-filter: blur(3px);
}

.employee-modal-dialog {
    position: relative;
    z-index: 1;
    display: flex;
    width: min(940px, calc(100vw - 36px));
    max-height: min(88vh, 760px);
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, .35);
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 30px 90px rgba(15, 23, 42, .38);
}

.employee-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 22px;
    color: #ffffff;
    background:
        linear-gradient(
            110deg,
            #111827 0%,
            #273449 62%,
            #c95d2d 100%
        );
}

.employee-modal-eyebrow {
    display: block;
    margin-bottom: 5px;
    color: #bfdbfe;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: .16em;
}

.employee-modal-header h2 {
    margin: 0;
    font-size: clamp(20px, 2.6vw, 30px);
    line-height: 1.15;
}

.employee-modal-header p {
    margin: 6px 0 0;
    color: #dbeafe;
    font-size: 11px;
}

.employee-modal-close {
    display: inline-grid;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .42);
    border-radius: 50%;
    color: #ffffff;
    background: rgba(255, 255, 255, .12);
    cursor: pointer;
    font-size: 24px;
    line-height: 1;
}

.employee-modal-close:hover {
    background: rgba(255, 255, 255, .22);
}

.employee-modal-body {
    display: grid;
    grid-template-columns: 240px minmax(0, 1fr);
    gap: 20px;
    min-height: 0;
    padding: 20px 22px;
    overflow: auto;
}

.employee-modal-profile {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.employee-photo-frame {
    position: relative;
    min-height: 260px;
    overflow: hidden;
    border: 1px solid #dbe3ec;
    border-radius: 14px;
    background: #eef2f7;
}

.employee-photo-image {
    width: 100%;
    height: 260px;
    display: block;
    object-fit: cover;
    object-position: center top;
    background: #eef2f7;
}

.employee-photo-loader {
    display: grid;
    min-height: 260px;
    place-content: center;
    gap: 10px;
    color: #475569;
    text-align: center;
}

.employee-photo-loader span {
    width: 34px;
    height: 34px;
    margin: 0 auto;
    border: 4px solid #cbd5e1;
    border-top-color: #147df5;
    border-radius: 50%;
    animation: employee-photo-spin .75s linear infinite;
}

.employee-photo-loader small {
    font-size: 9px;
    font-weight: 900;
    letter-spacing: .08em;
}

@keyframes employee-photo-spin {
    to {
        transform: rotate(360deg);
    }
}

.employee-photo-placeholder {
    display: grid;
    min-height: 260px;
    place-content: center;
    gap: 8px;
    color: #64748b;
    text-align: center;
}

.employee-photo-placeholder span {
    display: grid;
    width: 84px;
    height: 84px;
    margin: 0 auto;
    place-items: center;
    border-radius: 50%;
    color: #ffffff;
    background: #23324b;
    font-size: 24px;
    font-weight: 900;
    letter-spacing: .05em;
}

.employee-photo-placeholder small {
    font-size: 9px;
    font-weight: 900;
}

.employee-photo-notice {
    padding: 10px 11px;
    border: 1px solid #fcd34d;
    border-radius: 11px;
    color: #92400e;
    background: #fffbeb;
}

.employee-photo-notice strong {
    display: block;
    font-size: 9px;
}

.employee-photo-notice p {
    margin: 5px 0 8px;
    font-size: 8px;
    line-height: 1.45;
}

.employee-photo-retry {
    padding: 6px 8px;
    border: 1px solid #f59e0b;
    border-radius: 7px;
    color: #92400e;
    background: #ffffff;
    cursor: pointer;
    font-size: 8px;
    font-weight: 900;
}

.employee-photo-retry:hover {
    background: #fef3c7;
}

.employee-completeness-card,
.employee-missing-box {
    border: 1px solid #dbe3ec;
    border-radius: 12px;
    background: #f8fafc;
}

.employee-completeness-card {
    padding: 12px;
}

.employee-completeness-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    color: #334155;
    font-size: 9px;
    font-weight: 800;
}

.employee-completeness-badge {
    padding: 5px 7px;
    border-radius: 999px;
    font-size: 8px;
    font-weight: 900;
}

.employee-completeness-badge.complete {
    color: #087a45;
    background: #dcfce7;
}

.employee-completeness-badge.incomplete {
    color: #b45309;
    background: #fef3c7;
}

.employee-progress-track {
    height: 8px;
    margin-top: 10px;
    overflow: hidden;
    border-radius: 999px;
    background: #e2e8f0;
}

.employee-progress-bar {
    display: block;
    width: 0;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #147df5, #16a34a);
    transition: width .24s ease;
}

.employee-completeness-card p {
    margin: 8px 0 0;
    color: #64748b;
    font-size: 9px;
}

.employee-completeness-card p strong {
    color: #172033;
}

.employee-missing-box {
    padding: 11px 12px;
    border-color: #fcd34d;
    color: #92400e;
    background: #fffbeb;
}

.employee-missing-box strong {
    display: block;
    font-size: 9px;
}

.employee-missing-box p {
    margin: 5px 0 0;
    font-size: 9px;
    line-height: 1.45;
}

.employee-modal-information {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 16px;
}

.employee-information-section {
    padding: 15px;
    border: 1px solid #dbe3ec;
    border-radius: 14px;
    background: #ffffff;
}

.employee-information-section h3 {
    margin: 0 0 12px;
    padding-bottom: 9px;
    border-bottom: 1px solid #e2e8f0;
    color: #172033;
    font-size: 12px;
}

.employee-information-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 13px 18px;
    margin: 0;
}

.employee-information-grid > div {
    min-width: 0;
}

.employee-information-grid dt {
    margin-bottom: 4px;
    color: #64748b;
    font-size: 9px;
    font-weight: 700;
}

.employee-information-grid dd {
    margin: 0;
    overflow-wrap: anywhere;
    color: #172033;
    font-size: 11px;
    font-weight: 900;
    line-height: 1.35;
}

.employee-modal-footer {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
    padding: 14px 22px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

@media (max-width: 760px) {
    .employee-modal {
        padding: 10px;
    }

    .employee-modal-dialog {
        width: calc(100vw - 20px);
        max-height: 94vh;
        border-radius: 13px;
    }

    .employee-modal-header,
    .employee-modal-body,
    .employee-modal-footer {
        padding-right: 15px;
        padding-left: 15px;
    }

    .employee-modal-body {
        grid-template-columns: 1fr;
    }

    .employee-photo-frame,
    .employee-photo-image,
    .employee-photo-placeholder {
        min-height: 220px;
        height: 220px;
    }

    .employee-information-grid {
        grid-template-columns: 1fr;
    }

    .employee-modal-footer .db-button {
        flex: 1 1 135px;
        justify-content: center;
    }
}

.employee-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 13px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 9px;
}

.employee-pagination-actions {
    display: flex;
    gap: 7px;
}

.employee-page-button {
    display: inline-flex;
    min-height: 31px;
    align-items: center;
    padding: 0 10px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    color: #172033;
    background: #fff;
    font-weight: 800;
    text-decoration: none;
}

.employee-page-button.disabled {
    cursor: not-allowed;
    opacity: .45;
}

@media (max-width: 1100px) {
    .employee-page-head {
        flex-direction: column;
    }

    .employee-head-actions {
        justify-content: flex-start;
    }

    .employee-filter-grid {
        grid-template-columns: 1fr 1fr;
    }

    .employee-kpi-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 680px) {
    .employee-filter-grid,
    .employee-kpi-grid {
        grid-template-columns: 1fr;
    }

    .employee-pagination {
        align-items: flex-start;
        flex-direction: column;
    }
}
.employee-sync-button:disabled {
    cursor: wait;
    opacity: .72;
}

.employee-sync-button.is-loading {
    pointer-events: none;
}


/*
|--------------------------------------------------------------------------
| Sticky Database Karyawan
|--------------------------------------------------------------------------
| Sticky zone menjaga status sinkron, filter, dan KPI tetap terlihat.
| Tabel memiliki scroll sendiri agar header dan identitas karyawan tidak
| bergerak saat daftar digulir.
*/

.employee-sticky-zone {
    position: sticky;
    z-index: 40;
    top: 0;
    margin: 0 -2px 10px;
    padding: 2px 2px 1px;
    background: var(--db-bg);
    box-shadow:
        0 10px 18px -18px rgba(15, 23, 42, .65);
}

.employee-sticky-zone .employee-kpi-grid {
    margin-bottom: 0;
}

.employee-table-card {
    overflow: visible;
}

.employee-table-scroll {
    position: relative;
    height: clamp(
        320px,
        calc(100vh - 420px),
        620px
    );
    overflow: auto;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
}

/* Header tabel tetap terlihat ketika isi tabel digulir. */
.employee-real-table thead th {
    position: sticky;
    z-index: 12;
    top: 0;
    background: #f8fafc;
    box-shadow:
        0 1px 0 #dce2e8,
        0 5px 12px rgba(15, 23, 42, .05);
}

/* NRP tetap terlihat saat tabel digeser ke kanan. */
.employee-real-table th:nth-child(1),
.employee-real-table td:nth-child(1) {
    position: sticky;
    left: 0;
    width: 92px;
    min-width: 92px;
    max-width: 92px;
}

/* Nama karyawan tetap terlihat saat tabel digeser ke kanan. */
.employee-real-table th:nth-child(2),
.employee-real-table td:nth-child(2) {
    position: sticky;
    left: 92px;
    width: 205px;
    min-width: 205px;
    max-width: 205px;
    box-shadow:
        1px 0 0 #e5e7eb,
        7px 0 12px -12px rgba(15, 23, 42, .65);
}

.employee-real-table tbody td:nth-child(1),
.employee-real-table tbody td:nth-child(2) {
    z-index: 6;
    background: #ffffff;
}

.employee-real-table thead th:nth-child(1),
.employee-real-table thead th:nth-child(2) {
    z-index: 18;
    background: #f8fafc;
}

.employee-real-table tbody tr:hover td {
    background: #f8fbff;
}

.employee-real-table tbody tr:hover td:nth-child(1),
.employee-real-table tbody tr:hover td:nth-child(2) {
    background: #f8fbff;
}

@media (max-width: 1100px) {
    .employee-table-scroll {
        height: clamp(
            300px,
            calc(100vh - 500px),
            520px
        );
    }
}

@media (max-width: 680px) {
    .employee-sticky-zone {
        position: static;
    }

    .employee-table-scroll {
        height: 55vh;
        min-height: 300px;
    }

    .employee-real-table th:nth-child(2),
    .employee-real-table td:nth-child(2) {
        left: 86px;
        width: 170px;
        min-width: 170px;
        max-width: 170px;
    }

    .employee-real-table th:nth-child(1),
    .employee-real-table td:nth-child(1) {
        width: 86px;
        min-width: 86px;
        max-width: 86px;
    }
}


.employee-mapping-warning {
    margin-bottom: 10px;
    padding: 10px 12px;
    border: 1px solid #fcd34d;
    border-radius: 9px;
    color: #92400e;
    background: #fffbeb;
    font-size: 9px;
    line-height: 1.5;
}

.employee-mapping-warning strong {
    color: #78350f;
}


.employee-alert.warning {
    border-color: #fcd34d;
    color: #92400e;
    background: #fffbeb;
}

.employee-connection.warning,
.employee-connection.disconnected {
    display: inline-flex;
    min-height: 34px;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
    border-radius: 8px;
    font-size: 9px;
    font-weight: 900;
    text-decoration: none;
    white-space: nowrap;
}

.employee-connection.warning {
    border: 1px solid #fcd34d;
    color: #92400e;
    background: #fffbeb;
}

.employee-connection.disconnected {
    border: 1px solid #fca5a5;
    color: #b91c1c;
    background: #fef2f2;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const syncForm =
        document.getElementById('employeeSyncForm');

    const syncButton =
        document.getElementById('employeeSyncButton');

    const syncButtonText =
        document.getElementById('employeeSyncButtonText');

    syncForm?.addEventListener('submit', function () {
        if (!syncButton || !syncButtonText) {
            return;
        }

        syncButton.disabled = true;
        syncButton.classList.add('is-loading');
        syncButtonText.textContent =
            '↻ MENYINKRONKAN...';
    });

    const modal =
        document.getElementById('employeeDetailModal');

    const modalDialog = modal?.querySelector(
        '.employee-modal-dialog'
    );

    const modalPhoto =
        document.getElementById('employeeModalPhoto');

    const modalPhotoPlaceholder =
        document.getElementById(
            'employeeModalPhotoPlaceholder'
        );

    const modalPhotoFrame =
        document.getElementById(
            'employeeModalPhotoFrame'
        );

    const modalPhotoLoader =
        document.getElementById(
            'employeeModalPhotoLoader'
        );

    const modalPhotoNotice =
        document.getElementById(
            'employeeModalPhotoNotice'
        );

    const modalPhotoRetry =
        document.getElementById(
            'employeeModalPhotoRetry'
        );

    const modalPhotoPlaceholderText =
        document.getElementById(
            'employeeModalPhotoPlaceholderText'
        );

    const modalPhotoLink =
        document.getElementById(
            'employeeModalPhotoLink'
        );

    const modalWhatsapp =
        document.getElementById(
            'employeeModalWhatsapp'
        );

    const modalEmailAction =
        document.getElementById(
            'employeeModalEmailAction'
        );

    const modalCompletenessBadge =
        document.getElementById(
            'employeeModalCompletenessBadge'
        );

    const modalProgressBar =
        document.getElementById(
            'employeeModalProgressBar'
        );

    const modalMissingBox =
        document.getElementById(
            'employeeModalMissingBox'
        );

    let lastModalTrigger = null;

    const textTargets = {
        employeeModalName: 'nama',
        employeeModalNrp: 'nrp',
        employeeModalJabatan: 'jabatan',
        employeeModalDepartemen: 'departemen',
        employeeModalPerusahaan: 'perusahaan',
        employeeModalSite: 'site',
        employeeModalStatusKaryawan: 'statusKaryawan',
        employeeModalTanggalLahir: 'tanggalLahir',
        employeeModalStatusTinggal: 'statusTinggal',
        employeeModalGedungKamar: 'gedungKamar',
        employeeModalNoHp: 'noHp',
        employeeModalEmail: 'email',
    };

    function setText(id, value) {
        const target = document.getElementById(id);

        if (!target) {
            return;
        }

        const normalized = String(value ?? '').trim();
        target.textContent = normalized !== ''
            ? normalized
            : '-';
    }

    function initials(name) {
        const words = String(name ?? '')
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        if (words.length === 0) {
            return '--';
        }

        return words
            .slice(0, 2)
            .map(function (word) {
                return word.charAt(0).toUpperCase();
            })
            .join('');
    }

    function configureLink(link, url) {
        if (!link) {
            return;
        }

        const normalizedUrl = String(url ?? '').trim();

        if (normalizedUrl === '') {
            link.hidden = true;
            link.removeAttribute('href');
            return;
        }

        link.href = normalizedUrl;
        link.hidden = false;
    }

    let photoLoadSequence = 0;
    let activePhotoData = null;

    function safePhotoCandidates(data) {
        const candidates = Array.isArray(
            data.fotoPreviewCandidates
        )
            ? data.fotoPreviewCandidates
            : [];

        if (
            String(data.fotoPreviewUrl ?? '').trim() !== ''
        ) {
            candidates.unshift(data.fotoPreviewUrl);
        }

        return Array.from(
            new Set(
                candidates
                    .map(function (candidate) {
                        return String(candidate ?? '').trim();
                    })
                    .filter(function (candidate) {
                        return /^https?:\/\//i.test(candidate);
                    })
            )
        );
    }

    function setPhotoState(state) {
        if (modalPhotoFrame) {
            modalPhotoFrame.dataset.photoState = state;
        }

        if (modalPhoto) {
            modalPhoto.hidden = state !== 'loaded';
        }

        if (modalPhotoLoader) {
            modalPhotoLoader.hidden = state !== 'loading';
        }

        if (modalPhotoPlaceholder) {
            modalPhotoPlaceholder.hidden = ![
                'missing',
                'failed',
            ].includes(state);
        }

        if (modalPhotoNotice) {
            modalPhotoNotice.hidden = state !== 'failed';
        }

        if (modalPhotoPlaceholderText) {
            modalPhotoPlaceholderText.textContent =
                state === 'failed'
                    ? 'PREVIEW FOTO GAGAL'
                    : 'FOTO BELUM TERSEDIA';
        }
    }

    function loadPhotoCandidate(
        candidates,
        index,
        sequence
    ) {
        if (!modalPhoto || sequence !== photoLoadSequence) {
            return;
        }

        if (index >= candidates.length) {
            modalPhoto.removeAttribute('src');
            setPhotoState('failed');
            return;
        }

        modalPhoto.onload = function () {
            if (sequence !== photoLoadSequence) {
                return;
            }

            setPhotoState('loaded');
        };

        modalPhoto.onerror = function () {
            if (sequence !== photoLoadSequence) {
                return;
            }

            loadPhotoCandidate(
                candidates,
                index + 1,
                sequence
            );
        };

        modalPhoto.src = candidates[index];
    }

    function showPhoto(data) {
        if (!modalPhoto || !modalPhotoPlaceholder) {
            return;
        }

        activePhotoData = data;
        photoLoadSequence += 1;

        const sequence = photoLoadSequence;
        const candidates = safePhotoCandidates(data);

        setText(
            'employeeModalInitials',
            initials(data.nama)
        );

        modalPhoto.removeAttribute('src');

        if (candidates.length === 0) {
            setPhotoState('missing');
            return;
        }

        setPhotoState('loading');

        loadPhotoCandidate(
            candidates,
            0,
            sequence
        );
    }

    function openEmployeeModal(trigger) {
        if (!modal || !modalDialog) {
            return;
        }

        let data = {};

        try {
            data = JSON.parse(
                trigger.dataset.employee || '{}'
            );
        } catch (error) {
            console.error(
                'Data detail karyawan tidak valid.',
                error
            );
            return;
        }

        Object.entries(textTargets).forEach(
            function ([targetId, dataKey]) {
                setText(targetId, data[dataKey]);
            }
        );

        const percentage = Math.max(
            0,
            Math.min(
                100,
                Number(data.completionPercentage) || 0
            )
        );

        const isComplete = Boolean(data.isComplete);

        setText(
            'employeeModalCompletenessBadge',
            data.completenessStatus ||
                (isComplete
                    ? 'LENGKAP'
                    : 'BELUM LENGKAP')
        );

        setText(
            'employeeModalPercentage',
            percentage + '%'
        );

        if (modalProgressBar) {
            modalProgressBar.style.width =
                percentage + '%';
        }

        if (modalCompletenessBadge) {
            modalCompletenessBadge.classList.toggle(
                'complete',
                isComplete
            );

            modalCompletenessBadge.classList.toggle(
                'incomplete',
                !isComplete
            );
        }

        const missingLabels = Array.isArray(
            data.missingFieldLabels
        )
            ? data.missingFieldLabels.filter(Boolean)
            : [];

        if (modalMissingBox) {
            modalMissingBox.hidden = isComplete;
        }

        setText(
            'employeeModalMissingFields',
            missingLabels.length > 0
                ? missingLabels.join(', ')
                : 'Tidak ada.'
        );

        showPhoto(data);

        configureLink(
            modalPhotoLink,
            data.fotoOpenUrl || data.fotoUrl
        );

        configureLink(
            modalWhatsapp,
            data.whatsappUrl
        );

        configureLink(
            modalEmailAction,
            data.emailUrl
        );

        lastModalTrigger = trigger;
        modal.hidden = false;
        document.body.classList.add(
            'employee-modal-open'
        );

        window.requestAnimationFrame(function () {
            modalDialog.focus();
        });
    }

    function closeEmployeeModal() {
        if (!modal || modal.hidden) {
            return;
        }

        modal.hidden = true;
        document.body.classList.remove(
            'employee-modal-open'
        );

        photoLoadSequence += 1;
        activePhotoData = null;

        if (modalPhoto) {
            modalPhoto.onload = null;
            modalPhoto.onerror = null;
            modalPhoto.removeAttribute('src');
        }

        setPhotoState('missing');

        lastModalTrigger?.focus();
        lastModalTrigger = null;
    }

    document
        .querySelectorAll('.employee-detail-trigger')
        .forEach(function (trigger) {
            trigger.addEventListener(
                'click',
                function () {
                    openEmployeeModal(trigger);
                }
            );
        });

    modalPhotoRetry?.addEventListener(
        'click',
        function () {
            if (activePhotoData) {
                showPhoto(activePhotoData);
            }
        }
    );

    modal
        ?.querySelectorAll(
            '[data-employee-modal-close]'
        )
        .forEach(function (closeButton) {
            closeButton.addEventListener(
                'click',
                closeEmployeeModal
            );
        });

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape' &&
                modal &&
                !modal.hidden
            ) {
                closeEmployeeModal();
            }
        }
    );
});
</script>
