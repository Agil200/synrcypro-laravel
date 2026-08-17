@extends('admin-all.layout')

@section('title', 'Input / Update MCU — SYNRGYPRO')

@push('styles')
<style>
    .mfi-page { display: grid; gap: 9px; }

    .mfi-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 8px;
        border: 1px solid #dbe2e9;
        border-radius: 9px;
        background: #fff;
    }

    .mfi-search {
        display: flex;
        flex: 1;
        gap: 6px;
    }

    .mfi-search input {
        width: min(420px, 100%);
        min-height: 31px;
        padding: 6px 9px;
        border: 1px solid #cfd8e2;
        border-radius: 7px;
        outline: none;
        font-size: 9px;
    }

    .mfi-button {
        display: inline-flex;
        min-height: 31px;
        align-items: center;
        justify-content: center;
        padding: 7px 10px;
        border: 1px solid #cfd8e2;
        border-radius: 7px;
        color: #172b43;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
    }

    .mfi-button.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .mfi-alert {
        padding: 9px 11px;
        border-radius: 8px;
        font-size: 8px;
        font-weight: 700;
    }

    .mfi-alert.success {
        border: 1px solid #a9e3c6;
        color: #12643b;
        background: #edfff5;
    }

    .mfi-alert.error {
        border: 1px solid #f0c2c5;
        color: #9b1c25;
        background: #fff1f2;
    }

    .mfi-table-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid #d9e0e7;
        border-radius: 10px;
        background: #fff;
    }

    .mfi-table-wrap {
        overflow: auto;
        max-height: calc(100vh - 245px);
    }

    .mfi-table {
        width: 100%;
        min-width: 1060px;
        border-collapse: collapse;
        font-size: 8px;
    }

    .mfi-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 8px;
        border-bottom: 1px solid #d7e0e8;
        color: #fff;
        background: #173b63;
        font-size: 7px;
        text-align: left;
        white-space: nowrap;
    }

    .mfi-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #edf0f3;
        vertical-align: middle;
    }

    .mfi-table tr:hover td { background: #f8fbfd; }

    .mfi-name strong {
        display: block;
        color: #102a43;
        font-size: 8px;
    }

    .mfi-name span {
        display: block;
        margin-top: 2px;
        color: #728195;
        font-size: 7px;
    }

    .mfi-badge {
        display: inline-flex;
        padding: 3px 6px;
        border-radius: 999px;
        background: #eef4fb;
        color: #295477;
        font-size: 7px;
        font-weight: 900;
    }

    .mfi-edit {
        display: grid;
        grid-template-columns: 120px 120px 160px auto;
        gap: 6px;
        align-items: center;
        min-width: 470px;
    }

    .mfi-edit input,
    .mfi-edit select {
        min-width: 0;
        min-height: 29px;
        padding: 5px 7px;
        border: 1px solid #cfd8e2;
        border-radius: 6px;
        background: #fff;
        font-size: 8px;
    }

    .mfi-save {
        min-height: 29px;
        padding: 5px 9px;
        border: 0;
        border-radius: 6px;
        color: #fff;
        background: #0f78ef;
        font-size: 7px;
        font-weight: 900;
    }

    .mfi-pagination {
        padding: 8px;
        border-top: 1px solid #e2e7ec;
    }

    .mfi-pagination nav {
        font-size: 8px;
    }
</style>
@endpush

@section('admin-content')
<div class="mfi-page">

    <div class="aa-page-title">
        <div>
            <h1>Input / Update MCU</h1>
            <p>
                Update hanya kolom manual: EXP MCU, JADWAL MCU, dan HASIL MCU.
            </p>
        </div>

        <div class="aa-title-actions">
            <a
                href="{{ route('admin-all.mcu-fu.index') }}"
                class="mfi-button"
            >
                DASHBOARD
            </a>

            <a
                href="{{ route('admin-all.mcu-fu.follow-up') }}"
                class="mfi-button"
            >
                INPUT FOLLOW UP
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mfi-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mfi-alert error">
            {{ session('error') }}
        </div>
    @endif

    @if (!empty($error))
        <div class="mfi-alert error">
            {{ $error }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mfi-alert error">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mfi-toolbar">
        <form method="GET" class="mfi-search">
            @foreach (['year', 'month', 'hasil_mcu', 'status_mcu', 'jabatan', 'fu_stage'] as $filterName)
                @if (request($filterName) !== null && request($filterName) !== '')
                    <input
                        type="hidden"
                        name="{{ $filterName }}"
                        value="{{ request($filterName) }}"
                    >
                @endif
            @endforeach

            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari NRP, nama, jabatan, hasil MCU..."
            >

            <button
                type="submit"
                class="mfi-button primary"
            >
                CARI
            </button>

            @if (request('q'))
                <a
                    href="{{ route('admin-all.mcu-fu.mcu') }}"
                    class="mfi-button"
                >
                    RESET
                </a>
            @endif
        </form>

        <span class="mfi-badge">
            {{ number_format($data->total()) }} DATA
            @if (request('hasil_mcu'))
                · {{ request('hasil_mcu') }}
            @elseif (request('status_mcu'))
                · {{ request('status_mcu') }}
            @elseif (request('jabatan'))
                · {{ request('jabatan') }}
            @endif
        </span>
    </div>

    <div class="mfi-table-card">
        <div class="mfi-table-wrap">
            <table class="mfi-table">
                <thead>
                    <tr>
                        <th>ROW</th>
                        <th>NRP / NAMA</th>
                        <th>JABATAN</th>
                        <th>STATUS MCU</th>
                        <th>UPDATE MCU</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($data as $row)
                        <tr>
                            <td>
                                <span class="mfi-badge">
                                    {{ $row['sheet_row'] }}
                                </span>
                            </td>

                            <td class="mfi-name">
                                <strong>
                                    {{ $row['nama'] ?: '-' }}
                                </strong>
                                <span>
                                    {{ $row['nrp'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $row['jabatan'] ?: '-' }}
                            </td>

                            <td>
                                <span class="mfi-badge">
                                    {{ $row['status_mcu'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                <form
                                    method="POST"
                                    action="{{ route('admin-all.mcu-fu.mcu.update', $row['sheet_row']) }}"
                                    class="mfi-edit"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        type="date"
                                        name="exp_mcu"
                                        value="{{ app(\App\Services\McuFuInternalService::class)->htmlDate($row['exp_mcu']) }}"
                                        title="EXP MCU"
                                    >

                                    <input
                                        type="date"
                                        name="jadwal_mcu"
                                        value="{{ app(\App\Services\McuFuInternalService::class)->htmlDate($row['jadwal_mcu']) }}"
                                        title="JADWAL MCU"
                                    >

                                    <select
                                        name="hasil_mcu"
                                        title="HASIL MCU"
                                    >
                                        <option value="">
                                            -- HASIL MCU --
                                        </option>

                                        @if (
                                            $row['hasil_mcu'] &&
                                            !in_array(
                                                $row['hasil_mcu'],
                                                $options['hasil_mcu'],
                                                true
                                            )
                                        )
                                            <option
                                                value="{{ $row['hasil_mcu'] }}"
                                                selected
                                            >
                                                {{ $row['hasil_mcu'] }}
                                            </option>
                                        @endif

                                        @foreach ($options['hasil_mcu'] as $option)
                                            <option
                                                value="{{ $option }}"
                                                @selected($row['hasil_mcu'] === $option)
                                            >
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button
                                        type="submit"
                                        class="mfi-save"
                                        onclick="return confirm('Simpan update MCU untuk {{ addslashes($row['nama']) }}?')"
                                    >
                                        SIMPAN
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                style="padding:24px;text-align:center;color:#718092;"
                            >
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mfi-pagination">
            {{ $data->links() }}
        </div>
    </div>

</div>
@endsection