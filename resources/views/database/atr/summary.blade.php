<div class="db-page-title">
    <h1>Ringkasan ATR Karyawan</h1>
    <p>Monitoring absensi dan progres pemanggilan karyawan.</p>
</div>

<div class="atr-sticky-zone atr-summary-sticky">
<section class="db-panel">
    <div class="db-filter-grid">
        <div class="db-field">
            <label>Bulan</label>
            <select class="db-select">
                <option>Juni 2026</option>
            </select>
        </div>

        <div class="db-field">
            <label>Posisi / Jabatan</label>
            <select class="db-select">
                <option>Semua Posisi</option>
            </select>
        </div>

        <div class="db-field">
            <label>Cari Karyawan</label>
            <input
                class="db-input"
                placeholder="Cari NRP atau nama…"
            >
        </div>
    </div>
</section>

<section class="db-panel">
    <div class="db-card-header">
        <div>
            <h2>Statistik ATR — Juni 2026</h2>
            <small>Ambang aman ≥ 98,5%</small>
        </div>
    </div>

    <div class="atr-stat-grid">
        @foreach ([
            ['label' => 'Aman ≥98,5%', 'value' => $atrStats['aman'], 'color' => '#16a05d'],
            ['label' => 'Di Bawah', 'value' => $atrStats['di_bawah'], 'color' => '#ed1c2e'],
            ['label' => 'No Data', 'value' => $atrStats['no_data'], 'color' => '#64748b'],
            ['label' => 'Total Sakit', 'value' => $atrStats['sakit'], 'color' => '#f59e0b'],
            ['label' => 'Total Izin', 'value' => $atrStats['izin'], 'color' => '#2563eb'],
            ['label' => 'Total Alpa', 'value' => $atrStats['alpa'], 'color' => '#e11d48'],
        ] as $stat)
            <article
                class="atr-stat"
                style="border-top-color: {{ $stat['color'] }}"
            >
                <strong style="color: {{ $stat['color'] }}">
                    {{ $stat['value'] }}
                </strong>
                <small>{{ $stat['label'] }}</small>
            </article>
        @endforeach
    </div>
</section>
</div>

<section class="db-panel">
    <div class="db-card-header">
        <div>
            <h2>Progress Pemanggilan</h2>
            <small>{{ $atrProgress['percentage'] }}% selesai</small>
        </div>
    </div>

    <div class="atr-progress-row">
        <div>
            <strong>{{ $atrProgress['belum'] }}</strong>
            <small>BELUM</small>
        </div>
        <div>
            <strong>{{ $atrProgress['sudah'] }}</strong>
            <small>SUDAH</small>
        </div>
        <div>
            <strong>{{ $atrProgress['total'] }}</strong>
            <small>TOTAL PERLU</small>
        </div>
    </div>

    <div class="atr-track">
        <div
            class="atr-bar"
            style="width: {{ $atrProgress['percentage'] }}%"
        ></div>
    </div>
</section>

<section class="db-table-card">
    <div class="db-card-header">
        <div>
            <h2>Top Absensi S + I + A Terbanyak</h2>
            <small>Data dummy untuk finalisasi UI.</small>
        </div>
    </div>

    <div class="db-table-wrap atr-table-scroll atr-ranking-scroll">
        <table class="db-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>NRP</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>S</th>
                    <th>I</th>
                    <th>A</th>
                    <th>Total</th>
                    <th>ATR%</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($atrRanking as $index => $employee)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $employee['nrp'] }}</td>
                        <td>{{ $employee['nama'] }}</td>
                        <td>{{ $employee['jabatan'] }}</td>
                        <td>{{ $employee['s'] }}</td>
                        <td>{{ $employee['i'] }}</td>
                        <td>{{ $employee['a'] }}</td>
                        <td>
                            {{ $employee['s'] + $employee['i'] + $employee['a'] }}
                        </td>
                        <td>
                            <span class="db-badge red">
                                {{ $employee['atr'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>