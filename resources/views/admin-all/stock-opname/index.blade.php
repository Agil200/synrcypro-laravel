@extends('admin-all.layout')

@section('admin-content')
<!-- Chart.js CDN untuk Visualisasi Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .so-dashboard {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        padding-bottom: 30px;
    }

    /* Page Header */
    .so-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .so-title-group h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.5px;
    }
    .so-title-group p {
        margin: 4px 0 0;
        font-size: 11.5px;
        color: #64748b;
    }
    .so-actions-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Action Buttons */
    .btn-so-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 34px;
        padding: 0 14px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
    }
    .btn-so-action:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .btn-so-action.primary {
        background: #0284c7;
        border-color: #0284c7;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    }
    .btn-so-action.primary:hover {
        background: #0369a1;
    }
    .btn-so-action.success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #15803d;
    }
    .btn-so-action.success:hover {
        background: #dcfce7;
    }

    /* Filter Bar */
    .so-filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        flex-wrap: wrap;
    }
    .filter-inner {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .filter-label {
        font-size: 10px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .so-input {
        height: 32px;
        padding: 0 10px;
        font-size: 11px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        outline: none;
        color: #1e293b;
        background: #ffffff;
    }
    .so-input:focus {
        border-color: #0284c7;
    }

    /* 4 Summary Metric Cards (Mirip Dashboard MCU & FU) */
    .so-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 22px;
    }
    .so-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 18px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        position: relative;
        overflow: hidden;
    }
    .so-kpi-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
    }
    .so-kpi-card.teal::before { background: #0d9488; }
    .so-kpi-card.blue::before { background: #0284c7; }
    .so-kpi-card.amber::before { background: #d97706; }
    .so-kpi-card.purple::before { background: #7c3aed; }

    .kpi-title {
        font-size: 10px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .kpi-subtitle {
        font-size: 10px;
        color: #94a3b8;
    }

    /* Analytics Charts Section */
    .so-analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 22px;
    }
    .analytics-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .analytics-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
    }
    .analytics-header h3 {
        font-size: 12px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .chart-container {
        position: relative;
        height: 180px;
        width: 100%;
    }

    /* Table Panel */
    .so-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .so-panel-header {
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafbfc;
        flex-wrap: wrap;
        gap: 10px;
    }
    .so-panel-header h2 {
        margin: 0;
        font-size: 13px;
        font-weight: 800;
        color: #0f172a;
    }
    .so-panel-header p {
        margin: 2px 0 0;
        font-size: 11px;
        color: #64748b;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    .so-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        white-space: nowrap;
    }
    .so-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 14px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }
    .so-table td {
        padding: 11px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .so-table tr:hover td {
        background: #f8fafc;
    }
    
    /* Chips & Buttons */
    .chip-item {
        display: inline-flex;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f0fdfa;
        color: #0f766e;
        border: 1px solid #ccfbf1;
        font-weight: 700;
        font-size: 10.5px;
    }
    .btn-photo {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 6px;
        border: 1px solid #bfdbfe;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-photo:hover {
        background: #dbeafe;
    }

    /* Modal Custom */
    .modal-backdrop-custom { position: fixed; inset: 0; background: rgba(15, 23, 42, .6); backdrop-filter: blur(3px); z-index: 1040; display: none; }
    .modal-custom { position: fixed; inset: 0; z-index: 1050; display: none; place-items: center; padding: 15px; overflow-y: auto; }
    .modal-card-custom { position: relative; width: min(540px, 100%); padding: 22px; border-radius: 12px; background: #fff; box-shadow: 0 20px 40px rgba(15, 23, 42, .2); }
    .modal-card-custom h3 { margin: 0 0 4px; font-size: 15px; font-weight: 800; color: #0f172a; }
    .modal-card-custom p { margin: 0 0 16px; font-size: 11.5px; color: #64748b; }
    
    .form-group-custom { margin-bottom: 12px; }
    .form-group-custom label { display: block; margin-bottom: 4px; color: #334155; font-size: 11px; font-weight: 700; }
    .form-control-custom { width: 100%; height: 36px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 11.5px; outline: 0; background: #fff; color: #1e293b; }
    .form-control-custom:focus { border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, .1); }
    .form-control-custom:read-only { background: #f8fafc; color: #64748b; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }

    @media (max-width: 950px) {
        .so-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .so-analytics-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .so-kpi-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="so-dashboard">
    <!-- Header Halaman -->
    <div class="so-header">
        <div class="so-title-group">
            <h1>Stock Opname & Pengambilan Barang</h1>
            <p>Pencatatan inventaris departemen produksi site Bukit Asam secara cepat dan terstruktur.</p>
        </div>
        <div class="so-actions-group">
            <button type="button" class="btn-so-action success" onclick="openExportModal()">
                ⬇ Download Excel
            </button>
            <button type="button" class="btn-so-action" onclick="openItemManagerModal()">
                ⚙️ Kelola Master Barang
            </button>
            <button type="button" class="btn-so-action primary" onclick="openTransactionModal()">
                + Tambah Riwayat
            </button>
        </div>
    </div>

    <!-- Filter Bar Interaktif -->
    <div class="so-filter-card">
        <div class="filter-inner">
            <span class="filter-label">Filter Periode:</span>
            <select id="filter-dashboard-period" class="so-input" style="font-weight: 700;">
                <option value="month" selected>Bulanan</option>
                <option value="week">Mingguan</option>
            </select>
            <input type="date" id="filter-dashboard-date" class="so-input">
            <button type="button" id="btn-apply-filter" class="btn-so-action primary" style="height: 32px;" onclick="loadDashboardData()">
                Terapkan Filter
            </button>
        </div>
        <div class="filter-inner">
            <input type="search" id="table-search" class="so-input" placeholder="Cari nama, NRP, barang, lokasi..." style="width: 220px;">
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="so-kpi-grid">
        <div class="so-kpi-card teal">
            <div class="kpi-title">Total Barang Keluar</div>
            <div class="kpi-value" id="stat-total">0</div>
            <div class="kpi-subtitle">Jumlah kuantitas pada periode ini</div>
        </div>
        <div class="so-kpi-card blue">
            <div class="kpi-title">Karyawan Mengambil</div>
            <div class="kpi-value" id="stat-employees">0</div>
            <div class="kpi-subtitle">NRP unik terverifikasi</div>
        </div>
        <div class="so-kpi-card amber">
            <div class="kpi-title">Barang Terbanyak</div>
            <div class="kpi-value" id="stat-top-item" style="font-size: 16px; margin-top: 6px;">-</div>
            <div class="kpi-subtitle">Item paling sering diminta</div>
        </div>
        <div class="so-kpi-card purple">
            <div class="kpi-title">Rata-rata Hari Aktif</div>
            <div class="kpi-value" id="stat-average">0</div>
            <div class="kpi-subtitle">Barang keluar per hari kerja</div>
        </div>
    </div>

    <!-- Analytics Visual Grid -->
    <div class="so-analytics-grid">
        <div class="analytics-panel">
            <div class="analytics-header">
                <h3>Top 6 Pengeluaran Barang Terbanyak</h3>
            </div>
            <div class="chart-container">
                <canvas id="topItemsChart"></canvas>
            </div>
        </div>
        <div class="analytics-panel">
            <div class="analytics-header">
                <h3>Tren Pengeluaran Barang Harian</h3>
            </div>
            <div class="chart-container">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Panel Tabel Riwayat Pengambilan -->
    <div class="so-panel">
        <div class="so-panel-header">
            <div>
                <h2>Riwayat Pengambilan Barang</h2>
                <p id="transaction-count">0 transaksi ditemukan.</p>
            </div>
        </div>
        <div class="table-container">
            <table class="so-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Karyawan</th>
                        <th>NRP</th>
                        <th>Jabatan</th>
                        <th>Barang</th>
                        <th>Lokasi Tujuan</th>
                        <th>Foto Bukti</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="transaction-body">
                    <tr><td colspan="8" style="text-align: center; padding: 24px; color: #64748b;">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DOWNLOAD EXCEL FILTER TANGGAL -->
<div class="modal-backdrop-custom" id="exportBackdrop"></div>
<div class="modal-custom" id="exportModal">
    <div class="modal-card-custom">
        <h3>Download Excel Riwayat Pengambilan</h3>
        <p>Pilih rentang tanggal data riwayat pengambilan barang yang ingin diexport ke format Excel (.csv).</p>
        <form method="GET" action="{{ route('barang.export.excel') }}" onsubmit="closeModal('exportModal')">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group-custom">
                    <label>Dari Tanggal (Mulai)</label>
                    <input type="date" name="start_date" id="export-start-date" class="form-control-custom" required>
                </div>
                <div class="form-group-custom">
                    <label>Sampai Tanggal (Selesai)</label>
                    <input type="date" name="end_date" id="export-end-date" class="form-control-custom" required>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-so-action" onclick="closeModal('exportModal')">Batal</button>
                <button type="submit" class="btn-so-action primary" style="background: #15803d; border-color: #15803d;">Download File</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT TRANSAKSI -->
<div class="modal-backdrop-custom" id="transactionBackdrop"></div>
<div class="modal-custom" id="transactionModal">
    <div class="modal-card-custom">
        <h3 id="modal-title-action">Tambah Riwayat Pengambilan</h3>
        <p>Lengkapi formulir di bawah ini untuk mencatat pengeluaran barang gudang.</p>
        <form id="transaction-crud-form" onsubmit="handleTransactionSubmit(event)">
            <input type="hidden" id="crud-id">
            <div class="form-group-custom">
                <label>Tanggal</label>
                <input type="date" id="crud-date" class="form-control-custom" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <div class="form-group-custom">
                    <label>NRP</label>
                    <input type="text" id="crud-nrp" class="form-control-custom" placeholder="Contoh: 12345678" required autocomplete="off">
                </div>
                <div class="form-group-custom">
                    <label>Nama Lengkap</label>
                    <input type="text" id="crud-name" class="form-control-custom" placeholder="Terisi otomatis dari NRP" required>
                </div>
            </div>
            <div class="form-group-custom">
                <label>Jabatan</label>
                <input type="text" id="crud-jabatan" class="form-control-custom" placeholder="Terisi otomatis dari NRP" readonly>
            </div>
            <div class="form-group-custom">
                <label>Barang</label>
                <select id="crud-item" class="form-control-custom" required></select>
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 8px;">
                <div class="form-group-custom">
                    <label>Jumlah</label>
                    <input type="number" id="crud-qty" class="form-control-custom" min="1" value="1" required>
                </div>
                <div class="form-group-custom">
                    <label>Satuan</label>
                    <select id="crud-unit" class="form-control-custom">
                        <option value="Pcs">Pcs</option>
                        <option value="Box">Box</option>
                        <option value="Buah">Buah</option>
                        <option value="Bungkus">Bungkus</option>
                        <option value="Rim">Rim</option>
                        <option value="Botol">Botol</option>
                        <option value="Unit">Unit</option>
                    </select>
                </div>
            </div>
            <div class="form-group-custom">
                <label>Lokasi Tujuan</label>
                <input type="text" id="crud-lokasi" class="form-control-custom" placeholder="Contoh: PIT 1, Workshop, Hauling" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-so-action" onclick="closeModal('transactionModal')">Batal</button>
                <button type="submit" class="btn-so-action primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL KELOLA MASTER BARANG -->
<div class="modal-backdrop-custom" id="itemManagerBackdrop"></div>
<div class="modal-custom" id="itemManagerModal">
    <div class="modal-card-custom" style="width: min(580px, 100%);">
        <h3>Kelola Master Barang</h3>
        <p>Tambah, edit nama/status, atau hapus jenis barang inventaris.</p>
        <form id="add-item-form" onsubmit="handleAddMasterItem(event)" style="display: flex; gap: 7px; margin-bottom: 12px;">
            <input type="text" id="new-item-name" class="form-control-custom" placeholder="Nama barang baru..." required style="flex: 1;">
            <button type="submit" class="btn-so-action primary" style="min-height: 36px;">Tambah</button>
        </form>
        <div class="table-container" style="max-height: 260px; overflow-y: auto;">
            <table class="so-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th style="text-align: center;">Aktif</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="master-item-body">
                    <tr><td colspan="4" style="text-align: center; padding: 15px;">Memuat master barang...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-so-action" onclick="closeModal('itemManagerModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL LIHAT FOTO -->
<div class="modal-backdrop-custom" id="photoBackdrop"></div>
<div class="modal-custom" id="photoModal">
    <div class="modal-card-custom" style="text-align: center; width: min(440px, 100%);">
        <h3>Foto Bukti Pengambilan</h3>
        <p id="photo-loader" style="padding: 20px 0;">Memuat foto...</p>
        <img id="modal-photo-img" style="display: none; max-width: 100%; max-height: 55vh; border-radius: 8px; margin: 8px auto 14px; object-fit: contain;" alt="Foto Bukti">
        <div class="modal-actions" style="justify-content: center;">
            <button type="button" class="btn-so-action" onclick="closeModal('photoModal')">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentTransactions = [];
    let masterItemsList = [];
    let globalKaryawanMap = {};
    let modalLookupTimer = null;
    let topItemsChartInstance = null;
    let trendChartInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        // Set default nilai input tanggal ke format YYYY-MM-DD
        const dateInput = document.getElementById('filter-dashboard-date');
        if (dateInput && !dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }

        loadDashboardData();
        loadMasterItems();
        loadPublicConfigForModal();

        document.getElementById('table-search')?.addEventListener('input', function(e) {
            filterTable(e.target.value);
        });

        // Trigger filter otomatis saat pilihan dropdown periode diubah
        document.getElementById('filter-dashboard-period')?.addEventListener('change', () => loadDashboardData());

        // Realtime NRP Lookup pada Modal Admin
        document.getElementById('crud-nrp')?.addEventListener('input', function(e) {
            clearTimeout(modalLookupTimer);
            const val = e.target.value.trim().toUpperCase();
            const nameInput = document.getElementById('crud-name');
            const jabatanInput = document.getElementById('crud-jabatan');
            
            if (val.length < 2) {
                if (nameInput) nameInput.value = '';
                if (jabatanInput) jabatanInput.value = '';
                return;
            }

            if (globalKaryawanMap[val]) {
                if (nameInput) nameInput.value = globalKaryawanMap[val].nama || '';
                if (jabatanInput) jabatanInput.value = globalKaryawanMap[val].jabatan || '';
                return;
            }

            modalLookupTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`{{ route('barang.public.employee.lookup') }}?nrp=${encodeURIComponent(val)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.found && data.employee) {
                        if (nameInput) nameInput.value = data.employee.nama || '';
                        if (jabatanInput) jabatanInput.value = data.employee.jabatan || '';
                        globalKaryawanMap[val] = {
                            nama: data.employee.nama,
                            jabatan: data.employee.jabatan
                        };
                    }
                } catch (err) {}
            }, 300);
        });
    });

    function openItemManagerModal() {
        loadMasterItems();
        showModal('itemManagerModal', 'itemManagerBackdrop');
    }

    function openExportModal() {
        const today = new Date().toISOString().split('T')[0];
        const firstDayOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
        
        document.getElementById('export-start-date').value = firstDayOfMonth;
        document.getElementById('export-end-date').value = today;
        
        showModal('exportModal', 'exportBackdrop');
    }

    async function loadPublicConfigForModal() {
        try {
            const res = await fetch("{{ route('barang.config') }}");
            const data = await res.json();
            if (data.success && data.karyawan) {
                globalKaryawanMap = data.karyawan;
            }
        } catch (err) {
            console.error('Gagal memuat data karyawan untuk autofill:', err);
        }
    }

    async function loadDashboardData() {
        const period = document.getElementById('filter-dashboard-period')?.value || 'month';
        let anchorDate = document.getElementById('filter-dashboard-date')?.value;

        if (!anchorDate) {
            anchorDate = new Date().toISOString().split('T')[0];
            document.getElementById('filter-dashboard-date').value = anchorDate;
        }

        const btnFilter = document.getElementById('btn-apply-filter');
        if (btnFilter) {
            btnFilter.disabled = true;
            btnFilter.textContent = 'Memuat... ⏳';
        }

        try {
            const res = await fetch(`{{ route('barang.dashboard.data') }}?period=${period}&anchorDate=${anchorDate}`);
            const data = await res.json();
            if (data.success) {
                currentTransactions = data.transactions;
                document.getElementById('stat-total').textContent = new Intl.NumberFormat('id-ID').format(data.summary.total);
                document.getElementById('stat-employees').textContent = data.summary.employees;
                document.getElementById('stat-top-item').textContent = data.summary.topItem;
                document.getElementById('stat-average').textContent = String(data.summary.averagePerActiveDay).replace('.', ',');
                
                renderTable(currentTransactions);
                renderCharts(data.breakdown, data.trend);
            }
        } catch (err) {
            console.error('Gagal memuat dashboard:', err);
        } finally {
            if (btnFilter) {
                btnFilter.disabled = false;
                btnFilter.textContent = 'Terapkan Filter';
            }
        }
    }

    function renderCharts(breakdownData, trendData) {
        // 1. Horizontal Bar Chart (Top 6 Items)
        const topItems = (breakdownData || []).slice(0, 6);
        const topLabels = topItems.map(i => i.name);
        const topValues = topItems.map(i => i.value);

        const ctxTop = document.getElementById('topItemsChart').getContext('2d');
        if (topItemsChartInstance) topItemsChartInstance.destroy();

        topItemsChartInstance = new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'Jumlah Diambil',
                    data: topValues,
                    backgroundColor: '#0d9488',
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 9 } } },
                    y: { grid: { display: false }, ticks: { font: { size: 9.5, weight: 'bold' } } }
                }
            }
        });

        // 2. Line Chart (Tren Harian)
        const trendLabels = (trendData || []).map(t => t.label);
        const trendValues = (trendData || []).map(t => t.value);

        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        if (trendChartInstance) trendChartInstance.destroy();

        trendChartInstance = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Total Qty',
                    data: trendValues,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#0284c7'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 9 } }, beginAtZero: true }
                }
            }
        });
    }

    function renderTable(rows) {
        const tbody = document.getElementById('transaction-body');
        document.getElementById('transaction-count').textContent = rows.length + ' transaksi ditemukan pada periode ini.';
        
        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 24px; color: #64748b;">Belum ada riwayat pengambilan barang pada periode ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr>
                <td style="color: #64748b; font-weight: 600;">${r.date}</td>
                <td><strong style="color: #0f172a;">${r.name}</strong></td>
                <td><code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 700;">${r.nrp}</code></td>
                <td style="color: #64748b;">${r.jabatan}</td>
                <td><span class="chip-item">${r.item} (${r.qty})</span></td>
                <td><span style="font-weight: 600;">${r.lokasi}</span></td>
                <td>${r.hasPhoto ? `<button class="btn-photo" type="button" onclick="openPhoto('${r.id}')">📷 Lihat Foto</button>` : '<span style="color: #94a3b8; font-size: 10px;">Tidak ada</span>'}</td>
                <td style="text-align: right;">
                    <button type="button" class="btn-so-action" style="height: 26px; padding: 0 8px; font-size: 9.5px;" onclick="editTransaction('${r.id}')">Edit</button>
                    <button type="button" class="btn-so-action" style="height: 26px; padding: 0 8px; font-size: 9.5px; color: #dc2626; border-color: #fecaca; background: #fff5f5;" onclick="deleteTransaction('${r.id}')">Hapus</button>
                </td>
            </tr>
        `).join('');
    }

    function filterTable(query) {
        const q = query.toLowerCase();
        const filtered = currentTransactions.filter(r => 
            r.name.toLowerCase().includes(q) || 
            r.nrp.toLowerCase().includes(q) || 
            r.item.toLowerCase().includes(q) ||
            r.lokasi.toLowerCase().includes(q)
        );
        renderTable(filtered);
    }

    async function loadMasterItems() {
        try {
            const res = await fetch("{{ route('barang.admin.items') }}");
            masterItemsList = await res.json();
            
            const select = document.getElementById('crud-item');
            select.innerHTML = masterItemsList.filter(i => i.aktif).map(i => `<option value="${i.nama}">${i.nama}</option>`).join('');

            const tbody = document.getElementById('master-item-body');
            tbody.innerHTML = masterItemsList.map(i => `
                <tr>
                    <td><code>${i.kode}</code></td>
                    <td><input type="text" class="form-control-custom item-name-val" value="${i.nama}" data-code="${i.kode}" style="height: 28px;"></td>
                    <td style="text-align: center;"><input type="checkbox" class="item-active-val" ${i.aktif ? 'checked' : ''} data-code="${i.kode}"></td>
                    <td style="text-align: right; display: flex; gap: 4px; justify-content: flex-end;">
                        <button type="button" class="btn-so-action primary" style="height: 26px; padding: 0 8px; font-size: 9px;" onclick="updateItem('${i.kode}')">Simpan</button>
                        <button type="button" class="btn-so-action" style="height: 26px; padding: 0 8px; font-size: 9px; color: #dc2626; border-color: #fecaca;" onclick="deleteMasterItem('${i.kode}')">Hapus</button>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            console.error('Gagal memuat master barang:', err);
        }
    }

    async function handleAddMasterItem(e) {
        e.preventDefault();
        const nameInput = document.getElementById('new-item-name');
        try {
            const res = await fetch("{{ route('barang.admin.item.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name: nameInput.value })
            });
            const data = await res.json();
            if (data.success) {
                nameInput.value = '';
                loadMasterItems();
                loadDashboardData();
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function updateItem(code) {
        const row = document.querySelector(`[data-code="${code}"].item-name-val`).closest('tr');
        const nama = row.querySelector('.item-name-val').value;
        const aktif = row.querySelector('.item-active-val').checked;

        try {
            const res = await fetch(`/admin-all/stock-opname/admin/item/${code}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ nama, aktif })
            });
            const data = await res.json();
            if (data.success) {
                alert('Master barang berhasil diperbarui.');
                loadMasterItems();
                loadDashboardData();
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function deleteMasterItem(code) {
        if (!confirm('Apakah Anda yakin ingin menghapus barang ini dari master inventaris?')) return;

        try {
            const res = await fetch(`/admin-all/stock-opname/admin/item/${code}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                alert('Barang berhasil dihapus.');
                loadMasterItems();
                loadDashboardData();
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error('Gagal menghapus master barang:', err);
        }
    }

    function openTransactionModal(tx = null) {
        const isEdit = Boolean(tx);
        document.getElementById('modal-title-action').textContent = isEdit ? 'Edit Riwayat Pengambilan' : 'Tambah Riwayat Pengambilan';
        document.getElementById('crud-id').value = isEdit ? tx.id : '';
        document.getElementById('crud-date').value = isEdit ? tx.rawDate : '{{ date("Y-m-d") }}';
        document.getElementById('crud-nrp').value = isEdit ? tx.nrp : '';
        document.getElementById('crud-name').value = isEdit ? tx.name : '';
        document.getElementById('crud-jabatan').value = isEdit ? tx.jabatan : '';
        document.getElementById('crud-lokasi').value = isEdit ? tx.lokasi : '';
        
        if (isEdit) {
            document.getElementById('crud-item').value = tx.item;
            const parts = tx.qty.split(' ');
            document.getElementById('crud-qty').value = parts[0] || 1;
            document.getElementById('crud-unit').value = parts[1] || 'Pcs';
        } else {
            document.getElementById('crud-qty').value = 1;
            document.getElementById('crud-unit').value = 'Pcs';
        }

        showModal('transactionModal', 'transactionBackdrop');
    }

    function editTransaction(id) {
        const tx = currentTransactions.find(t => t.id === id);
        if (tx) openTransactionModal(tx);
    }

    async function handleTransactionSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('crud-id').value;
        const payload = {
            name: document.getElementById('crud-name').value,
            nrp: document.getElementById('crud-nrp').value,
            jabatan: document.getElementById('crud-jabatan').value,
            date: document.getElementById('crud-date').value,
            item: document.getElementById('crud-item').value,
            qty: document.getElementById('crud-qty').value,
            unit: document.getElementById('crud-unit').value,
            lokasi: document.getElementById('crud-lokasi').value,
        };

        const url = id ? `/admin-all/stock-opname/admin/transaction/${id}` : "{{ route('barang.admin.transaction.store') }}";
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                closeModal('transactionModal');
                loadDashboardData();
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function deleteTransaction(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus data riwayat ini?')) return;
        try {
            const res = await fetch(`/admin-all/stock-opname/admin/transaction/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await res.json();
            if (data.success) loadDashboardData();
        } catch (err) {
            console.error(err);
        }
    }

    async function openPhoto(id) {
        showModal('photoModal', 'photoBackdrop');
        const loader = document.getElementById('photo-loader');
        const img = document.getElementById('modal-photo-img');
        loader.style.display = 'block';
        img.style.display = 'none';

        try {
            const res = await fetch(`/admin-all/stock-opname/admin/photo/${id}`);
            const data = await res.json();
            if (data.success) {
                img.src = data.dataUrl;
                loader.style.display = 'none';
                img.style.display = 'block';
            } else {
                loader.textContent = data.message || 'Gagal memuat foto.';
            }
        } catch (err) {
            loader.textContent = 'Gagal memuat foto.';
        }
    }

    function showModal(modalId, backdropId) {
        document.getElementById(backdropId).style.display = 'block';
        document.getElementById(modalId).style.display = 'grid';
    }

    function closeModal(modalId) {
        if (modalId === 'transactionModal') document.getElementById('transactionBackdrop').style.display = 'none';
        if (modalId === 'itemManagerModal') document.getElementById('itemManagerBackdrop').style.display = 'none';
        if (modalId === 'photoModal') document.getElementById('photoBackdrop').style.display = 'none';
        if (modalId === 'exportModal') document.getElementById('exportBackdrop').style.display = 'none';
        document.getElementById(modalId).style.display = 'none';
    }
</script>
@endpush
@endsection