@php
    $fieldMappings = collect(
        $diagnostics['field_mappings'] ?? []
    );

    $qualityRows = collect(
        $diagnostics['quality'] ?? []
    );

    $rawHeaders = collect(
        $diagnostics['raw_headers'] ?? []
    );

    $unmappedHeaders = collect(
        $diagnostics['unmapped_headers'] ?? []
    );

    $cache = $diagnostics['cache'] ?? [];
@endphp

<div class="db-page-title mapping-page-title">
    <div>
        <h1>Diagnostik MASTER_DATABASE</h1>

        <p>
            Pemeriksaan mapping langsung, field turunan,
            kualitas data, cache, dan kesiapan fallback.
        </p>
    </div>

    <div class="mapping-title-actions">
        <a
            href="{{ route('database.employees') }}"
            class="db-button secondary"
        >
            ← KEMBALI
        </a>

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

        <form
            method="POST"
            action="{{ route(
                'database.employees.test-fallback'
            ) }}"
        >
            @csrf

            <button
                type="submit"
                class="db-button warning"
            >
                UJI FALLBACK
            </button>
        </form>

        <a
            href="{{ route(
                'database.employees.mapping-diagnostics'
            ) }}"
            class="db-button"
        >
            ↻ PERIKSA ULANG
        </a>
    </div>
</div>

@if (session('success'))
    <section class="mapping-alert success">
        {{ session('success') }}
    </section>
@endif

@if (session('error'))
    <section class="mapping-alert danger">
        {{ session('error') }}
    </section>
@endif

@if (!empty($diagnosticError))
    <section class="mapping-alert danger">
        <strong>Diagnostik gagal dijalankan</strong>
        <p>{{ $diagnosticError }}</p>
    </section>
