@extends('admin-all.layout')

@section('admin-content')
<style>
    .cc-page-title { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 15px; }
    .cc-page-title h1 { margin: 0; color: #051d39; font-size: clamp(21px, 2vw, 28px); letter-spacing: -.03em; }
    .cc-page-title p { margin: 3px 0 0; color: #5d6c7c; font-size: 9px; line-height: 1.4; }
    .cc-title-actions { display: flex; flex-wrap: wrap; gap: 7px; justify-content: flex-end; }
    
    .cc-stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 9px; margin-bottom: 12px; }
    .cc-stat-box { position: relative; padding: 12px 14px 11px; border: 1px solid #d9e0e7; border-radius: 10px; background: #fff; box-shadow: 0 5px 18px rgba(31, 47, 65, .05); }
    .cc-stat-box small { display: block; color: #617083; font-size: 7px; font-weight: 900; letter-spacing: .05em; text-transform: uppercase; }
    .cc-stat-box strong { display: block; margin-top: 3px; color: #091f3c; font-size: 22px; font-weight: 900; line-height: 1; }
    .cc-stat-box span { display: block; margin-top: 4px; color: #68778a; font-size: 7px; }

    .cc-panel { border: 1px solid #d9e0e7; border-radius: 10px; background: #fff; box-shadow: 0 5px 18px rgba(31, 47, 65, .05); margin-bottom: 12px; overflow: hidden; }
    .cc-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-bottom: 1px solid #e0e6ec; background: #fafbfc; }
    .cc-panel-head h2 { margin: 0; font-size: 12px; font-weight: 800; color: #0d1f33; }
    .cc-panel-head p { margin: 2px 0 0; color: #68778a; font-size: 7px; }
    .cc-panel-body { padding: 10px 11px; }

    .table-container { width: 100%; overflow-x: auto; }
    .aa-custom-table { width: 100%; border-collapse: collapse; font-size: 9px; white-space: nowrap; }
    .aa-custom-table th, .aa-custom-table td { padding: 9px 11px; text-align: left; border-bottom: 1px solid #edf0f4; }
    .aa-custom-table th { color: #617083; background: #f8fafc; font-size: 8px; text-transform: uppercase; letter-spacing: .04em; }
    .aa-custom-table td { color: #334155; }
    .aa-custom-table td strong { color: #0d1f33; font-weight: 800; }
    .aa-custom-table tbody tr:hover { background: #f0fdfa; }

    .item-chip { display: inline-flex; padding: 3px 6px; border-radius: 6px; color: #115e59; background: #f0fdfa; font-weight: 700; font-size: 8px; border: 1px solid #ccfbf1; }
    .photo-link { padding: 3px 6px; display: inline-flex; align-items: center; gap: 4px; border: 0; border-radius: 6px; color: #0f78ef; background: #eff6ff; font-size: 8px; font-weight: 800; text-decoration: none; cursor: pointer; }
    .photo-link:hover { background: #dbeafe; }

    /* Custom Modal CSS murni (Tanpa Bootstrap) */
    .modal-backdrop-custom { position: fixed; inset: 0; background: rgba(15, 23, 42, .6); backdrop-filter: blur(3px); z-index: 1040; display: none; }
    .modal-custom { position: fixed; inset: 0; z-index: 1050; display: none; place-items: center; padding: 15px; overflow-y: auto; }
    .modal-card-custom { position: relative; width: min(560px, 100%); padding: 20px; border-radius: 10px; background: #fff; box-shadow: 0 20px 40px rgba(15, 23, 42, .2); }
    .modal-card-custom h3 { margin: 0 0 4px; font-size: 14px; color: #0d1f33; }
    .modal-card-custom p { margin: 0 0 14px; font-size: 8px; color: #617083; }
    
    .form-group-custom { margin-bottom: 10px; }
    .form-group-custom label { display: block; margin-bottom: 4px; color: #334155; font-size: 8px; font-weight: 800; text-transform: uppercase; }
    .form-control-custom { width: 100%; height: 34px; padding: 6px 9px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 9px; outline: 0; background: #fff; color: #1e293b; }
    .form-control-custom:focus { border-color: #0f78ef; box-shadow: 0 0 0 3px rgba(15, 120, 239, .1); }
    .form-control-custom:read-only { background: #f8fafc; color: #64748b; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 7px; margin-top: 16px; }

    @media (max-width: 950px) { .cc-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 600px) {
        .cc-stat-grid { grid-template-columns: 1fr; }
        .cc-page-title { flex-direction: column; align-items: stretch; }
        .cc-title-actions { justify-content: flex-start; }
    }
</style>

<div class="cc-page-title">
    <div>
        <h1>Stock Opname & Pengambilan Barang</h1>
        <p>Pencatatan inventaris departemen produksi site Bukit Asam secara cepat dan terstruktur.</p>
    </div>
    <div class="cc-title-actions">
        <button type="button" class="aa-action-button" onclick="openItemManagerModal()">
            ⚙️ Kelola Master Barang
        </button>
        <button type="button" class="aa-action-button primary" onclick="openTransactionModal()">
            + Tambah Riwayat
        </button>
    </div>
</div>

<!-- Kotak Statistik Ringkas -->
<div class="cc-stat-grid">
    <div class="cc-stat-box">
        <small>Total Barang Keluar</small>
        <strong id="stat-total" style="color: #0f766e;">0</strong>
        <span>Transaksi pada periode ini</span>
    </div>
    <div class="cc-stat-box">
        <small>Karyawan Mengambil</small>
        <strong id="stat-employees" style="color: #0f78ef;">0</strong>
        <span>NRP unik tercatat</span>
    </div>
    <div class="cc-stat-box">
        <small>Barang Terbanyak</small>
        <strong id="stat-top-item" style="font-size: 14px; margin-top: 8px; color: #d97706;">-</strong>
        <span>Paling sering diambil</span>
    </div>
    <div class="cc-stat-box">
        <small>Rata-rata Hari Aktif</small>
        <strong id="stat-average" style="color: #7c3aed;">0</strong>
        <span>Barang per hari aktif</span>
    </div>
</div>

<!-- Panel Tabel Riwayat Pengambilan -->
<section class="cc-panel">
    <div class="cc-panel-head">
        <div>
            <h2>Riwayat Pengambilan Barang</h2>
            <p id="transaction-count">0 transaksi ditemukan.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 7px;">
            <input type="search" id="table-search" class="form-control-custom" placeholder="Cari nama, NRP, barang..." style="width: 200px; height: 28px;">
            <button type="button" class="aa-action-button" style="min-height: 28px;" onclick="loadDashboardData()">Perbarui</button>
        </div>
    </div>
    <div class="cc-panel-body" style="padding: 0;">
        <div class="table-container">
            <table class="aa-custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>NRP</th>
                        <th>Jabatan</th>
                        <th>Barang</th>
                        <th>Lokasi</th>
                        <th>Foto</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="transaction-body">
                    <tr><td colspan="8" style="text-align: center; padding: 20px; color: #68778a;">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- MODAL TAMBAH/EDIT TRANSAKSI -->
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
                <button type="button" class="aa-action-button" onclick="closeModal('transactionModal')">Batal</button>
                <button type="submit" class="aa-action-button primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL KELOLA MASTER BARANG -->
<div class="modal-backdrop-custom" id="itemManagerBackdrop"></div>
<div class="modal-custom" id="itemManagerModal">
    <div class="modal-card-custom" style="width: min(600px, 100%);">
        <h3>Kelola Master Barang</h3>
        <p>Tambah atau sesuaikan status aktif jenis barang inventaris.</p>
        <form id="add-item-form" onsubmit="handleAddMasterItem(event)" style="display: flex; gap: 7px; margin-bottom: 12px;">
            <input type="text" id="new-item-name" class="form-control-custom" placeholder="Nama barang baru..." required style="flex: 1;">
            <button type="submit" class="aa-action-button primary" style="min-height: 34px;">Tambah</button>
        </form>
        <div class="table-container" style="max-height: 280px; overflow-y: auto;">
            <table class="aa-custom-table">
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
            <button type="button" class="aa-action-button" onclick="closeModal('itemManagerModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL LIHAT FOTO -->
<div class="modal-backdrop-custom" id="photoBackdrop"></div>
<div class="modal-custom" id="photoModal">
    <div class="modal-card-custom" style="text-align: center; width: min(450px, 100%);">
        <h3>Foto Bukti Pengambilan</h3>
        <p id="photo-loader" style="padding: 30px 0;">Memuat foto...</p>
        <img id="modal-photo-img" style="display: none; max-width: 100%; max-height: 60vh; border-radius: 8px; margin: 10px auto 14px; object-fit: contain;" alt="Foto Bukti">
        <div class="modal-actions" style="justify-content: center;">
            <button type="button" class="aa-action-button" onclick="closeModal('photoModal')">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentTransactions = [];
    let masterItemsList = [];
    let globalKaryawanMap = {};

    document.addEventListener('DOMContentLoaded', function () {
        loadDashboardData();
        loadMasterItems();
        loadPublicConfigForModal();

        document.getElementById('table-search')?.addEventListener('input', function(e) {
            filterTable(e.target.value);
        });

        // Event listener autofill NRP secara real-time pada modal tambah/edit riwayat
        document.getElementById('crud-nrp')?.addEventListener('input', function(e) {
            const val = e.target.value.trim().toUpperCase();
            const nameInput = document.getElementById('crud-name');
            const jabatanInput = document.getElementById('crud-jabatan');
            
            if (val.length >= 2 && globalKaryawanMap[val]) {
                if (nameInput) nameInput.value = globalKaryawanMap[val].nama;
                if (jabatanInput) jabatanInput.value = globalKaryawanMap[val].jabatan;
            } else {
                if (nameInput) nameInput.value = '';
                if (jabatanInput) jabatanInput.value = '';
            }
        });
    });

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
        try {
            const res = await fetch("{{ route('barang.dashboard.data') }}");
            const data = await res.json();
            if (data.success) {
                currentTransactions = data.transactions;
                document.getElementById('stat-total').textContent = new Intl.NumberFormat('id-ID').format(data.summary.total);
                document.getElementById('stat-employees').textContent = data.summary.employees;
                document.getElementById('stat-top-item').textContent = data.summary.topItem;
                document.getElementById('stat-average').textContent = String(data.summary.averagePerActiveDay).replace('.', ',');
                renderTable(currentTransactions);
            }
        } catch (err) {
            console.error('Gagal memuat dashboard:', err);
        }
    }

    function renderTable(rows) {
        const tbody = document.getElementById('transaction-body');
        document.getElementById('transaction-count').textContent = rows.length + ' transaksi ditemukan.';
        
        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 20px; color: #68778a;">Belum ada data transaksi.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr>
                <td>${r.date}</td>
                <td><strong>${r.name}</strong></td>
                <td>${r.nrp}</td>
                <td style="color: #68778a;">${r.jabatan}</td>
                <td><span class="item-chip">${r.item} (${r.qty})</span></td>
                <td>${r.lokasi}</td>
                <td>${r.hasPhoto ? `<button class="photo-link" type="button" onclick="openPhoto('${r.id}')">Lihat Foto</button>` : '<span style="color: #94a3b8;">Tidak ada</span>'}</td>
                <td style="text-align: right;">
                    <button type="button" class="aa-action-button" style="min-height: 24px; padding: 3px 6px; font-size: 8px;" onclick="editTransaction('${r.id}')">Edit</button>
                    <button type="button" class="aa-action-button" style="min-height: 24px; padding: 3px 6px; font-size: 8px; color: #dc2626; border-color: #fecaca;" onclick="deleteTransaction('${r.id}')">Hapus</button>
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
                    <td>${i.kode}</td>
                    <td><input type="text" class="form-control-custom item-name-val" value="${i.nama}" data-code="${i.kode}" style="height: 26px;"></td>
                    <td style="text-align: center;"><input type="checkbox" class="item-active-val" ${i.aktif ? 'checked' : ''} data-code="${i.kode}"></td>
                    <td style="text-align: right;">
                        <button type="button" class="aa-action-button primary" style="min-height: 24px; padding: 3px 8px; font-size: 8px;" onclick="updateItem('${i.kode}')">Simpan</button>
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
        document.getElementById(modalId).style.display = 'none';
    }
</script>
@endpush
@endsection