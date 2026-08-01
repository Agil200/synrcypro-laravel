<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class MinePermitController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MONITORING SHE
    |--------------------------------------------------------------------------
    */

    public function monitoringShe(
        Request $request,
        GoogleSheetsService $googleSheetsService
    ): View {
        $sheetError = null;

        /*
        |--------------------------------------------------------------------------
        | Baca data asli Google Spreadsheet
        |--------------------------------------------------------------------------
        */

        try {
            $values =
                $googleSheetsService
                    ->getMonitoringSheValues();

            /*
             * Baris pertama Spreadsheet adalah header.
             * Data dimulai dari baris kedua.
             */

            $monitoringSheRows = collect($values)
                ->slice(1)
                ->map(function (array $row): array {
                    /*
                     * Spreadsheet dibaca sampai kolom Z.
                     * array_pad mencegah error apabila suatu baris
                     * memiliki kolom kosong di bagian belakang.
                     */

                    $row = array_pad(
                        $row,
                        26,
                        ''
                    );

                    /*
                     * Pemetaan kolom Spreadsheet Monitoring SHE:
                     *
                     * Index 0  = Timestamp
                     * Index 3  = Nama Karyawan Baru (KAPITAL)
                     * Index 4  = Jabatan
                     * Index 10 = Jenis Pengajuan
                     * Index 13 = NRP Karyawan
                     * Index 18 = Column 19 / Status proses SHE
                     */

                    $statusRaw = strtoupper(
                        trim((string) $row[18])
                    );

                    $status = match (true) {
                        str_contains(
                            $statusRaw,
                            'GAGAL'
                        ) =>
                            'GAGAL',

                        str_contains(
                            $statusRaw,
                            'SELESAI'
                        ) =>
                            'SELESAI',

                        default =>
                            'PROSES',
                    };

return [
    'timestamp' =>
        trim((string) $row[0]),

    'nrp' =>
        trim((string) $row[13]),

    'nama' =>
        trim((string) $row[3]),

    'jabatan' =>
        trim((string) $row[4]),

    /*
     * Kolom Departemen berada pada index 5.
     */
    'departemen' =>
        strtoupper(
            trim((string) $row[5])
        ),

    'jenis_pengajuan' =>
        trim((string) $row[10]),

    'status' =>
        $status,
];
                })
->filter(function (array $row): bool {
    return
        $row['timestamp'] !== '' ||
        $row['nrp'] !== '' ||
        $row['nama'] !== '';
})
->filter(function (array $row): bool {
    /*
     * Hanya tampilkan data Departemen PRODUKSI.
     */
    return $row['departemen'] === 'PRODUKSI';
})
->values();

        } catch (Throwable $exception) {
            report($exception);

            $monitoringSheRows = collect();

            $sheetError =
                'Data Google Spreadsheet belum dapat dibaca: ' .
                $exception->getMessage();
        }

        /*
        |--------------------------------------------------------------------------
        | Filter pencarian Monitoring SHE
        |--------------------------------------------------------------------------
        */

        $search = strtolower(
            trim(
                (string) $request->query(
                    'search',
                    ''
                )
            )
        );

        $filteredMonitoringSheRows =
            $monitoringSheRows
                ->filter(
                    function (array $row) use ($search): bool {
                        if ($search === '') {
                            return true;
                        }

                        $searchableText = strtolower(
                            implode(' ', [
                                $row['timestamp'],
                                $row['nrp'],
                                $row['nama'],
                                $row['jabatan'],
                                $row['jenis_pengajuan'],
                                $row['status'],
                            ])
                        );

                        return str_contains(
                            $searchableText,
                            $search
                        );
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Statistik Monitoring SHE dari Spreadsheet
        |--------------------------------------------------------------------------
        */

        $totalSelesai =
            $monitoringSheRows
                ->where('status', 'SELESAI')
                ->count();

        $totalGagal =
            $monitoringSheRows
                ->where('status', 'GAGAL')
                ->count();

        /*
         * Menghitung semua pengajuan pada bulan dan tahun berjalan
         * berdasarkan kolom Timestamp.
         */

        $currentDate = now(
            config(
                'app.timezone',
                'Asia/Jakarta'
            )
        );

        $prosesPengajuanBulanIni =
            $monitoringSheRows
                ->filter(
                    function (array $row) use ($currentDate): bool {
                        $timestamp =
                            $this->parseSpreadsheetTimestamp(
                                $row['timestamp']
                            );

                        if (!$timestamp) {
                            return false;
                        }

                        return
                            $timestamp->year ===
                                $currentDate->year &&
                            $timestamp->month ===
                                $currentDate->month;
                    }
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Kirim data Monitoring SHE ke Blade
        |--------------------------------------------------------------------------
        */

        return view('manpower', [
            'contentView' =>
                'manpower.mine-permit.monitoring-she',

            'monitoringSheRows' =>
                $monitoringSheRows,

            'filteredMonitoringSheRows' =>
                $filteredMonitoringSheRows,

            'totalMonitoringSheRows' =>
                $monitoringSheRows->count(),

            'prosesPengajuanBulanIni' =>
                $prosesPengajuanBulanIni,

            'totalSelesai' =>
                $totalSelesai,

            'totalGagal' =>
                $totalGagal,

            'search' =>
                $request->query('search', ''),

            'sheetError' =>
                $sheetError,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MONITORING INTERNAL UPLOAD
    |--------------------------------------------------------------------------
    | Bagian ini masih menggunakan data contoh.
    | Nanti dihubungkan ke Spreadsheet Internal Upload pada tahap berikutnya.
    */

    public function monitoringInternalUpload(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Data contoh Monitoring Internal Upload
        |--------------------------------------------------------------------------
        */

        $employees = [
            [
                'nama' => 'ADHE ANDESKA',
                'nrp' => '22000579',
                'jabatan' => 'OPERATOR DT 31 - 50T',
                'versatility' => 'DT - WT',
                'tanggal_berlaku' => '25 Mar 2026',
                'uploaded_at' => '22 Apr 2026, 13:32 WIB',
                'dokumen_terisi' => 6,
                'total_dokumen' => 8,
                'status' => 'belum-lengkap',
                'documents' => [
                    'KTP' => true,
                    'SIMPOL Aktif' => true,
                    'SIMPER Depan' => true,
                    'SIMPER Belakang' => true,
                    'Sertifikat/Piagam' => true,
                    'Pasfoto' => true,
                    'Foto KTP' => false,
                    'Foto SIB DLT' => false,
                ],
            ],
            [
                'nama' => 'MIRZA PRANATA',
                'nrp' => '23002436',
                'jabatan' => 'OPERATOR DT 31 - 50T',
                'versatility' => 'DUMP TRUCK',
                'tanggal_berlaku' => '26 Mei 2026',
                'uploaded_at' => '22 Apr 2026, 14:31 WIB',
                'dokumen_terisi' => 8,
                'total_dokumen' => 8,
                'status' => 'lengkap',
                'documents' => [
                    'KTP' => true,
                    'SIMPOL Aktif' => true,
                    'SIMPER Depan' => true,
                    'SIMPER Belakang' => true,
                    'Sertifikat/Piagam' => true,
                    'Pasfoto' => true,
                    'Foto KTP' => true,
                    'Foto SIB DLT' => true,
                ],
            ],
            [
                'nama' => 'EDI SUPRAPTO',
                'nrp' => '22002624',
                'jabatan' => 'GROUP LEADER PROD',
                'versatility' => 'WATER TRUCK',
                'tanggal_berlaku' => '23 Jul 2025',
                'uploaded_at' => '24 Apr 2026, 09:18 WIB',
                'dokumen_terisi' => 5,
                'total_dokumen' => 8,
                'status' => 'expired',
                'documents' => [
                    'KTP' => true,
                    'SIMPOL Aktif' => true,
                    'SIMPER Depan' => true,
                    'SIMPER Belakang' => true,
                    'Sertifikat/Piagam' => false,
                    'Pasfoto' => true,
                    'Foto KTP' => false,
                    'Foto SIB DLT' => false,
                ],
            ],
            [
                'nama' => 'SUTRISNO',
                'nrp' => '24004350',
                'jabatan' => 'OPERATOR WT 20KL',
                'versatility' => 'WT DT',
                'tanggal_berlaku' => '31 Jul 2026',
                'uploaded_at' => '24 Apr 2026, 17:20 WIB',
                'dokumen_terisi' => 7,
                'total_dokumen' => 8,
                'status' => 'belum-lengkap',
                'documents' => [
                    'KTP' => true,
                    'SIMPOL Aktif' => true,
                    'SIMPER Depan' => true,
                    'SIMPER Belakang' => true,
                    'Sertifikat/Piagam' => true,
                    'Pasfoto' => true,
                    'Foto KTP' => true,
                    'Foto SIB DLT' => false,
                ],
            ],
            [
                'nama' => 'DAUT SYAH PUTRA',
                'nrp' => '24003455',
                'jabatan' => 'OPERATOR WT 20KL',
                'versatility' => 'WATER TRUCK',
                'tanggal_berlaku' => '31 Jul 2026',
                'uploaded_at' => '26 Apr 2026, 14:19 WIB',
                'dokumen_terisi' => 8,
                'total_dokumen' => 8,
                'status' => 'lengkap',
                'documents' => [
                    'KTP' => true,
                    'SIMPOL Aktif' => true,
                    'SIMPER Depan' => true,
                    'SIMPER Belakang' => true,
                    'Sertifikat/Piagam' => true,
                    'Pasfoto' => true,
                    'Foto KTP' => true,
                    'Foto SIB DLT' => true,
                ],
            ],
            [
                'nama' => 'AL MIZAR',
                'nrp' => '22003821',
                'jabatan' => 'OPERATOR DT 31 - 50T',
                'versatility' => 'DUMP TRUCK',
                'tanggal_berlaku' => '23 Jun 2026',
                'uploaded_at' => '28 Apr 2026, 10:12 WIB',
                'dokumen_terisi' => 4,
                'total_dokumen' => 8,
                'status' => 'belum-lengkap',
                'documents' => [
                    'KTP' => true,
                    'SIMPOL Aktif' => true,
                    'SIMPER Depan' => true,
                    'SIMPER Belakang' => false,
                    'Sertifikat/Piagam' => false,
                    'Pasfoto' => true,
                    'Foto KTP' => false,
                    'Foto SIB DLT' => false,
                ],
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Filter pencarian dan status Internal Upload
        |--------------------------------------------------------------------------
        */

        $search = strtolower(
            trim((string) $request->query('search', ''))
        );

        $selectedStatus = (string) $request->query(
            'status',
            'semua'
        );

        $filteredEmployees = collect($employees)
            ->filter(function (array $employee) use (
                $search,
                $selectedStatus
            ): bool {
                $matchesSearch =
                    $search === '' ||
                    str_contains(
                        strtolower($employee['nama']),
                        $search
                    ) ||
                    str_contains(
                        strtolower($employee['nrp']),
                        $search
                    ) ||
                    str_contains(
                        strtolower($employee['jabatan']),
                        $search
                    );

                $matchesStatus =
                    $selectedStatus === 'semua' ||
                    $employee['status'] === $selectedStatus;

                return $matchesSearch && $matchesStatus;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Statistik Monitoring Internal Upload
        |--------------------------------------------------------------------------
        */

        $employeeCollection = collect($employees);

        $totalData = $employeeCollection->count();

        $totalLengkap = $employeeCollection
            ->where('status', 'lengkap')
            ->count();

        $totalBelumLengkap = $employeeCollection
            ->where('status', 'belum-lengkap')
            ->count();

        $totalExpired = $employeeCollection
            ->where('status', 'expired')
            ->count();

        return view('manpower', [
            'contentView' =>
                'manpower.mine-permit.monitoring-internal-upload',

            'employees' =>
                $employees,

            'filteredEmployees' =>
                $filteredEmployees,

            'selectedStatus' =>
                $selectedStatus,

            'totalData' =>
                $totalData,

            'totalLengkap' =>
                $totalLengkap,

            'totalBelumLengkap' =>
                $totalBelumLengkap,

            'totalExpired' =>
                $totalExpired,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Parse Timestamp Google Spreadsheet
    |--------------------------------------------------------------------------
    */

    private function parseSpreadsheetTimestamp(
        string $value
    ): ?Carbon {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $timezone = config(
            'app.timezone',
            'Asia/Jakarta'
        );

        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'j/n/Y H:i:s',
            'j/n/Y H:i',
            'd/m/Y',
            'j/n/Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat(
                    $format,
                    $value,
                    $timezone
                );
            } catch (Throwable) {
                // Coba format berikutnya.
            }
        }

        return null;
    }
}