@else
    <section class="mapping-sticky-summary">
        <div class="mapping-kpi-grid">
            <article class="mapping-kpi">
                <small>Baris Header</small>
                <strong>
                    {{ $diagnostics['header_row'] ?? '-' }}
                </strong>
            </article>

            <article class="mapping-kpi">
                <small>Baris Data</small>
                <strong>
                    {{ number_format(
                        (int) (
                            $diagnostics['raw_row_count'] ??
                            0
                        )
                    ) }}
                </strong>
            </article>

            <article class="mapping-kpi success">
                <small>Mapping Langsung</small>
                <strong>
                    {{ $diagnostics[
                        'direct_mapped_count'
                    ] ?? 0 }}
                </strong>
            </article>

            <article class="mapping-kpi derived">
                <small>Field Turunan</small>
                <strong>
                    {{ $diagnostics[
                        'derived_field_count'
                    ] ?? 0 }}
                </strong>
            </article>

            <article class="mapping-kpi danger">
                <small>Tidak Tersedia</small>
                <strong>
                    {{ $diagnostics[
                        'unavailable_field_count'
                    ] ?? 0 }}
                </strong>
            </article>

            <article class="mapping-kpi">
                <small>Karyawan Terbaca</small>
                <strong>
                    {{ number_format(
                        (int) (
                            $diagnostics[
                                'mapped_employee_count'
                            ] ?? 0
                        )
                    ) }}
                </strong>
            </article>

            <article class="mapping-kpi warning">
                <small>NRP Duplikat</small>
                <strong>
                    {{ number_format(
                        (int) (
                            $diagnostics[
                                'duplicate_rows'
                            ] ?? 0
                        )
                    ) }}
                </strong>
            </article>
        </div>
    </section>

    <section class="mapping-card">
        <div class="mapping-card-head">
            <div>
                <h2>Status Cache dan Fallback</h2>
                <p>
                    Uji fallback bersifat aman: hanya membaca backup
                    cache, tidak menghapus cache dan tidak memanggil Google.
                </p>
            </div>

            <span
                class="mapping-cache-ready {{
                    !empty($cache['fallback_ready'])
                        ? 'success'
                        : 'danger'
                }}"
            >
                {{
                    !empty($cache['fallback_ready'])
                        ? 'FALLBACK SIAP'
                        : 'FALLBACK BELUM SIAP'
                }}
            </span>
        </div>

        <div class="mapping-cache-grid">
            <article>
                <small>Driver Cache</small>
                <strong>
                    {{ strtoupper(
                        $cache['driver'] ?? '-'
                    ) }}
                </strong>
            </article>

            <article>
                <small>Cache Segar</small>
                <strong>
                    {{
                        !empty($cache['fresh_exists'])
                            ? 'TERSEDIA'
                            : 'TIDAK ADA'
                    }}
                </strong>
                <span>
                    {{ number_format(
                        (int) (
                            $cache['fresh_count'] ?? 0
                        )
                    ) }}
                    data
                </span>
            </article>

            <article>
                <small>Backup Cache</small>
                <strong>
                    {{
                        !empty($cache['backup_exists'])
                            ? 'TERSEDIA'
                            : 'TIDAK ADA'
                    }}
                </strong>
                <span>
                    {{ number_format(
                        (int) (
                            $cache['backup_count'] ?? 0
                        )
                    ) }}
                    data
                </span>
            </article>

            <article>
                <small>Konsistensi Data</small>
                <strong>
                    {{
                        !empty($cache['same_data'])
                            ? 'SAMA'
                            : 'BERBEDA / BELUM ADA'
                    }}
                </strong>
                <span>
                    Cache segar dibandingkan backup.
                </span>
            </article>

            <article>
                <small>Metadata</small>
                <strong>
                    {{
                        !empty($cache['meta_exists'])
                            ? 'TERSEDIA'
                            : 'TIDAK ADA'
                    }}
                </strong>
                <span>
                    Status:
                    {{ $cache['status'] ?? '-' }}
                </span>
            </article>
        </div>
    </section>

    <section class="mapping-card">
        <div class="mapping-card-head">
            <div>
                <h2>Hasil Mapping Kolom</h2>
                <p>
                    Hijau: mapping langsung. Biru: field dihitung
                    otomatis. Merah: tidak tersedia dari sumber ini.
                </p>
            </div>

            <span class="mapping-range">
                {{ $diagnostics['range'] ?? '-' }}
            </span>
        </div>

        <div class="mapping-table-scroll">
            <table class="mapping-table">
                <thead>
                    <tr>
                        <th>Field Sistem</th>
                        <th>Status</th>
                        <th>Kolom</th>
                        <th>Header Asli</th>
                        <th>Header Normalisasi</th>
                        <th>Contoh Isi</th>
                        <th>Alias / Formula</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($fieldMappings as $mapping)
                        @php
                            $mappingStatus =
                                $mapping[
                                    'mapping_status'
                                ] ?? (
                                    $mapping['matched']
                                        ? 'direct'
                                        : 'missing'
                                );
                        @endphp

                        <tr
                            class="{{
                                $mappingStatus === 'missing'
                                    ? 'mapping-missing-row'
                                    : (
                                        $mappingStatus === 'derived'
                                            ? 'mapping-derived-row'
                                            : ''
                                    )
                            }}"
                        >
                            <td>
                                <strong>
                                    {{ $mapping['label'] }}
                                </strong>

                                <small>
                                    {{ $mapping['field'] }}
                                </small>
                            </td>

                            <td>
                                @if ($mappingStatus === 'direct')
                                    <span class="mapping-badge success">
                                        TERDETEKSI
                                    </span>
                                @elseif ($mappingStatus === 'derived')
                                    <span class="mapping-badge derived">
                                        DITURUNKAN
                                    </span>
                                @else
                                    <span class="mapping-badge danger">
                                        TIDAK TERSEDIA
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $mapping['column_letter'] }}
                            </td>

                            <td>
                                {{ $mapping['raw_header'] }}
                            </td>

                            <td>
                                {{ $mapping[
                                    'normalized_header'
                                ] }}
                            </td>

                            <td>
                                @forelse (
                                    $mapping['samples']
                                    as $sample
                                )
                                    <span class="mapping-sample">
                                        {{ $sample }}
                                    </span>
                                @empty
                                    <span class="mapping-muted">
                                        Tidak ada contoh
                                    </span>
                                @endforelse
                            </td>

                            <td>
                                <details>
                                    <summary>
                                        {{
                                            $mappingStatus === 'derived'
                                                ? 'Lihat formula'
                                                : 'Lihat alias'
                                        }}
                                    </summary>

                                    <div class="mapping-alias-list">
                                        @foreach (
                                            $mapping['aliases']
                                            as $alias
                                        )
                                            <span>{{ $alias }}</span>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="mapping-card">
        <div class="mapping-card-head">
            <div>
                <h2>Kualitas Data Setelah Mapping</h2>
                <p>
                    Field turunan Gedung/Kamar juga dihitung dari
                    nilai Gedung dan Kamar.
                </p>
            </div>
        </div>

        <div class="mapping-quality-grid">
            @foreach ($qualityRows as $quality)
                @php
                    $percentage =
                        (float) (
                            $quality[
                                'filled_percentage'
                            ] ?? 0
                        );

                    $qualityClass =
                        $percentage >= 90
                            ? 'success'
                            : (
                                $percentage >= 60
                                    ? 'warning'
                                    : 'danger'
                            );
                @endphp

                <article
                    class="mapping-quality {{ $qualityClass }}"
                >
                    <div class="mapping-quality-head">
                        <strong>
                            {{ $quality['label'] }}
                        </strong>

                        <span>
                            {{ number_format(
                                $percentage,
                                1
                            ) }}%
                        </span>
                    </div>

                    <div class="mapping-progress">
                        <span
                            style="width: {{
                                min(100, max(0, $percentage))
                            }}%;"
                        ></span>
                    </div>

                    <small>
                        Terisi:
                        {{ number_format(
                            $quality['filled_count']
                        ) }}

                        · Kosong:
                        {{ number_format(
                            $quality['empty_count']
                        ) }}
                    </small>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mapping-card">
        <div class="mapping-card-head">
            <div>
                <h2>Header Belum Digunakan</h2>
                <p>
                    Header ini tidak digunakan oleh mapping
                    MASTER_DATABASE saat ini.
                </p>
            </div>

            <span class="mapping-count">
                {{ $unmappedHeaders->count() }} header
            </span>
        </div>

        <div class="mapping-unmapped-grid">
            @forelse ($unmappedHeaders as $header)
                <article class="mapping-unmapped">
                    <span class="mapping-column-letter">
                        {{ $header['column_letter'] }}
                    </span>

                    <div>
                        <strong>
                            {{ $header['raw_header'] }}
                        </strong>

                        <small>
                            {{ $header[
                                'normalized_header'
                            ] }}
                        </small>

                        <p>
                            @forelse (
                                $header['samples']
                                as $sample
                            )
                                <span class="mapping-sample">
                                    {{ $sample }}
                                </span>
                            @empty
                                Tidak ada contoh isi.
                            @endforelse
                        </p>
                    </div>
                </article>
            @empty
                <p class="mapping-empty">
                    Semua header sudah digunakan oleh mapping.
                </p>
            @endforelse
        </div>
    </section>

    <section class="mapping-card">
        <div class="mapping-card-head">
            <div>
                <h2>Semua Header Spreadsheet</h2>
                <p>
                    Urutan kolom sesuai sheet MASTER_DATABASE.
                </p>
            </div>
        </div>

        <div class="mapping-header-grid">
            @foreach ($rawHeaders as $header)
                <article class="mapping-header-item">
                    <span>
                        {{ $header['column_letter'] }}
                    </span>

                    <div>
                        <strong>
                            {{ $header['raw_header'] }}
                        </strong>

                        <small>
                            {{ $header[
                                'normalized_header'
                            ] }}
                        </small>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif

