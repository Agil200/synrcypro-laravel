<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class ManpowerDashboardController extends Controller
{
    /**
     * Menampilkan dashboard Manpower dari data yang tersimpan.
     *
     * Tabel yang belum tersedia tidak akan menyebabkan error 500.
     * Modul tersebut akan ditampilkan sebagai "Belum terhubung".
     */
    public function index(Request $request): View
    {
        Carbon::setLocale('id');

        $bulan = $this->validMonth(
            $request->input('bulan', now()->format('Y-m'))
        );

        $periode = Carbon::createFromFormat('Y-m', $bulan);
        $awalBulan = $periode->copy()->startOfMonth();
        $akhirBulan = $periode->copy()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Tabel utama yang sudah digunakan fitur aplikasi
        |--------------------------------------------------------------------------
        */

        $documentOutAvailable = Schema::hasTable('surat_keluar');
        $coachingAvailable = Schema::hasTable('coaching_counsellings');
        $stSpAvailable = Schema::hasTable('st_sp_records');

        $documentOutTotal = $this->safeCount('surat_keluar');
        $coachingTotal = $this->safeCount('coaching_counsellings');

        $teguranTotal = $this->safeCountWhere(
            'st_sp_records',
            fn ($query) => $query->where('jenis', 'TEGURAN')
        );

        $peringatanTotal = $this->safeCountWhere(
            'st_sp_records',
            fn ($query) => $query->whereIn(
                'jenis',
                [
                    'PERINGATAN PERTAMA',
                    'PERINGATAN KEDUA',
                    'PERINGATAN KETIGA',
                ]
            )
        );

        $stSpAktif = $this->safeCountWhere(
            'st_sp_records',
            fn ($query) => $query->where('status', 'AKTIF')
        );

        $stSpExpired = $this->safeCountWhere(
            'st_sp_records',
            fn ($query) => $query->where('status', 'EXPIRED')
        );

        /*
        |--------------------------------------------------------------------------
        | Tabel modul lain
        |--------------------------------------------------------------------------
        | Beberapa menu masih dapat memakai Google Sheets atau nama tabel yang
        | berbeda. Controller mencoba beberapa nama tabel umum secara aman.
        */

        $minePermitTable = $this->firstExistingTable([
            'mine_permits',
            'monitoring_mine_permits',
            'monitoring_she',
            'monitoring_internal_uploads',
        ]);

        $bastTable = $this->firstExistingTable([
            'bast_assets',
            'bast_asset',
            'berita_acara_assets',
            'asset_handover_records',
        ]);

        $apdTable = $this->firstExistingTable([
            'apd_requests',
            'monitoring_apds',
            'apd_records',
            'apd_submissions',
            'pengajuan_apds',
        ]);

        $mcuTable = $this->firstExistingTable([
            'mcu_fu_records',
            'mcu_records',
            'monitoring_mcu',
            'medical_follow_ups',
        ]);

        $minePermitTotal = $this->safeCount($minePermitTable);
        $bastTotal = $this->safeCount($bastTable);
        $apdTotal = $this->safeCount($apdTable);
        $mcuTotal = $this->safeCount($mcuTable);

        /*
        |--------------------------------------------------------------------------
        | Data pada bulan yang dipilih
        |--------------------------------------------------------------------------
        */

        $documentOutBulan = $this->safeCountByPeriod(
            'surat_keluar',
            'tanggal_surat',
            $awalBulan,
            $akhirBulan
        );

        $coachingBulan = $this->safeCountByPeriod(
            'coaching_counsellings',
            'tanggal',
            $awalBulan,
            $akhirBulan
        );

        $teguranBulan = $this->safeCountByPeriod(
            'st_sp_records',
            'tanggal',
            $awalBulan,
            $akhirBulan,
            fn ($query) => $query->where('jenis', 'TEGURAN')
        );

        $peringatanBulan = $this->safeCountByPeriod(
            'st_sp_records',
            'tanggal',
            $awalBulan,
            $akhirBulan,
            fn ($query) => $query->whereIn(
                'jenis',
                [
                    'PERINGATAN PERTAMA',
                    'PERINGATAN KEDUA',
                    'PERINGATAN KETIGA',
                ]
            )
        );

        $minePermitBulan = $this->safeCountOptionalModuleByPeriod(
            $minePermitTable,
            $awalBulan,
            $akhirBulan
        );

        $bastBulan = $this->safeCountOptionalModuleByPeriod(
            $bastTable,
            $awalBulan,
            $akhirBulan
        );

        $apdBulan = $this->safeCountOptionalModuleByPeriod(
            $apdTable,
            $awalBulan,
            $akhirBulan
        );

        $mcuBulan = $this->safeCountOptionalModuleByPeriod(
            $mcuTable,
            $awalBulan,
            $akhirBulan
        );

        $totalTersimpan = array_sum([
            $documentOutTotal,
            $coachingTotal,
            $teguranTotal,
            $peringatanTotal,
            $minePermitTotal,
            $bastTotal,
            $apdTotal,
            $mcuTotal,
        ]);

        $totalBulan = array_sum([
            $documentOutBulan,
            $coachingBulan,
            $teguranBulan,
            $peringatanBulan,
            $minePermitBulan,
            $bastBulan,
            $apdBulan,
            $mcuBulan,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Kartu seluruh fitur
        |--------------------------------------------------------------------------
        */

        $features = [
            [
                'title' => 'Mine Permit',
                'icon' => '⛏',
                'total' => $minePermitTotal,
                'month' => $minePermitBulan,
                'available' => $minePermitTable !== null,
                'description' => $minePermitTable
                    ? 'Data Mine Permit tersimpan'
                    : 'Monitoring tersedia, database lokal belum terdeteksi',
                'url' => route('mine-permit.monitoring-she'),
                'tone' => 'orange',
            ],
            [
                'title' => 'Test BNN',
                'icon' => '⚕',
                'total' => null,
                'month' => null,
                'available' => false,
                'description' => 'Data saat ini terhubung melalui Google Sheets',
                'url' => 'https://docs.google.com/spreadsheets/d/1V9LU2Ft9NpxHULY7cVWczqpclCDy_Vja6qWtr7la38o/edit?usp=sharing',
                'tone' => 'purple',
                'external' => true,
            ],
            [
                'title' => 'Berita Acara Asset',
                'icon' => '▣',
                'total' => $bastTotal,
                'month' => $bastBulan,
                'available' => $bastTable !== null,
                'description' => $bastTable
                    ? 'Seluruh berita acara asset'
                    : 'Menu tersedia, tabel lokal belum terdeteksi',
                'url' => route('bast.index'),
                'tone' => 'blue',
            ],
            [
                'title' => 'Monitoring APD',
                'icon' => '♙',
                'total' => $apdTotal,
                'month' => $apdBulan,
                'available' => $apdTable !== null,
                'description' => $apdTable
                    ? 'Data pengajuan dan monitoring APD'
                    : 'Tabel APD belum terhubung',
                'url' => route('apd.index'),
                'tone' => 'green',
            ],
            [
                'title' => 'Coaching & Counselling',
                'icon' => '◉',
                'total' => $coachingTotal,
                'month' => $coachingBulan,
                'available' => $coachingAvailable,
                'description' => 'Data pembinaan berdasarkan NRP',
                'url' => route('cc-st-sp.coaching.index'),
                'tone' => 'cyan',
            ],
            [
                'title' => 'Surat Teguran',
                'icon' => '!',
                'total' => $teguranTotal,
                'month' => $teguranBulan,
                'available' => $stSpAvailable,
                'description' => 'Teguran yang sudah tersimpan',
                'url' => route('cc-st-sp.teguran.index'),
                'tone' => 'yellow',
            ],
            [
                'title' => 'Surat Peringatan',
                'icon' => '⚠',
                'total' => $peringatanTotal,
                'month' => $peringatanBulan,
                'available' => $stSpAvailable,
                'description' => 'SP pertama, kedua, dan ketiga',
                'url' => route('cc-st-sp.peringatan.index'),
                'tone' => 'red',
            ],
            [
                'title' => 'MCU & FU',
                'icon' => '✚',
                'total' => $mcuTotal,
                'month' => $mcuBulan,
                'available' => $mcuTable !== null,
                'description' => $mcuTable
                    ? 'Data MCU dan tindak lanjut'
                    : 'Tabel MCU & FU belum terhubung',
                'url' => '#',
                'tone' => 'pink',
            ],
            [
                'title' => 'Document Out',
                'icon' => '↗',
                'total' => $documentOutTotal,
                'month' => $documentOutBulan,
                'available' => $documentOutAvailable,
                'description' => 'Dokumen dan surat keluar beserta PDF',
                'url' => route('document-out.index'),
                'tone' => 'navy',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Tren enam bulan
        |--------------------------------------------------------------------------
        */

        $trend = collect();

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = $periode->copy()->subMonths($offset);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $documentOut = $this->safeCountByPeriod(
                'surat_keluar',
                'tanggal_surat',
                $start,
                $end
            );

            $coaching = $this->safeCountByPeriod(
                'coaching_counsellings',
                'tanggal',
                $start,
                $end
            );

            $teguran = $this->safeCountByPeriod(
                'st_sp_records',
                'tanggal',
                $start,
                $end,
                fn ($query) => $query->where('jenis', 'TEGURAN')
            );

            $peringatan = $this->safeCountByPeriod(
                'st_sp_records',
                'tanggal',
                $start,
                $end,
                fn ($query) => $query->whereIn(
                    'jenis',
                    [
                        'PERINGATAN PERTAMA',
                        'PERINGATAN KEDUA',
                        'PERINGATAN KETIGA',
                    ]
                )
            );

            $trend->push([
                'label' => $month->translatedFormat('M Y'),
                'document_out' => $documentOut,
                'coaching' => $coaching,
                'teguran' => $teguran,
                'peringatan' => $peringatan,
                'total' => (
                    $documentOut
                    + $coaching
                    + $teguran
                    + $peringatan
                ),
            ]);
        }

        $maxTrend = max(1, (int) $trend->max('total'));

        /*
        |--------------------------------------------------------------------------
        | Aktivitas terbaru
        |--------------------------------------------------------------------------
        */

        $recentActivities = $this->recentActivities();

        return view('manpower', [
            'contentView' => 'manpower.dashboard',
            'bulan' => $bulan,
            'periodeLabel' => $periode->translatedFormat('F Y'),
            'summary' => [
                'total' => $totalTersimpan,
                'month' => $totalBulan,
                'active' => $stSpAktif,
                'expired' => $stSpExpired,
            ],
            'features' => $features,
            'trend' => $trend,
            'maxTrend' => $maxTrend,
            'recentActivities' => $recentActivities,
            'stSpStatus' => [
                'active' => $stSpAktif,
                'expired' => $stSpExpired,
                'total' => $stSpAktif + $stSpExpired,
            ],
        ]);
    }

    private function validMonth(mixed $month): string
    {
        $month = (string) $month;

        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)
            ? $month
            : now()->format('Y-m');
    }

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    return $table;
                }
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    private function safeCount(?string $table): int
    {
        if (! $table) {
            return 0;
        }

        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            return (int) DB::table($table)->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeCountWhere(
        string $table,
        callable $callback
    ): int {
        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            $query = DB::table($table);
            $callback($query);

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeCountByPeriod(
        string $table,
        string $dateColumn,
        Carbon $start,
        Carbon $end,
        ?callable $callback = null
    ): int {
        try {
            if (
                ! Schema::hasTable($table)
                || ! Schema::hasColumn($table, $dateColumn)
            ) {
                return 0;
            }

            $query = DB::table($table)->whereBetween(
                $dateColumn,
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            );

            if ($callback) {
                $callback($query);
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeCountOptionalModuleByPeriod(
        ?string $table,
        Carbon $start,
        Carbon $end
    ): int {
        if (! $table) {
            return 0;
        }

        foreach (
            [
                'tanggal',
                'tanggal_surat',
                'date',
                'created_at',
                'updated_at',
            ] as $column
        ) {
            try {
                if (Schema::hasColumn($table, $column)) {
                    return $this->safeCountByPeriod(
                        $table,
                        $column,
                        $start,
                        $end
                    );
                }
            } catch (Throwable) {
                return 0;
            }
        }

        return 0;
    }

    private function recentActivities(): Collection
    {
        $activities = collect();

        try {
            if (Schema::hasTable('surat_keluar')) {
                DB::table('surat_keluar')
                    ->select([
                        'id',
                        'tanggal_surat',
                        'nomor_surat',
                        'tujuan_surat',
                        'nama',
                    ])
                    ->orderByDesc('tanggal_surat')
                    ->orderByDesc('id')
                    ->limit(6)
                    ->get()
                    ->each(function ($item) use ($activities) {
                        $activities->push([
                            'date' => $item->tanggal_surat,
                            'module' => 'Document Out',
                            'title' => $item->nomor_surat
                                ?: 'Dokumen tanpa nomor surat',
                            'description' => trim(
                                ($item->tujuan_surat ?: '-')
                                .' · '
                                .($item->nama ?: '-')
                            ),
                            'url' => route('document-out.index'),
                            'tone' => 'navy',
                        ]);
                    });
            }
        } catch (Throwable) {
            // Dashboard tetap dapat dibuka.
        }

        try {
            if (Schema::hasTable('apd_requests')) {
                DB::table('apd_requests')
                    ->select([
                        'id',
                        'tanggal_pengajuan',
                        'nrp',
                        'nama',
                        'status_sepatu',
                    ])
                    ->orderByDesc('tanggal_pengajuan')
                    ->orderByDesc('id')
                    ->limit(6)
                    ->get()
                    ->each(function ($item) use ($activities) {
                        $activities->push([
                            'date' => $item->tanggal_pengajuan,
                            'module' => 'Monitoring APD',
                            'title' => $item->nama ?: 'Pengajuan APD',
                            'description' => 'NRP '
                                .($item->nrp ?: '-')
                                .' · Status Sepatu '
                                .($item->status_sepatu ?: '-'),
                            'url' => route('apd.index'),
                            'tone' => 'green',
                        ]);
                    });
            }
        } catch (Throwable) {
            // Dashboard tetap dapat dibuka.
        }

        try {
            if (Schema::hasTable('coaching_counsellings')) {
                DB::table('coaching_counsellings')
                    ->select([
                        'id',
                        'tanggal',
                        'nrp',
                        'materi',
                        'dibuat_oleh',
                    ])
                    ->orderByDesc('tanggal')
                    ->orderByDesc('id')
                    ->limit(6)
                    ->get()
                    ->each(function ($item) use ($activities) {
                        $activities->push([
                            'date' => $item->tanggal,
                            'module' => 'Coaching & Counselling',
                            'title' => $item->materi ?: 'Pembinaan',
                            'description' => 'NRP '
                                .($item->nrp ?: '-')
                                .' · '
                                .($item->dibuat_oleh ?: '-'),
                            'url' => route(
                                'cc-st-sp.coaching.index'
                            ),
                            'tone' => 'cyan',
                        ]);
                    });
            }
        } catch (Throwable) {
            // Dashboard tetap dapat dibuka.
        }

        try {
            if (Schema::hasTable('st_sp_records')) {
                DB::table('st_sp_records')
                    ->select([
                        'id',
                        'tanggal',
                        'nrp',
                        'jenis_pelanggaran',
                        'jenis',
                        'status',
                    ])
                    ->orderByDesc('tanggal')
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get()
                    ->each(function ($item) use ($activities) {
                        $isTeguran = $item->jenis === 'TEGURAN';

                        $activities->push([
                            'date' => $item->tanggal,
                            'module' => $isTeguran
                                ? 'Surat Teguran'
                                : 'Surat Peringatan',
                            'title' => $item->jenis
                                ?: 'ST/SP',
                            'description' => 'NRP '
                                .($item->nrp ?: '-')
                                .' · '
                                .($item->jenis_pelanggaran ?: '-')
                                .' · '
                                .($item->status ?: '-'),
                            'url' => $isTeguran
                                ? route('cc-st-sp.teguran.index')
                                : route(
                                    'cc-st-sp.peringatan.index'
                                ),
                            'tone' => $isTeguran
                                ? 'yellow'
                                : 'red',
                        ]);
                    });
            }
        } catch (Throwable) {
            // Dashboard tetap dapat dibuka.
        }

        return $activities
            ->sortByDesc(function (array $activity) {
                try {
                    return Carbon::parse(
                        $activity['date']
                    )->timestamp;
                } catch (Throwable) {
                    return 0;
                }
            })
            ->take(10)
            ->values();
    }
}