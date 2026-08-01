<div class="db-page-title">
    <h1>Upload Data ATR</h1>
    <p>UI upload raw Excel dari website internal perusahaan.</p>
</div>

<div class="db-grid-2">
    <section class="db-panel">
        <div class="db-card-header">
            <div>
                <h2>1. Pilih File Excel</h2>
                <small>XLSX/CSV — backend belum diaktifkan.</small>
            </div>
        </div>

        <label class="db-upload-zone" for="atrRawFile">
            <span>
                <strong>📁 Pilih file ATR</strong>
                <span id="atrRawFileName">
                    Belum ada file dipilih
                </span>
            </span>
        </label>

        <input
            id="atrRawFile"
            type="file"
            accept=".xlsx,.xls,.csv"
            hidden
        >
    </section>

    <section class="db-panel">
        <div class="db-card-header">
            <div>
                <h2>2. Informasi Import</h2>
                <small>Konfirmasi periode dan sumber data.</small>
            </div>
        </div>

        <div class="db-grid-2">
            <div class="db-field">
                <label>Tanggal Awal</label>
                <input type="date" class="db-input">
            </div>

            <div class="db-field">
                <label>Tanggal Akhir</label>
                <input type="date" class="db-input">
            </div>

            <div class="db-field">
                <label>Departemen</label>
                <input
                    class="db-input"
                    value="PRO"
                >
            </div>

            <div class="db-field">
                <label>Perusahaan</label>
                <input
                    class="db-input"
                    value="PPA"
                >
            </div>
        </div>

        <div class="db-actions" style="margin-top: 12px">
            <button
                type="button"
                class="db-button"
                disabled
            >
                PREVIEW DATA
            </button>
        </div>
    </section>
</div>

<section class="db-table-card atr-upload-preview-card">
    <div class="db-card-header">
        <div>
            <h2>Preview Import</h2>
            <small>
                Akan menampilkan validasi header, NRP, ATR, dan duplikat.
            </small>
        </div>
    </div>

    <div class="db-empty">
        Pilih file untuk melihat preview data pada tahap backend.
    </div>
</section>