<style>
.mapping-page-title {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.mapping-title-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 7px;
}

.mapping-title-actions form {
    margin: 0;
}

.mapping-alert {
    margin-bottom: 10px;
    padding: 12px 14px;
    border: 1px solid;
    border-radius: 11px;
    font-size: 10px;
    font-weight: 800;
}

.mapping-alert.success {
    border-color: #86efac;
    color: #087a45;
    background: #f0fdf4;
}

.mapping-alert.danger {
    border-color: #fca5a5;
    color: #991b1b;
    background: #fef2f2;
}

.mapping-alert p {
    margin: 5px 0 0;
}

.mapping-sticky-summary {
    position: sticky;
    z-index: 35;
    top: 0;
    margin-bottom: 11px;
    padding: 2px;
    background: var(--db-bg);
}

.mapping-kpi-grid {
    display: grid;
    grid-template-columns:
        repeat(7, minmax(0, 1fr));
    gap: 9px;
}

.mapping-kpi {
    padding: 12px;
    border: 1px solid #dbe2ea;
    border-radius: 10px;
    background: #fff;
    box-shadow: var(--db-shadow);
}

.mapping-kpi.success {
    border-color: #86efac;
    background: #f0fdf4;
}

.mapping-kpi.derived {
    border-color: #93c5fd;
    background: #eff6ff;
}

