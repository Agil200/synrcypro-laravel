@extends('admin-all.layout')

@section('title', 'Riwayat Update MCU & FU — SYNRGYPRO')

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

    .mfi-filter {
        display: flex;
        flex-wrap: wrap;
        flex: 1;
        gap: 6px;
    }

    .mfi-filter input,
    .mfi-filter select {
        min-height: 31px;
        padding: 6px 9px;
        border: 1px solid #cfd8e2;
        border-radius: 7px;
        background: #fff;
        font-size: 8px;
    }

    .mfi-filter input {
        width: min(360px, 100%);
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

    .mfi-table-card {
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
        min-width: 1100px;
        border-collapse: collapse;
        font-size: 8px;
    }

    .mfi-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 8px;
        color: #fff;
        background: #3e4c59;
        font-size: 7px;
        text-align: left;
        white-space: nowrap;
    }

    .mfi-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #edf0f3;
        vertical-align: top;
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

    .mfi-badge.fu {
        color: #176b57;
        background: #e9f8f2;
    }

    .mfi-change {
        display: grid;
        gap: 3px;
        min-width: 320px;
    }

    .mfi-change-row {
        display: grid;
        grid-template-columns: 100px 1fr;
        gap: 6px;
        color: #46586a;
    }

    .mfi-change-row strong {
        color: #20384e;
        font-size: 7px;
    }

    .mfi-muted {
        color: #728195;
        font-size: 7px;
    }

    .mfi-pagination {
        padding: 8px;
        border-top: 1px solid #e2e7ec;
    }
</style>
@endpush

@section('admin-content')
<div class="mfi-page">

    <div class="aa-page-title">
        <div>
            <h1>Riwayat Update MCU & FU</h1>
            <p>
                Riwayat perubahan yang dilakukan melalui website SYNRGYPRO.
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
                href="{{ route('admin-all.mcu-fu.mcu') }}"
                class="mfi-button"
            >
                UPDATE MCU
            </a>

            <a
                href="{{ route('admin-all.mcu-fu.follow-up') }}"
                class="mfi-button"
            >
                FOLLOW UP
            </a>
        </div>
    </div>

    <div class="mfi-toolbar">
        <form method="GET" class="mfi-filter">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari NRP, nama, atau user..."
            >

            <select name="action">
                <option value="">
                    SEMUA AKTIVITAS
                </option>

                <option
                    value="MCU_UPDATE"
                    @selected(request('action') === 'MCU_UPDATE')
                >
                    UPDATE MCU
                </option>

                <option
                    value="FOLLOW_UP_UPDATE"
                    @selected(request('action') === 'FOLLOW_UP_UPDATE')
                >
                    UPDATE FOLLOW UP
                </option>
            </select>

            <button
                type="submit"
                class="mfi-button primary"
            >
                FILTER
            </button>

            @if (request('q') || request('action'))
                <a
                    href="{{ route('admin-all.mcu-fu.history') }}"
                    class="mfi-button"
                >
                    RESET
                </a>
            @endif
        </form>

        <span class="mfi-badge">
            {{ number_format($histories->total()) }} RIWAYAT
        </span>
    </div>

    <div class="mfi-table-card">
        <div class="mfi-table-wrap">
            <table class="mfi-table">
                <thead>
                    <tr>
                        <th>WAKTU</th>
                        <th>AKTIVITAS</th>
                        <th>ROW</th>
                        <th>NRP / NAMA</th>
                        <th>PERUBAHAN</th>
                        <th>USER</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($histories as $history)
                        @php
                            $before = $history->before_data ?? [];
                            $after = $history->after_data ?? [];

                            $fields = $history->action === 'MCU_UPDATE'
                                ? [
                                    'exp_mcu' => 'EXP MCU',
                                    'jadwal_mcu' => 'JADWAL MCU',
                                    'hasil_mcu' => 'HASIL MCU',
                                ]
                                : [
                                    'follow_up_1' => 'FOLLOW UP 1',
                                    'follow_up_2' => 'FOLLOW UP 2',
                                    'follow_up_3' => 'FOLLOW UP 3',
                                    'jadwal_fu' => 'JADWAL FU',
                                    'status_fu' => 'STATUS FU',
                                ];

                            $changes = collect($fields)
                                ->filter(
                                    fn ($label, $field) =>
                                        (string) ($before[$field] ?? '') !==
                                        (string) ($after[$field] ?? '')
                                );
                        @endphp

                        <tr>
                            <td>
                                <strong>
                                    {{ optional($history->created_at)->format('d/m/Y H:i') }}
                                </strong>
                            </td>

                            <td>
                                <span
                                    class="mfi-badge {{ $history->action === 'FOLLOW_UP_UPDATE' ? 'fu' : '' }}"
                                >
                                    {{ $history->action === 'MCU_UPDATE' ? 'UPDATE MCU' : 'UPDATE FOLLOW UP' }}
                                </span>
                            </td>

                            <td>
                                {{ $history->sheet_row }}
                            </td>

                            <td>
                                <strong>{{ $history->nama ?: '-' }}</strong>
                                <div class="mfi-muted">
                                    {{ $history->nrp ?: '-' }}
                                </div>
                            </td>

                            <td>
                                <div class="mfi-change">
                                    @forelse ($changes as $field => $label)
                                        <div class="mfi-change-row">
                                            <strong>{{ $label }}</strong>
                                            <span>
                                                {{ $before[$field] ?? '-' }}
                                                →
                                                {{ $after[$field] ?? '-' }}
                                            </span>
                                        </div>
                                    @empty
                                        <span class="mfi-muted">
                                            Tidak ada perubahan nilai.
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <td>
                                <strong>
                                    {{ $history->user_name ?: '-' }}
                                </strong>
                                <div class="mfi-muted">
                                    {{ $history->user_email ?: '-' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                style="padding:24px;text-align:center;color:#718092;"
                            >
                                Belum ada riwayat update dari website.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mfi-pagination">
            {{ $histories->links() }}
        </div>
    </div>

</div>
@endsection
