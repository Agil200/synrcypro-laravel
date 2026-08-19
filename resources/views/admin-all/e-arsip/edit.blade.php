@extends('admin-all.layout')

@section('admin-content')

<style>
    .aa-main {
        overflow: hidden !important;
    }

    .aa-content {
        height: 100%;
        min-height: 0;
    }

    .ea-form-page {
        display: flex;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        gap: 9px;
        overflow: hidden;
    }

    .ea-page-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex: 0 0 auto;
    }

    .ea-page-head h1 {
        margin: 0;
        color: #071f3d;
        font-size: clamp(20px, 2vw, 27px);
    }

    .ea-page-head p {
        margin: 3px 0 0;
        color: #667587;
        font-size: 8px;
        line-height: 1.45;
    }

    .ea-head-actions {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .ea-btn {
        display: inline-flex;
        min-height: 31px;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border: 1px solid #ccd7e1;
        border-radius: 7px;
        color: #17304e;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
    }

    .ea-btn.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .ea-card {
        min-height: 0;
        overflow: auto;
        border: 1px solid #d8e0e7;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 5px 18px rgba(31,47,65,.06);
    }

    .ea-card-head {
        padding: 11px 13px;
        border-bottom: 1px solid #e1e7ec;
        background: #f8fafc;
    }

    .ea-card-head strong {
        color: #102b49;
        font-size: 10px;
    }

    .ea-card-head small {
        display: block;
        margin-top: 3px;
        color: #718093;
        font-size: 7px;
    }

    .ea-card-body {
        padding: 13px;
    }

    .ea-form-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .ea-field {
        min-width: 0;
    }

    .ea-span-2 {
        grid-column: span 2;
    }

    .ea-span-4 {
        grid-column: span 4;
    }

    .ea-field label {
        display: block;
        margin-bottom: 5px;
        color: #52657a;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .ea-field input,
    .ea-field textarea {
        width: 100%;
        border: 1px solid #cfd9e2;
        border-radius: 7px;
        color: #112942;
        background: #fff;
        outline: none;
        font-size: 9px;
    }

    .ea-field input {
        height: 35px;
        padding: 0 10px;
    }

    .ea-field textarea {
        padding: 9px 10px;
        resize: vertical;
    }

    .ea-field input:focus,
    .ea-field textarea:focus {
        border-color: #0f78ef;
        box-shadow: 0 0 0 2px rgba(15,120,239,.08);
    }

    .ea-help,
    .ea-error {
        display: block;
        margin-top: 4px;
        font-size: 7px;
        line-height: 1.4;
    }

    .ea-help {
        color: #738195;
    }

    .ea-error {
        color: #bd1e2d;
        font-weight: 800;
    }

    .ea-switch-row {
        display: flex !important;
        align-items: center;
        gap: 8px;
        padding: 9px 10px;
        margin: 0 !important;
        border: 1px solid #dce4ea;
        border-radius: 8px;
        background: #f8fafc;
        text-transform: none !important;
        cursor: pointer;
    }

    .ea-switch-row input {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
    }

    .ea-switch-row strong {
        display: block;
        color: #17304e;
        font-size: 8px;
    }

    .ea-switch-row small {
        display: block;
        margin-top: 2px;
        color: #708093;
        font-size: 7px;
        font-weight: 400;
    }

    .ea-form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 7px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e5e9ed;
    }

    .ea-note {
        padding: 9px 11px;
        border: 1px solid #b8dfcb;
        border-radius: 8px;
        color: #16613c;
        background: #effcf5;
        font-size: 8px;
        line-height: 1.45;
    }

    @media(max-width:900px) {
        .ea-page-head {
            flex-direction: column;
        }

        .ea-form-grid {
            grid-template-columns: 1fr;
        }

        .ea-span-2,
        .ea-span-4 {
            grid-column: auto;
        }
    }
</style>

<div class="ea-form-page">
    <div class="ea-page-head">
        <div>
            <h1>Edit Arsip</h1>
            <p>
                Perbarui registry E-Arsip. Perubahan ini tidak menghapus atau memindahkan file Google Drive.
            </p>
        </div>

        <div class="ea-head-actions">
            <a
                href="{{ route('admin-all.e-arsip.index') }}"
                class="ea-btn"
            >
                ← DAFTAR ARSIP
            </a>

            <a
                href="{{ $archive->drive_url }}"
                target="_blank"
                rel="noopener noreferrer"
                class="ea-btn primary"
            >
                BUKA DRIVE ↗
            </a>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('admin-all.e-arsip.update', $archive) }}"
        class="ea-card"
    >
        @csrf
        @method('PUT')

        <div class="ea-card-head">
            <strong>{{ $archive->name }}</strong>
            <small>
                Terakhir diperbarui {{ optional($archive->updated_at)->format('d-m-Y H:i') ?? '-' }}.
            </small>
        </div>

        <div class="ea-card-body">
            @include('admin-all.e-arsip._form')

            <div class="ea-form-footer">
                <a
                    href="{{ route('admin-all.e-arsip.index') }}"
                    class="ea-btn"
                >
                    BATAL
                </a>

                <button
                    type="submit"
                    class="ea-btn primary"
                >
                    SIMPAN PERUBAHAN
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