.mapping-kpi.warning {
    border-color: #fcd34d;
    background: #fffbeb;
}

.mapping-kpi.danger {
    border-color: #fca5a5;
    background: #fef2f2;
}

.mapping-kpi small,
.mapping-kpi strong {
    display: block;
}

.mapping-kpi small {
    color: #64748b;
    font-size: 8px;
    font-weight: 900;
    text-transform: uppercase;
}

.mapping-kpi strong {
    margin-top: 4px;
    color: #172033;
    font-size: 22px;
}

.mapping-card {
    margin-bottom: 11px;
    overflow: hidden;
    border: 1px solid #dbe2ea;
    border-radius: 11px;
    background: #fff;
    box-shadow: var(--db-shadow);
}

.mapping-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 13px;
    border-bottom: 1px solid #e2e8f0;
}

.mapping-card-head h2 {
    margin: 0 0 3px;
    font-size: 13px;
}

.mapping-card-head p {
    margin: 0;
    color: #64748b;
    font-size: 9px;
}

.mapping-range,
.mapping-count,
.mapping-cache-ready {
    font-size: 9px;
    font-weight: 900;
}

.mapping-range,
.mapping-count {
    color: #1d4ed8;
}

.mapping-cache-ready {
    padding: 5px 8px;
    border-radius: 999px;
}

.mapping-cache-ready.success {
    color: #087a45;
    background: #dcfce7;
}

.mapping-cache-ready.danger {
    color: #b91c1c;
    background: #fee2e2;
}

.mapping-cache-grid {
    display: grid;
    grid-template-columns:
        repeat(5, minmax(0, 1fr));
    gap: 9px;
    padding: 13px;
}

.mapping-cache-grid article {
    padding: 11px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #f8fafc;
}

.mapping-cache-grid small,
.mapping-cache-grid strong,
.mapping-cache-grid span {
    display: block;
}

.mapping-cache-grid small {
    color: #64748b;
    font-size: 8px;
    font-weight: 900;
    text-transform: uppercase;
}

.mapping-cache-grid strong {
    margin-top: 4px;
    color: #172033;
    font-size: 12px;
}

.mapping-cache-grid span {
    margin-top: 3px;
    color: #64748b;
    font-size: 8px;
}

.mapping-table-scroll {
    height: clamp(
        320px,
        calc(100vh - 350px),
        600px
    );
    overflow: auto;
}

.mapping-table {
    width: 100%;
    min-width: 1250px;
    border-collapse: collapse;
}

.mapping-table th,
.mapping-table td {
    padding: 10px;
    border-bottom: 1px solid #e5e7eb;
    color: #334155;
    font-size: 9px;
    text-align: left;
    vertical-align: top;
}

.mapping-table th {
    position: sticky;
    z-index: 10;
    top: 0;
    color: #475569;
    background: #f8fafc;
    font-size: 8px;
    text-transform: uppercase;
}

