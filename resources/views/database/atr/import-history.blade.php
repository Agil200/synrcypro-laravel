<div class="db-page-title">
    <h1>Riwayat Import ATR</h1>
    <p>Daftar file ATR yang pernah diproses.</p>
</div>

<section class="db-table-card">
    <div class="db-card-header">
        <div>
            <h2>Riwayat Import</h2>
            <small>Data contoh Fase 1.</small>
        </div>
    </div>

    <div class="db-table-wrap atr-table-scroll atr-history-scroll">
        <table class="db-table">
            <thead>
                <tr>
                    <th>Nama File</th>
                    <th>Periode</th>
                    <th>Total Baris</th>
                    <th>Valid</th>
                    <th>Invalid</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($imports as $import)
                    <tr>
                        <td>{{ $import['file'] }}</td>
                        <td>{{ $import['period'] }}</td>
                        <td>{{ $import['rows'] }}</td>
                        <td>{{ $import['valid'] }}</td>
                        <td>{{ $import['invalid'] }}</td>
                        <td>
                            <span class="db-badge blue">
                                {{ $import['status'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>