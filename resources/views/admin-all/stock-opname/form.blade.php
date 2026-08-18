<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Pengambilan Barang — SYNRGYPRO</title>
    
    <!-- Favicon Logo SYNRGYPRO -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/synrgypro-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/synrgypro-logo.png') }}">

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #f0fdfa;
            --primary-border: #ccfbf1;
            --navy-dark: #0f172a;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-400: #94a3b8;
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-md: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
            --shadow-lg: 0 20px 35px -10px rgba(15, 23, 42, 0.07);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-page);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--navy-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 50px;
        }

        /* Top Navigation Bar */
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.95);
        }

        .top-navbar-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--slate-600);
            font-weight: 700;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-back-home:hover {
            color: var(--primary);
            border-color: var(--primary-border);
            background: var(--primary-light);
            transform: translateX(-2px);
        }

        .brand-badge {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-badge img {
            height: 22px;
            object-fit: contain;
        }

        .brand-badge span {
            font-size: 12px;
            font-weight: 800;
            color: var(--slate-600);
            letter-spacing: 0.3px;
        }

        /* Main Workspace Container */
        .main-container {
            max-width: 1240px;
            margin: 36px auto 0;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 48px;
            align-items: flex-start;
        }

        /* Left Side: Brand & Overview */
        .brand-sidebar {
            position: sticky;
            top: 90px;
            display: flex;
            flex-direction: column;
        }

        .brand-logo-card {
            margin-bottom: 20px;
        }

        .brand-logo-card img {
            height: 42px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .system-tag {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary-border);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .system-heading {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.25;
            color: var(--navy-dark);
            letter-spacing: -0.8px;
            margin-bottom: 14px;
        }

        .system-subheading {
            color: var(--slate-600);
            font-size: 13.5px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .feature-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: #ffffff;
            padding: 15px 18px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--primary-light);
            color: var(--primary-dark);
            border: 1px solid var(--primary-border);
            display: grid;
            place-items: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .feature-text h4 {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy-dark);
            margin-bottom: 2px;
        }

        .feature-text p {
            font-size: 11.5px;
            color: var(--slate-600);
            line-height: 1.45;
        }

        .security-badge {
            background: #ffffff;
            border: 1px solid #e0e7ff;
            padding: 12px 16px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11.5px;
            color: #3730a3;
            font-weight: 600;
        }

        /* Right Side: Form Card */
        .form-card {
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 36px;
            box-shadow: var(--shadow-lg);
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 18px;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-title-group h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--navy-dark);
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .form-title-group p {
            font-size: 12.5px;
            color: var(--slate-600);
        }

        .required-star {
            color: #ef4444;
            font-weight: 700;
        }

        /* Form Controls */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-700);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 13px;
            color: var(--navy-dark);
            background: #ffffff;
            font-family: inherit;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.12);
        }

        .form-control:read-only {
            background: #f8fafc;
            color: var(--slate-600);
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        /* Lookup Status Notification */
        .lookup-status-box {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 700;
            display: none;
            align-items: center;
            gap: 8px;
        }

        .lookup-status-box.info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            display: flex;
        }

        .lookup-status-box.success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            display: flex;
        }

        .lookup-status-box.error {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
            display: flex;
        }

        /* Dynamic Item Repeater */
        .repeater-section {
            margin-top: 6px;
            padding-top: 20px;
            border-top: 1px dashed var(--border-color);
        }

        .repeater-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .repeater-title {
            font-size: 12.5px;
            font-weight: 800;
            color: var(--navy-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-add-item {
            background: var(--primary-light);
            border: 1.5px solid var(--primary-border);
            color: var(--primary-dark);
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-add-item:hover {
            background: #ccfbf1;
            border-color: #99f6e4;
        }

        .item-row {
            display: grid;
            grid-template-columns: 2.2fr 1fr 1fr auto;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 12px;
            align-items: end;
            animation: fadeIn 0.25s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn-remove-row {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #ffffff;
            border: 1.5px solid #fecdd3;
            color: #e11d48;
            display: grid;
            place-items: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-remove-row:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        /* Professional Photo Upload Box */
        .photo-card {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 18px 20px;
            margin-top: 18px;
            margin-bottom: 26px;
            transition: all 0.2s ease;
        }

        .photo-card:hover {
            border-color: var(--primary);
            background: #f0fdfa;
        }

        .photo-card-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .photo-icon-group {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .photo-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            display: grid;
            place-items: center;
            font-size: 18px;
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .photo-text h5 {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy-dark);
            margin-bottom: 2px;
        }

        .photo-text p {
            font-size: 11px;
            color: var(--slate-600);
        }

        .btn-upload-trigger {
            background: #ffffff;
            border: 1.5px solid var(--border-color);
            color: var(--slate-600);
            padding: 9px 16px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }

        .btn-upload-trigger:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
            background: var(--primary-light);
        }

        #preview-container {
            margin-top: 16px;
            position: relative;
            width: max-content;
            display: none;
        }

        #preview-img {
            max-height: 180px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            padding: 4px;
            background: #ffffff;
            box-shadow: var(--shadow-md);
        }

        #remove-photo {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e11d48;
            color: #ffffff;
            border: 2px solid #ffffff;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }

        /* Submit Button */
        .btn-submit-main {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 800;
            font-family: inherit;
            letter-spacing: 0.3px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
            box-shadow: 0 10px 20px -5px rgba(13, 148, 136, 0.4);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-submit-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 25px -5px rgba(13, 148, 136, 0.5);
        }

        .btn-submit-main:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
                gap: 28px;
            }
            .brand-sidebar {
                position: static;
            }
            .form-card {
                padding: 28px;
            }
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .item-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .btn-remove-row {
                width: 100%;
                margin-top: 4px;
            }
            .photo-card-inner {
                flex-direction: column;
                align-items: flex-start;
            }
            .btn-upload-trigger {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- Professional Header -->
    <nav class="top-navbar">
        <div class="top-navbar-container">
            <a href="{{ route('login') }}" class="btn-back-home">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Kembali ke Login
            </a>
            <div class="brand-badge">
                <img src="{{ asset('assets/images/synrgypro-logo.png') }}" alt="SYNRGYPRO">
                <span>PPA Production System</span>
            </div>
        </div>
    </nav>

    <!-- Main Workspace -->
    <div class="main-container">
        
        <!-- Left Column: Branding & Overview -->
        <aside class="brand-sidebar">
            <div class="brand-logo-card">
                <img src="{{ asset('assets/images/synrgypro-logo.png') }}" alt="SYNRGYPRO Logo">
            </div>

            <span class="system-tag">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                Inventory Logistics
            </span>

            <h1 class="system-heading">Pengambilan Barang Produksi.</h1>
            <p class="system-subheading">Catat inventaris barang operasional departemen produksi secara digital, terstruktur, dan tervalidasi secara instan.</p>
            
            <div class="feature-stack">
                <div class="feature-item">
                    <div class="feature-icon">⚡</div>
                    <div class="feature-text">
                        <h4>Autofill NRP Otomatis</h4>
                        <p>Ketik NRP Anda dan nama serta jabatan akan terisi secara otomatis.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📦</div>
                    <div class="feature-text">
                        <h4>Multi-Item Sekaligus</h4>
                        <p>Pengambilan beberapa jenis barang sekaligus dalam satu form kirim.</p>
                    </div>
                </div>
            </div>

            <div class="security-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Data langsung terverifikasi ke server database.
            </div>
        </aside>

        <!-- Right Column: Form Panel -->
        <main class="form-card">
            <div class="form-header">
                <div class="form-title-group">
                    <h2>Form Pengambilan</h2>
                    <p>Lengkapi kolom formulir bertanda <span class="required-star">*</span> berikut.</p>
                </div>
            </div>

            <form id="pickup-form" onsubmit="submitForm(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">NRP Karyawan <span class="required-star">*</span></label>
                        <input type="text" id="nrp" class="form-control" placeholder="Ketik NRP (contoh: 22001209)" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="required-star">*</span></label>
                        <input type="text" id="name" class="form-control" placeholder="Terisi otomatis dari NRP" readonly required>
                    </div>

                    <!-- Notifikasi Status Pencarian NRP -->
                    <div class="form-group full">
                        <div id="nrp-notice" class="lookup-status-box"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jabatan</label>
                        <input type="text" id="jabatan" class="form-control" placeholder="Terisi otomatis dari NRP" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lokasi Tujuan <span class="required-star">*</span></label>
                        <input type="text" id="lokasi" class="form-control" placeholder="Contoh: PIT 1, Workshop, Pos 3..." required>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Tanggal Pengambilan <span class="required-star">*</span></label>
                        <input type="date" id="date" class="form-control" required>
                    </div>
                </div>

                <!-- Repeater Section -->
                <div class="repeater-section">
                    <div class="repeater-header">
                        <span class="repeater-title">Daftar Barang & Jumlah <span class="required-star">*</span></span>
                        <button type="button" class="btn-add-item" onclick="addItemRow()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Tambah Barang
                        </button>
                    </div>
                    
                    <div id="items-container">
                        <!-- Baris barang dimuat via JavaScript -->
                    </div>
                </div>

                <!-- Bukti Foto -->
                <div class="photo-card">
                    <div class="photo-card-inner">
                        <div class="photo-icon-group">
                            <div class="photo-icon-box">📷</div>
                            <div class="photo-text">
                                <h5>Foto Bukti Pengambilan</h5>
                                <p>Opsional • Lampirkan foto dari kamera atau galeri</p>
                            </div>
                        </div>
                        <button type="button" class="btn-upload-trigger" onclick="document.getElementById('photo-input').click()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            Ambil / Upload Foto
                        </button>
                        <input type="file" id="photo-input" accept="image/*" capture="environment" style="display: none;" onchange="handlePhotoSelect(event)">
                    </div>

                    <div id="preview-container">
                        <img id="preview-img" src="" alt="Pratinjau Foto">
                        <button type="button" id="remove-photo" onclick="removePhoto()" title="Hapus Foto">✕</button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="btn-submit" class="btn-submit-main">
                    <span>Kirim Pengambilan Barang</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </form>
        </main>
    </div>

    <script>
        let appConfig = { items: [], karyawan: {}, maxPhotoMb: 5 };
        let photoBase64 = null;
        let lookupTimer = null;
        let lookupAbortController = null;

        document.addEventListener('DOMContentLoaded', async () => {
            document.getElementById('date').valueAsDate = new Date();
            
            try {
                const configRes = await fetch("{{ route('barang.public.config') }}").then(r => r.json()).catch(() => ({}));

                if (configRes && configRes.success) {
                    appConfig.karyawan = configRes.karyawan || {};
                    appConfig.maxPhotoMb = configRes.maxPhotoMb || 5;
                    appConfig.items = configRes.items || [];
                }
            } catch (e) {
                console.error("Gagal memuat konfigurasi awal.", e);
            }

            // Tampilkan 1 baris input barang
            addItemRow();

            // Listener input NRP dengan debounce & fallback lookup
            const nrpInput = document.getElementById('nrp');
            nrpInput.addEventListener('input', function(e) {
                clearTimeout(lookupTimer);
                const val = normalizeNrp(e.target.value);
                
                lookupTimer = setTimeout(() => {
                    handleNrpLookup(val);
                }, 300);
            });

            nrpInput.addEventListener('blur', function(e) {
                clearTimeout(lookupTimer);
                handleNrpLookup(normalizeNrp(e.target.value));
            });
        });

        function normalizeNrp(val) {
            return String(val || '').trim().replace(/\s+/g, '').toUpperCase();
        }

        function showNotice(msg, type = '') {
            const notice = document.getElementById('nrp-notice');
            if (!notice) return;
            
            notice.className = 'lookup-status-box';
            if (type) notice.classList.add(type);
            notice.innerHTML = msg;
        }

        async function handleNrpLookup(nrp) {
            const nameInput = document.getElementById('name');
            const jabatanInput = document.getElementById('jabatan');

            if (!nrp || nrp.length < 2) {
                nameInput.value = '';
                jabatanInput.value = '';
                showNotice('');
                return;
            }

            if (appConfig.karyawan && appConfig.karyawan[nrp]) {
                nameInput.value = appConfig.karyawan[nrp].nama || '';
                jabatanInput.value = appConfig.karyawan[nrp].jabatan || '';
                showNotice('✓ NRP ditemukan. Nama dan jabatan terisi otomatis.', 'success');
                return;
            }

            if (lookupAbortController) {
                lookupAbortController.abort();
            }
            lookupAbortController = new AbortController();

            showNotice('Mencari NRP pada database karyawan…', 'info');

            try {
                const url = `{{ route('barang.public.employee.lookup') }}?nrp=${encodeURIComponent(nrp)}`;
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: lookupAbortController.signal
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok && data.found && data.employee) {
                    nameInput.value = data.employee.nama || '';
                    jabatanInput.value = data.employee.jabatan || '';
                    showNotice('✓ NRP ditemukan. Nama dan jabatan terisi otomatis.', 'success');
                    
                    if (!appConfig.karyawan) appConfig.karyawan = {};
                    appConfig.karyawan[nrp] = {
                        nama: data.employee.nama,
                        jabatan: data.employee.jabatan
                    };
                } else {
                    nameInput.value = '';
                    jabatanInput.value = '';
                    showNotice(data.message || 'NRP tidak ditemukan di database karyawan.', 'error');
                }
            } catch (err) {
                if (err.name === 'AbortError') return;
                nameInput.value = '';
                jabatanInput.value = '';
                showNotice('Gagal memeriksa NRP. Silakan periksa koneksi data.', 'error');
            }
        }

        function addItemRow() {
            const container = document.getElementById('items-container');
            const row = document.createElement('div');
            row.className = 'item-row';
            
            let options = '<option value="">Pilih barang...</option>';
            const itemList = (appConfig.items && appConfig.items.length > 0) ? appConfig.items : [
                'Pulpen Gel', 'Pulpen Pilot', 'Spidol Putih Permanen',
                'Spidol Hitam Permanen', 'Spidol Hitam Whiteboard', 'Buku Saku',
                'Isolasi Bening Kecil', 'Isolasi Bening Besar', 'TISUE'
            ];
            
            itemList.forEach(item => { 
                options += `<option value="${item}">${item}</option>`; 
            });

            row.innerHTML = `
                <div class="form-group">
                    <label class="form-label">Barang <span class="required-star">*</span></label>
                    <select class="form-control item-select" required>${options}</select>
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah <span class="required-star">*</span></label>
                    <input type="number" class="form-control item-qty" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan <span class="required-star">*</span></label>
                    <select class="form-control item-unit" required>
                        <option value="Pcs">Pcs</option>
                        <option value="Box">Box</option>
                        <option value="Buah">Buah</option>
                        <option value="Bungkus">Bungkus</option>
                        <option value="Rim">Rim</option>
                        <option value="Botol">Botol</option>
                        <option value="Unit">Unit</option>
                    </select>
                </div>
                <button type="button" class="btn-remove-row" onclick="this.parentElement.remove()" title="Hapus baris">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
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
            const rows = document.querySelectorAll('.item-row');
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
            btn.innerHTML = '<span>Menyimpan Data Transaksi... ⏳</span><span></span>';

            try {
                const res = await fetch("{{ route('barang.public.pickup.store') }}", {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                
                if (data.success) {
                    alert('Berhasil! Data pengambilan telah tersimpan di sistem.');
                    document.getElementById('pickup-form').reset();
                    document.getElementById('date').valueAsDate = new Date();
                    document.getElementById('items-container').innerHTML = '';
                    showNotice('');
                    removePhoto();
                    addItemRow();
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan saat menyimpan data.');
                console.error(err);
            } finally {
                btn.disabled = false;
                btn.innerHTML = `
                    <span>Kirim Pengambilan Barang</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                `;
            }
        }
    </script>
</body>
</html>