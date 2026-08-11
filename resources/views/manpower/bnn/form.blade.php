@extends('layouts.app')

@section('content')
<style>
    .bnn-page {
        min-height: calc(100vh - 64px);
        padding: 26px;
        background:
            radial-gradient(circle at top right, rgba(234, 88, 12, .12), transparent 34%),
            #f4f7fb;
        color: #172033;
    }

    .bnn-shell {
        max-width: 1120px;
        margin: 0 auto;
    }

    .bnn-topbar,
    .bnn-card {
        border: 1px solid #e1e7ef;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
    }

    .bnn-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 18px;
        padding: 22px 24px;
        color: #fff;
        border: 0;
        background: linear-gradient(120deg, #182236 0%, #7c2d12 58%, #d14b21 100%);
    }

    .bnn-eyebrow {
        margin: 0 0 5px;
        color: #fed7aa;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .bnn-title {
        margin: 0;
        font-size: clamp(23px, 3vw, 34px);
        line-height: 1.1;
    }

    .bnn-subtitle {
        margin: 8px 0 0;
        color: #e8edf5;
        font-size: 14px;
    }

    .bnn-back,
    .bnn-sheet-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .bnn-back {
        flex: 0 0 auto;
        min-height: 42px;
        padding: 0 16px;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .42);
        background: rgba(255, 255, 255, .1);
    }

    .bnn-back:hover {
        color: #fff;
        background: rgba(255, 255, 255, .2);
    }

    .bnn-alert {
        margin-bottom: 16px;
        padding: 13px 16px;
        border: 1px solid;
        border-radius: 12px;
        font-size: 14px;
    }

    .bnn-alert-error {
        color: #991b1b;
        border-color: #fecaca;
        background: #fff1f2;
    }

    .bnn-alert-success {
        color: #166534;
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .bnn-card {
        overflow: hidden;
    }

    .bnn-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 24px;
        border-bottom: 1px solid #e8edf3;
    }

    .bnn-card-head h2,
    .bnn-section-title {
        margin: 0;
        color: #182236;
    }

    .bnn-card-head h2 {
        font-size: 18px;
    }

    .bnn-card-head p,
    .bnn-section-copy {
        margin: 6px 0 0;
        color: #667085;
        font-size: 13px;
    }

    .bnn-sheet-link {
        flex: 0 0 auto;
        min-height: 38px;
        padding: 0 13px;
        color: #166534;
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        font-size: 13px;
    }

    .bnn-sheet-link:hover {
        color: #14532d;
        background: #dcfce7;
    }

    .bnn-body {
        padding: 24px;
    }

    .bnn-lookup {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        align-items: end;
        max-width: 620px;
        margin-bottom: 12px;
    }

    .bnn-field label {
        display: block;
        margin-bottom: 7px;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .bnn-required {
        color: #dc2626;
    }

    .bnn-input,
    .bnn-select {
        width: 100%;
        height: 46px;
        padding: 0 13px;
        color: #172033;
        border: 1px solid #cfd8e5;
        border-radius: 10px;
        outline: none;
        background: #fff;
        font-size: 14px;
        transition: border-color .2s, box-shadow .2s;
    }

    .bnn-input:focus,
    .bnn-select:focus {
        border-color: #c64c27;
        box-shadow: 0 0 0 4px rgba(198, 76, 39, .12);
    }

    .bnn-input[readonly] {
        color: #344054;
        border-color: #e1e7ef;
        background: #f8fafc;
        cursor: default;
    }

    .bnn-lookup-button {
        height: 46px;
        padding: 0 18px;
        color: #fff;
        border: 0;
        border-radius: 10px;
        background: #24324a;
        font-weight: 800;
        cursor: pointer;
    }

    .bnn-lookup-button:disabled {
        opacity: .55;
        cursor: wait;
    }

    .bnn-status {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        margin-bottom: 22px;
        padding: 5px 10px;
        color: #475467;
        border-radius: 999px;
        background: #eef2f7;
        font-size: 12px;
        font-weight: 700;
    }

    .bnn-status.is-loading { color: #92400e; background: #fff7ed; }
    .bnn-status.is-success { color: #166534; background: #dcfce7; }
    .bnn-status.is-error { color: #991b1b; background: #fee2e2; }

    .bnn-section {
        padding-top: 22px;
        border-top: 1px solid #e8edf3;
    }

    .bnn-section + .bnn-section {
        margin-top: 26px;
    }

    .bnn-section-title {
        font-size: 16px;
    }

    .bnn-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 17px;
        margin-top: 17px;
    }

    .bnn-grid-manual {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        max-width: 760px;
    }

    .bnn-error-text {
        margin: 6px 0 0;
        color: #b42318;
        font-size: 12px;
    }

    .bnn-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 27px -24px -24px;
        padding: 18px 24px;
        border-top: 1px solid #e8edf3;
        background: #f8fafc;
    }

    .bnn-footer-note {
        margin: 0;
        color: #667085;
        font-size: 12px;
    }

    .bnn-submit {
        min-height: 46px;
        padding: 0 22px;
        color: #fff;
        border: 0;
        border-radius: 11px;
        background: linear-gradient(115deg, #a63d20, #df5a29);
        box-shadow: 0 9px 20px rgba(198, 76, 39, .24);
        font-size: 13px;
        font-weight: 900;
        letter-spacing: .02em;
        cursor: pointer;
    }

    .bnn-submit:disabled {
        opacity: .45;
        box-shadow: none;
        cursor: not-allowed;
    }

    @media (max-width: 820px) {
        .bnn-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .bnn-grid-manual { grid-template-columns: 1fr; }
    }

    @media (max-width: 560px) {
        .bnn-page { padding: 14px; }
        .bnn-topbar, .bnn-card-head, .bnn-footer { align-items: stretch; flex-direction: column; }
        .bnn-body, .bnn-card-head, .bnn-topbar { padding: 18px; }
        .bnn-grid, .bnn-lookup { grid-template-columns: 1fr; }
        .bnn-sheet-link, .bnn-back { width: 100%; }
        .bnn-footer { margin: 24px -18px -18px; padding: 17px 18px; }
        .bnn-submit { width: 100%; }
    }
</style>

<main class="bnn-page">
    <div class="bnn-shell">
        <header class="bnn-topbar">
            <div>
                <p class="bnn-eyebrow">Manpower · Pemeriksaan Karyawan</p>
                <h1 class="bnn-title">Form Test BNN</h1>
                <p class="bnn-subtitle">Cari peserta dengan NRP, lalu isi jadwal pemeriksaan dan akomodasi.</p>
            </div>
            <a class="bnn-back" href="{{ route('manpower') }}">← Kembali</a>
        </header>

        @if (session('success'))
            <div class="bnn-alert bnn-alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="bnn-alert bnn-alert-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="bnn-alert bnn-alert-error">
                <strong>Data belum dapat disimpan.</strong>
                <ul style="margin: 7px 0 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="bnn-card">
            <div class="bnn-card-head">
                <div>
                    <h2>Data peserta BNN</h2>
                    <p>Identitas diambil otomatis dari tab <strong>ALL BNN</strong> dan tidak perlu diketik ulang.</p>
                </div>
                <a class="bnn-sheet-link" href="{{ $sourceUrl }}" target="_blank" rel="noopener noreferrer">
                    Buka Spreadsheet ↗
                </a>
            </div>

            <form id="bnnForm" method="POST" action="{{ route('bnn.store') }}" novalidate>
                @csrf
                <div class="bnn-body">
                    <div class="bnn-lookup">
                        <div class="bnn-field">
                            <label for="nrp">NRP <span class="bnn-required">*</span></label>
                            <input
                                class="bnn-input"
                                id="nrp"
                                name="nrp"
                                type="text"
                                value="{{ old('nrp') }}"
                                placeholder="Contoh: 240005152"
                                inputmode="numeric"
                                autocomplete="off"
                                maxlength="50"
                                required
                            >
                            @error('nrp')<p class="bnn-error-text">{{ $message }}</p>@enderror
                        </div>
                        <button class="bnn-lookup-button" id="lookupButton" type="button">Cari NRP</button>
                    </div>

                    <div class="bnn-status" id="lookupStatus" role="status" aria-live="polite">
                        Ketik NRP untuk mengambil data peserta.
                    </div>

                    <section class="bnn-section" aria-labelledby="automaticDataTitle">
                        <h3 class="bnn-section-title" id="automaticDataTitle">Data otomatis</h3>
                        <p class="bnn-section-copy">Sumber: Google Spreadsheet. Data ini hanya dapat dibaca pada form.</p>

                        <div class="bnn-grid">
                            <div class="bnn-field">
                                <label for="nama">Nama</label>
                                <input class="bnn-input" id="nama" type="text" readonly placeholder="—">
                            </div>
                            <div class="bnn-field">
                                <label for="jenis_kelamin">Jenis kelamin</label>
                                <input class="bnn-input" id="jenis_kelamin" type="text" readonly placeholder="—">
                            </div>
                            <div class="bnn-field">
                                <label for="perusahaan">Perusahaan</label>
                                <input class="bnn-input" id="perusahaan" type="text" readonly placeholder="—">
                            </div>
                            <div class="bnn-field">
                                <label for="dept">Departemen</label>
                                <input class="bnn-input" id="dept" type="text" readonly placeholder="—">
                            </div>
                            <div class="bnn-field">
                                <label for="posisi">Posisi</label>
                                <input class="bnn-input" id="posisi" type="text" readonly placeholder="—">
                            </div>
                            <div class="bnn-field">
                                <label for="usia">Usia</label>
                                <input class="bnn-input" id="usia" type="text" readonly placeholder="—">
                            </div>
                            <div class="bnn-field">
                                <label for="kontak">Kontak</label>
                                <input class="bnn-input" id="kontak" type="text" readonly placeholder="—">
                            </div>
                            <div class="bnn-field">
                                <label for="nik">NIK</label>
                                <input class="bnn-input" id="nik" type="text" readonly placeholder="—">
                            </div>
                        </div>
                    </section>

                    <section class="bnn-section" aria-labelledby="scheduleTitle">
                        <h3 class="bnn-section-title" id="scheduleTitle">Jadwal pemeriksaan</h3>
                        <p class="bnn-section-copy">Hanya dua data berikut yang perlu diisi secara manual.</p>

                        <div class="bnn-grid bnn-grid-manual">
                            <div class="bnn-field">
                                <label for="tanggal_pemeriksaan">Tanggal pemeriksaan <span class="bnn-required">*</span></label>
                                <input
                                    class="bnn-input"
                                    id="tanggal_pemeriksaan"
                                    name="tanggal_pemeriksaan"
                                    type="date"
                                    value="{{ old('tanggal_pemeriksaan') }}"
                                    required
                                >
                                @error('tanggal_pemeriksaan')<p class="bnn-error-text">{{ $message }}</p>@enderror
                            </div>
                            <div class="bnn-field">
                                <label for="akomodasi">Akomodasi <span class="bnn-required">*</span></label>
                                <select class="bnn-select" id="akomodasi" name="akomodasi" required>
                                    <option value="">Pilih akomodasi</option>
                                    <option value="DIANTAR DARI MESS TAMBANG" @selected(old('akomodasi') === 'DIANTAR DARI MESS TAMBANG')>Diantar dari Mess Tambang</option>
                                    <option value="BERANGKAT SENDIRI" @selected(old('akomodasi') === 'BERANGKAT SENDIRI')>Berangkat Sendiri</option>
                                    <option value="BERANGKAT DARI BANGKO" @selected(old('akomodasi') === 'BERANGKAT DARI BANGKO')>Berangkat dari Bangko</option>
                                </select>
                                @error('akomodasi')<p class="bnn-error-text">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>

                    <footer class="bnn-footer">
                        <p class="bnn-footer-note">Penyimpanan akan memperbarui kolom tanggal dan akomodasi pada baris NRP yang ditemukan.</p>
                        <button class="bnn-submit" id="submitButton" type="submit" disabled>Simpan Data BNN</button>
                    </footer>
                </div>
            </form>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lookupBaseUrl = @json(url('/manpower/bnn/cari'));
    const form = document.getElementById('bnnForm');
    const nrpInput = document.getElementById('nrp');
    const lookupButton = document.getElementById('lookupButton');
    const lookupStatus = document.getElementById('lookupStatus');
    const submitButton = document.getElementById('submitButton');
    const dateInput = document.getElementById('tanggal_pemeriksaan');
    const accommodationInput = document.getElementById('akomodasi');
    const fieldNames = [
        'nama',
        'jenis_kelamin',
        'perusahaan',
        'dept',
        'posisi',
        'usia',
        'kontak',
        'nik'
    ];

    let debounceTimer = null;
    let activeRequest = null;
    let validLookupNrp = '';

    const setStatus = (message, type = '') => {
        lookupStatus.textContent = message;
        lookupStatus.className = `bnn-status${type ? ` is-${type}` : ''}`;
    };

    const clearParticipant = () => {
        fieldNames.forEach((fieldName) => {
            document.getElementById(fieldName).value = '';
        });
        validLookupNrp = '';
        updateSubmitState();
    };

    const updateSubmitState = () => {
        const currentNrp = nrpInput.value.trim();
        submitButton.disabled = !(
            validLookupNrp !== '' &&
            validLookupNrp === currentNrp &&
            dateInput.value !== '' &&
            accommodationInput.value !== ''
        );
    };

    const lookupNrp = async () => {
        const nrp = nrpInput.value.trim();
        clearParticipant();

        if (nrp.length < 3) {
            setStatus('Masukkan sedikitnya 3 karakter NRP.', 'error');
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }

        const requestController = new AbortController();
        activeRequest = requestController;
        lookupButton.disabled = true;
        lookupButton.textContent = 'Mencari…';
        setStatus('Mengambil data peserta dari Google Spreadsheet…', 'loading');

        try {
            const response = await fetch(
                `${lookupBaseUrl}/${encodeURIComponent(nrp)}`,
                {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    signal: requestController.signal
                }
            );

            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.status !== true) {
                throw new Error(data.message || 'Data peserta tidak ditemukan.');
            }

            if (nrpInput.value.trim() !== nrp) {
                return;
            }

            fieldNames.forEach((fieldName) => {
                document.getElementById(fieldName).value = data[fieldName] ?? '';
            });

            validLookupNrp = String(data.nrp ?? nrp).trim();
            setStatus(`Data ditemukan: ${data.nama ?? nrp}`, 'success');
            updateSubmitState();
        } catch (error) {
            if (error.name !== 'AbortError') {
                clearParticipant();
                setStatus(error.message || 'Gagal mengambil data peserta.', 'error');
            }
        } finally {
            if (activeRequest === requestController) {
                activeRequest = null;
                lookupButton.disabled = false;
                lookupButton.textContent = 'Cari NRP';
            }
        }
    };

    nrpInput.addEventListener('input', () => {
        nrpInput.value = nrpInput.value.replace(/\s+/g, '');
        clearParticipant();
        clearTimeout(debounceTimer);

        if (nrpInput.value.trim().length < 3) {
            setStatus('Ketik NRP untuk mengambil data peserta.');
            return;
        }

        setStatus('Menunggu pencarian…');
        debounceTimer = setTimeout(lookupNrp, 450);
    });

    lookupButton.addEventListener('click', lookupNrp);
    dateInput.addEventListener('change', updateSubmitState);
    accommodationInput.addEventListener('change', updateSubmitState);

    form.addEventListener('submit', (event) => {
        updateSubmitState();
        if (submitButton.disabled) {
            event.preventDefault();
            setStatus('Temukan NRP dan lengkapi tanggal serta akomodasi terlebih dahulu.', 'error');
        }
    });

    if (nrpInput.value.trim().length >= 3) {
        lookupNrp();
    }
});
</script>
@endsection