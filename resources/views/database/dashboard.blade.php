<div class="db-page-title">
    <h1>Dashboard Database &amp; ATR</h1>
    <p>Ringkasan UI modul Database Karyawan dan ATR Karyawan.</p>
</div>

<div class="db-kpi-grid">
    <article class="db-kpi-card">
        <span class="db-kpi-icon">👥</span>
        <div>
            <small>Database Karyawan</small>
            <strong>777</strong>
        </div>
    </article>

    <article class="db-kpi-card">
        <span class="db-kpi-icon">📈</span>
        <div>
            <small>Data ATR Terakhir</small>
            <strong>863</strong>
        </div>
    </article>

    <article class="db-kpi-card">
        <span class="db-kpi-icon">☎</span>
        <div>
            <small>Perlu Pemanggilan</small>
            <strong>210</strong>
        </div>
    </article>
</div>

<div class="db-grid-2">
    <section class="db-panel">
        <div class="db-card-header">
            <div>
                <h2>Database Karyawan</h2>
                <small>UI memakai data contoh pada Fase 1.</small>
            </div>
            <a
                href="{{ route('database.employees') }}"
                class="db-button"
            >
                BUKA DATABASE
            </a>
        </div>

        <p>
            Pencarian NRP/nama, filter tempat tinggal, detail
            identitas, kontak, serta hunian karyawan.
        </p>
    </section>

    <section class="db-panel">
        <div class="db-card-header">
            <div>
                <h2>ATR Karyawan</h2>
                <small>Upload, preview, riwayat, dan pemanggilan.</small>
            </div>
            <a
                href="{{ route('database.atr.summary') }}"
                class="db-button purple"
            >
                BUKA ATR
            </a>
        </div>

        <p>
            Format Excel aktual akan dipasang setelah struktur
            UI/UX disetujui.
        </p>
    </section>
</div>