.mapping-table td strong,
.mapping-table td small {
    display: block;
}

.mapping-table td small {
    margin-top: 3px;
    color: #94a3b8;
}

.mapping-missing-row td {
    background: #fff7f7;
}

.mapping-derived-row td {
    background: #f4f8ff;
}

.mapping-badge {
    display: inline-flex;
    min-height: 22px;
    align-items: center;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 8px;
    font-weight: 900;
}

.mapping-badge.success {
    color: #087a45;
    background: #dcfce7;
}

.mapping-badge.derived {
    color: #1d4ed8;
    background: #dbeafe;
}

.mapping-badge.danger {
    color: #b91c1c;
    background: #fee2e2;
}

.mapping-sample {
    display: inline-block;
    margin: 0 3px 3px 0;
    padding: 3px 6px;
    border-radius: 6px;
    color: #334155;
    background: #f1f5f9;
    font-size: 8px;
}

.mapping-muted {
    color: #94a3b8;
}

.mapping-alias-list {
    display: flex;
    max-width: 310px;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 7px;
}

.mapping-alias-list span {
    padding: 3px 6px;
    border-radius: 6px;
    color: #475569;
    background: #eef2f7;
    font-size: 8px;
}

.mapping-quality-grid {
    display: grid;
    grid-template-columns:
        repeat(4, minmax(0, 1fr));
    gap: 9px;
    padding: 13px;
}

.mapping-quality {
    padding: 11px;
    border: 1px solid #dbe2ea;
    border-radius: 9px;
}

.mapping-quality-head {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    font-size: 9px;
}

.mapping-quality-head span {
    font-weight: 900;
}

.mapping-progress {
    height: 6px;
    margin: 8px 0;
    overflow: hidden;
    border-radius: 999px;
    background: #e2e8f0;
}

.mapping-progress span {
    display: block;
    height: 100%;
    background: #16a34a;
}

.mapping-quality.warning .mapping-progress span {
    background: #f59e0b;
}

.mapping-quality.danger .mapping-progress span {
    background: #dc2626;
}

.mapping-quality small {
    color: #64748b;
    font-size: 8px;
}

.mapping-unmapped-grid,
.mapping-header-grid {
    display: grid;
    grid-template-columns:
        repeat(3, minmax(0, 1fr));
    gap: 9px;
    padding: 13px;
}

.mapping-unmapped,
.mapping-header-item {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    min-width: 0;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #f8fafc;
}

.mapping-column-letter,
.mapping-header-item > span {
    display: grid;
    width: 33px;
    height: 33px;
    flex: 0 0 33px;
    place-items: center;
    border-radius: 8px;
    color: #fff;
    background: #334155;
    font-size: 9px;
    font-weight: 900;
}

.mapping-unmapped strong,
.mapping-unmapped small,
.mapping-header-item strong,
.mapping-header-item small {
    display: block;
    overflow-wrap: anywhere;
}

.mapping-unmapped strong,
.mapping-header-item strong {
    font-size: 9px;
}

.mapping-unmapped small,
.mapping-header-item small {
    margin-top: 3px;
    color: #64748b;
    font-size: 8px;
}

.mapping-unmapped p {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 8px;
}

.mapping-empty {
    grid-column: 1 / -1;
    padding: 25px;
    color: #64748b;
    text-align: center;
}

@media (max-width: 1350px) {
    .mapping-kpi-grid {
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
    }

    .mapping-cache-grid {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 1000px) {
    .mapping-kpi-grid,
    .mapping-quality-grid,
    .mapping-unmapped-grid,
    .mapping-header-grid,
    .mapping-cache-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .mapping-page-title {
        flex-direction: column;
    }

    .mapping-title-actions {
        justify-content: flex-start;
    }

    .mapping-sticky-summary {
        position: static;
    }

    .mapping-kpi-grid,
    .mapping-quality-grid,
    .mapping-unmapped-grid,
    .mapping-header-grid,
    .mapping-cache-grid {
        grid-template-columns: 1fr;
    }
}
</style>