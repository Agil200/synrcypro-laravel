@extends('admin-all.layout')

@section('admin-content')
<style>
    /* UI yang Ditingkatkan: Lebih Bersih, Modern, & Ramah Pengguna */
    .so-wrapper { display: flex; gap: 35px; padding: 15px; max-width: 1200px; margin: 0 auto; font-family: 'Segoe UI', system-ui, sans-serif; align-items: flex-start; }
    .so-left { flex: 0.9; position: sticky; top: 20px; }
    .so-right { flex: 1.4; }
    
    .so-tag { color: #0d9488; font-weight: 900; font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 15px; display: block; }
    .so-title { font-size: 36px; font-weight: 900; color: #0f172a; line-height: 1.15; margin-bottom: 15px; letter-spacing: -1px; }
    .so-desc { color: #475569; font-size: 12.5px; line-height: 1.6; margin-bottom: 30px; }
    
    .so-feature { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 18px; }
    .so-feature-icon { background: #f0fdfa; color: #0d9488; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; border: 1px solid #ccfbf1; }
    .so-feature-text h4 { margin: 0 0 2px; font-size: 12px; color: #1e293b; font-weight: 800; }
    .so-feature-text p { margin: 0; font-size: 11px; color: #64748b; }
    
    .so-alert { background: #f0fdfa; border: 1px solid #ccfbf1; padding: 12px 16px; border-radius: 10px; display: flex; align-items: center; gap: 10px; color: #0f766e; font-size: 11px; font-weight: 800; margin-top: 25px; }
    
    /* Kartu Formulir Utama */
    .so-card { background: #ffffff; border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); border: 1px solid #e2e8f0; }
    .so-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .so-card-tag { font-size: 9px; font-weight: 900; color: #0d9488; text-transform: uppercase; letter-spacing: 1px; background: #f0fdfa; padding: 4px 8px; border-radius: 6px; }
    .so-status { font-size: 10px; font-weight: 800; color: #0f766e; display: flex; align-items: center; gap: 6px; }
    .so-status::before { content: ''; width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: inline-block; box-shadow: 0 0 0 3px #d1fae5; }
    
    .so-card h2 { font-size: 20px; font-weight: 900; color: #0f172a; margin: 0 0 4px; }
    .so-card p { font-size: 11.5px; color: #64748b; margin: 0 0 24px; }
    .so-card p span { color: #e11d48; font-weight: bold; }
    
    .so-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
    .so-form-group { display: flex; flex-direction: column; gap: 6px; }
    .so-form-group.full { grid-column: 1 / -1; }
    .so-label { font-size: 11px; font-weight: 800; color: #334155; }
    .so-label span { color: #e11d48; }
    .so-input { padding: 11px 13px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 12px; color: #0f172a; outline: none; transition: all 0.2s; background: #ffffff; width: 100%; }
    .so-input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px #ccfbf1; background: #fff; }
    .so-input:read-only { background: #f8fafc; color: #64748b; border-color: #e2e8f0; cursor: not-allowed; }
    
    /* Baris Input Barang Dinamis (Repeater) */
    .so-repeater-header { display: flex; justify-content: space-between; align-items: center; margin: 20px 0 12px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 10px; }
    .so-repeater-title { font-size: 12px; font-weight: 800; color: #0f172a; margin: 0; }
    .so-btn-add { background: #f8fafc; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 7px; font-size: 10.5px; font-weight: 800; color: #334155; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px; }
    .so-btn-add:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }
    
    .so-item-row { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: start; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; }
    .so-btn-remove { background: #fff1f2; border: 1px solid #fecdd3; color: #e11d48; width: 38px; height: 38px; border-radius: 8px; display: flex; justify-content: center; align-items: center; cursor: pointer; margin-top: 20px; transition: all 0.2s; }
    .so-btn-remove:hover { background: #ffe4e6; }
    
    /* Box Upload Foto */
    .so-photo-box { border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 12px; padding: 20px; margin-bottom: 25px; transition: all 0.2s; }
    .so-photo-box:hover { border-color: #94a3b8; }
    .so-photo-box-inner { display: flex; gap: 15px; align-items: center; }
    .so-photo-icon { background: #ffffff; border: 1px solid #e2e8f0; width: 42px; height: 42px; border-radius: 10px; display: flex; justify-content: center; align-items: center; color: #0d9488; font-size: 18px; }
    .so-photo-text h5 { margin: 0 0 3px; font-size: 12px; font-weight: 800; color: #0f172a; }
    .so-photo-text p { margin: 0; font-size: 10.5px; color: #64748b; }
    .so-photo-actions { display: flex; gap: 10px; margin-top: 15px; }
    .so-btn-camera { background: #f0fdfa; border: 1px solid #ccfbf1; color: #0d9488; padding: 9px 15px; border-radius: 7px; font-size: 11px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
    .so-btn-camera:hover { background: #ccfbf1; }
    
    /* Tombol Submit */
    .so-btn-submit { width: 100%; background: #0f766e; color: #ffffff; border: none; padding: 14px; border-radius: 10px; font-size: 13px; font-weight: 900; cursor: pointer; transition: all 0.2s; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(15, 118, 110, 0.25); }
    .so-btn-submit:hover { background: #115e59; transform: translateY(-1px); }
    .so-btn-submit:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }

    #preview-container { margin-top: 15px; display: none; position: relative; width: max-content; }
    #preview-img { max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; border: 1px solid #e2e8f0; padding: 3px; background: #fff; }
    #remove-photo { position: absolute; top: -8px; right: -8px; background: #e11d48; color: white; border: 2px solid #fff; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 11px; font-weight: bold; }

    @media (max-width: 950px) {
        .so-wrapper { flex-direction: column; }
        .so-left { position: static; margin-bottom: 20px; }
        .so-grid { grid-template-columns: 1fr; gap: 12px; }
        .so-item-row { grid-template-columns: 1fr 1fr; }
        .so-item-row .so-form-group:first-child { grid-column: 1 / -1; }
        .so-btn-remove { margin-top: 0; height: 38px; align-self: end; }
    }
</style>

<div class="so-wrapper">
    <!-- KOLOM KIRI: INFO & PANDUAN -->
    <div class="so-left">
        <span class="so-tag">PPA Production Inventory System</span>
        <h1 class="so-title">Catat pengambilan Barang dengan cepat & rapi.</h1>
        <p class="so-desc">Masukkan NRP untuk mengisi data secara otomatis. Anda dapat mencatat beberapa barang sekaligus dalam satu kali pengiriman.</p>
        
        <div class="so-feature">
            <div class="so-feature-icon">✓</div>
            <div class="so-feature-text">
                <h4>Otomatis & Real-time</h4>
                <p>Data langsung tersimpan aman ke database server.</p>
            </div>
        </div>
        <div class="so-feature">
            <div class="so-feature-icon">⚡</div>
            <div class="so-feature-text">
                <h4>Multi-Item Sekaligus</h4>
                <p>Tambah beberapa jenis barang dalam satu form.</p>
            </div>
        </div>
        <div class="so-feature">
            <div class="so-feature-icon">📷</div>
            <div class="so-feature-text">
                <h4>Foto Bukti (Opsional)</h4>
                <p>Lampirkan foto dari kamera atau galeri perangkat.</p>
            </div>
        </div>
        
        <div class="so-alert">
            🛡️ Foto bukti hanya dapat diakses melalui dashboard admin.
        </div>
    </div>

    <!-- KOLOM KANAN: FORMULIR -->
    <div class="so-right">
        <div class="so-card">
            <div class="so-card-header">
                <span class="so-card-tag">FORMULIR PENGAMBILAN</span>
                <span class="so-status">Siap digunakan</span>
            </div>
            
            <h2>Form Pengambilan Barang</h2>
            <p>Pastikan kolom bertanda <span>*</span> diisi dengan benar.</p>
            
            <form id="pickup-form" onsubmit="submitForm(event)">
                <div class="so-grid">
                    <div class="so-form-group">
                        <label class="so-label">NRP <span>*</span></label>
                        <input type="text" id="nrp" class="so-input" placeholder="Contoh: 12345678" required autocomplete="off">
                    </div>
                    <div class="so-form-group">
                        <label class="so-label">Nama Lengkap <span>*</span></label>
                        <input type="text" id="name" class="so-input" placeholder="Terisi otomatis dari NRP" required>
                    </div>
                    <div class="so-form-group">
                        <label class="so-label">Jabatan</label>
                        <input type="text" id="jabatan" class="so-input" placeholder="Terisi otomatis dari NRP" readonly>
                    </div>
                    <div class="so-form-group">
                        <label class="so-label">Lokasi Tujuan <span>*</span></label>
                        <input type="text" id="lokasi" class="so-input" placeholder="Contoh: PIT 1, Workshop..." required>
                    </div>
                    <div class="so-form-group full">
                        <label class="so-label">Tanggal Pengambilan <span>*</span></label>
                        <input type="date" id="date" class="so-input" required>
                    </div>
                </div>

                <div class="so-repeater-header">
                    <h3 class="so-repeater-title">Daftar Barang & Jumlah <span>*</span></h3>
                    <button type="button" class="so-btn-add" onclick="addItemRow()">+ Tambah Barang</button>
                </div>
                
                <div id="items-container">
                    <!-- Baris barang dimuat dinamis via JS -->
                </div>

                <div class="so-repeater-header" style="margin-top: 25px;">
                    <h3 class="so-repeater-title">Foto Bukti <span style="color:#94a3b8; font-weight:normal; font-size:10px;">(Opsional)</span></h3>
                </div>
                
                <div class="so-photo-box">
                    <div class="so-photo-box-inner">
                        <div class="so-photo-icon">📷</div>
                        <div class="so-photo-text">
                            <h5>Unggah atau Ambil Foto</h5>
                            <p>Format JPG, PNG, atau WEBP • Maks. 5 MB</p>
                        </div>
                    </div>
                    <div class="so-photo-actions">
                        <button type="button" class="so-btn-camera" onclick="document.getElementById('photo-input').click()">
                            📷 Buka Kamera / Galeri
                        </button>
                        <input type="file" id="photo-input" accept="image/*" capture="environment" style="display: none;" onchange="handlePhotoSelect(event)">
                    </div>
                    
                    <div id="preview-container">
                        <img id="preview-img" src="" alt="Preview">
                        <button type="button" id="remove-photo" onclick="removePhoto()" title="Hapus Foto">✕</button>
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="so-btn-submit">
                    <span>Simpan Pengambilan</span>
                    <span>→</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let appConfig = { items: [], karyawan: {}, maxPhotoMb: 5 };
    let photoBase64 = null;

    document.addEventListener('DOMContentLoaded', async () => {
        document.getElementById('date').valueAsDate = new Date();
        
        try {
            const res = await fetch("{{ route('barang.config') }}");
            const data = await res.json();
            if (data.success) {
                appConfig = data;
            }
        } catch (e) {
            console.error("Gagal memuat konfigurasi.", e);
        }

        // Langsung tampilkan 1 baris input barang pertama
        addItemRow();

        // Fitur Autofill NRP otomatis ke Nama dan Jabatan
        // Fitur Autofill NRP otomatis ke Nama dan Jabatan
        document.getElementById('nrp').addEventListener('input', function(e) {
            const val = e.target.value.trim().toUpperCase();
            const nameInput = document.getElementById('name');
            const jabatanInput = document.getElementById('jabatan');

            if (val.length >= 2 && appConfig.karyawan && appConfig.karyawan[val]) {
                nameInput.value = appConfig.karyawan[val].nama;
                jabatanInput.value = appConfig.karyawan[val].jabatan;
            } else {
                nameInput.value = '';
                jabatanInput.value = '';
            }
        });

    function addItemRow() {
        const container = document.getElementById('items-container');
        const row = document.createElement('div');
        row.className = 'so-item-row';
        
        let options = '<option value="">Pilih barang...</option>';
        const itemList = (appConfig.items && appConfig.items.length > 0) ? appConfig.items : [
            'Pulpen Gel', 'Pulpen Pilot', 'Spidol Putih Permanen',
            'Spidol Hitam Permanen', 'Spidol Hitam Whiteboard', 'Buku Saku'
        ];
        
        itemList.forEach(item => { 
            options += `<option value="${item}">${item}</option>`; 
        });

        row.innerHTML = `
            <div class="so-form-group">
                <label class="so-label">Barang <span>*</span></label>
                <select class="so-input item-select" required>${options}</select>
            </div>
            <div class="so-form-group">
                <label class="so-label">Jumlah <span>*</span></label>
                <input type="number" class="so-input item-qty" value="1" min="1" required>
            </div>
            <div class="so-form-group">
                <label class="so-label">Satuan <span>*</span></label>
                <select class="so-input item-unit" required>
                    <option value="Pcs">Pcs</option>
                    <option value="Box">Box</option>
                    <option value="Buah">Buah</option>
                    <option value="Bungkus">Bungkus</option>
                    <option value="Rim">Rim</option>
                    <option value="Botol">Botol</option>
                    <option value="Unit">Unit</option>
                </select>
            </div>
            <button type="button" class="so-btn-remove" onclick="this.parentElement.remove()" title="Hapus baris">🗑️</button>
        `;
        container.appendChild(row);
    }

    function handlePhotoSelect(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > appConfig.maxPhotoMb * 1024 * 1024) {
            alert(`Ukuran foto terlalu besar. Maksimal ${appConfig.maxPhotoMb} MB.`);
            e.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            photoBase64 = event.target.result;
            document.getElementById('preview-img').src = photoBase64;
            document.getElementById('preview-container').style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
    }

    function removePhoto() {
        photoBase64 = null;
        document.getElementById('photo-input').value = '';
        document.getElementById('preview-container').style.display = 'none';
    }

    async function submitForm(e) {
        e.preventDefault();
        
        const items = [];
        const rows = document.querySelectorAll('.so-item-row');
        if (rows.length === 0) {
            alert('Minimal pilih 1 barang.');
            return;
        }

        rows.forEach(row => {
            items.push({
                item: row.querySelector('.item-select').value,
                qty: parseInt(row.querySelector('.item-qty').value),
                unit: row.querySelector('.item-unit').value
            });
        });

        const payload = {
            nrp: document.getElementById('nrp').value,
            name: document.getElementById('name').value,
            jabatan: document.getElementById('jabatan').value,
            lokasi: document.getElementById('lokasi').value,
            date: document.getElementById('date').value,
            items: items,
            photoDataUrl: photoBase64
        };

        const btn = document.getElementById('btn-submit');
        btn.disabled = true;
        btn.innerHTML = '<span>Menyimpan data... ⏳</span><span></span>';

        try {
            const res = await fetch("{{ route('barang.pickup.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.success) {
                alert('Berhasil! Data pengambilan telah tersimpan di sistem.');
                document.getElementById('pickup-form').reset();
                document.getElementById('date').valueAsDate = new Date();
                document.getElementById('items-container').innerHTML = '';
                removePhoto();
                addItemRow();
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
            console.error(err);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan Pengambilan</span><span>→</span>';
        }
    }
</script>
@endpush
@endsection