<?php

namespace App\Services;

use App\Models\EArchiveLink;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AdminAllDashboardLiveService
{
    public function __construct(
        private readonly SuggestionSystemService $suggestion,
        private readonly IfutsService $ifuts,
        private readonly McuFuInternalService $mcuFu
    ) {
    }

    /**
     * Snapshot ringkas Dashboard Admin All.
     *
     * Filter periode:
     * - Suggestion System : SUBMIT_AT
     * - IFUTS             : TGL_OUT
     * - MCU & FU          : JADWAL MCU
     * - E-Arsip           : tidak terpengaruh periode
     *
     * Setiap module diisolasi try/catch agar satu sumber yang lambat
     * tidak membuat seluruh Control Center rusak.
     */
    public function snapshot(
        ?string $requestedYear = null,
        ?string $requestedMonth = null
    ): array {
        $currentYear = (int) now('Asia/Jakarta')->year;

        $year = preg_match(
            '/^\d{4}$/',
            trim((string) $requestedYear)
        ) === 1
            ? (int) $requestedYear
            : $currentYear;

        $monthRaw = trim((string) $requestedMonth);
        $month = ctype_digit($monthRaw)
            ? (int) $monthRaw
            : null;

        if ($month !== null && ($month < 1 || $month > 12)) {
            $month = null;
        }

        $result = [
            'period' => [
                'year' => $year,
                'month' => $month,
                'current_year' => $currentYear,
                'available_years' => [$year, $currentYear],
            ],

            'suggestion' => [
                'connected' => false,
                'total' => 0,
                'pending_gl_qcc' => 0,
                'pending_sh' => 0,
                'latest' => [],
                'message' => null,
            ],

            'ifuts' => [
                'connected' => false,
                'total' => 0,
                'regular_out' => 0,
                'additional_out' => 0,
                'cache_mode' => null,
                'fallback' => false,
                'message' => null,
            ],

            'mcu' => [
                'connected' => false,
                'total' => 0,
                'mcu_done' => 0,
                'fit_to_work' => 0,
                'follow_up' => 0,
                'priority_total' => 0,
                'expired' => 0,
                'overdue_fu' => 0,
                'message' => null,
            ],

            'archive' => [
                'connected' => false,
                'total' => 0,
                'active' => 0,
                'categories' => 0,
            ],
        ];

        /* Suggestion System */
        try {
            $data = $this->suggestion->getData();
            $rows = $data['database']['rows'] ?? [];

            $dashboard = $this->suggestion->buildDashboard(
                $rows,
                $month,
                $year
            );

            $periodRows = is_array($dashboard['rows'] ?? null)
                ? $dashboard['rows']
                : [];

            $pendingGl = 0;
            $pendingSh = 0;

            foreach ($periodRows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $gl = strtoupper(
                    trim((string) ($row['STATUS_GL_QCC'] ?? ''))
                );

                $sh = strtoupper(
                    trim((string) ($row['STATUS_SH'] ?? ''))
                );

                if (in_array($gl, ['PENDING', 'WAITING'], true)) {
                    $pendingGl++;
                }

                if ($sh === 'PENDING') {
                    $pendingSh++;
                }
            }

            $result['suggestion'] = [
                'connected' => true,
                'total' => (int) ($dashboard['total'] ?? count($periodRows)),
                'pending_gl_qcc' => $pendingGl,
                'pending_sh' => $pendingSh,
                'latest' => array_slice($periodRows, 0, 5),
                'message' => null,
            ];

            $result['period']['available_years'] = array_merge(
                $result['period']['available_years'],
                array_map('intval', $dashboard['available_years'] ?? [])
            );
        } catch (Throwable $e) {
            report($e);
            $result['suggestion']['message'] = 'Suggestion System belum dapat disinkronkan.';
        }

        /* IFUTS — tetap READ ONLY. */
        try {
            $data = $this->ifuts->getData();
            $dashboard = $this->ifuts->buildDashboard(
                $data['rows'] ?? [],
                [
                    'year' => $year,
                    'month' => $month,
                ]
            );

            $source = is_array($data['source'] ?? null)
                ? $data['source']
                : [];

            $summary = is_array($dashboard['summary'] ?? null)
                ? $dashboard['summary']
                : [];

            $result['ifuts'] = [
                'connected' => true,
                'total' => (int) ($summary['total'] ?? 0),
                'regular_out' => (int) ($summary['regular_out'] ?? 0),
                'additional_out' => (int) ($summary['additional_out'] ?? 0),
                'cache_mode' => $source['cache_mode'] ?? null,
                'fallback' => (bool) ($source['is_fallback_cache'] ?? false),
                'message' => null,
            ];

            $result['period']['available_years'] = array_merge(
                $result['period']['available_years'],
                array_map('intval', $dashboard['available_years'] ?? [])
            );
        } catch (Throwable $e) {
            report($e);
            $result['ifuts']['message'] = 'IFUTS sedang menyiapkan sinkronisasi Google Sheets.';
        }

        /* MCU & FU — dashboard periode + prioritas kondisi saat ini. */
        try {
            $dashboard = $this->mcuFu->dashboard(
                $year,
                $month,
                null
            );

            $summary = is_array($dashboard['summary'] ?? null)
                ? $dashboard['summary']
                : [];

            $priority = $this->mcuFu->priorityData();
            $prioritySummary = is_array($priority['summary'] ?? null)
                ? $priority['summary']
                : [];

            $result['mcu'] = [
                'connected' => true,
                'total' => (int) ($summary['total_data'] ?? 0),
                'mcu_done' => (int) ($summary['mcu_done'] ?? 0),
                'fit_to_work' => (int) ($summary['fit_to_work'] ?? 0),
                'follow_up' => (int) ($summary['hasil_follow_up'] ?? 0),
                'priority_total' => (int) ($prioritySummary['total'] ?? 0),
                'expired' => (int) ($prioritySummary['expired'] ?? 0),
                'overdue_fu' => (int) ($prioritySummary['overdue_fu'] ?? 0),
                'message' => null,
            ];

            $mcuYears = $dashboard['filters']['years'] ?? [];
            $result['period']['available_years'] = array_merge(
                $result['period']['available_years'],
                array_map('intval', is_array($mcuYears) ? $mcuYears : [])
            );
        } catch (Throwable $e) {
            report($e);
            $result['mcu']['message'] = 'MCU & FU sedang menyiapkan sinkronisasi data.';
        }

        /* E-Arsip tidak memakai filter periode. */
        try {
            if (Schema::hasTable('e_archive_links')) {
                $base = EArchiveLink::query();

                $result['archive'] = [
                    'connected' => true,
                    'total' => (clone $base)->count(),
                    'active' => (clone $base)->where('is_active', true)->count(),
                    'categories' => (clone $base)
                        ->whereNotNull('category')
                        ->where('category', '<>', '')
                        ->distinct('category')
                        ->count('category'),
                ];
            }
        } catch (Throwable $e) {
            report($e);
        }

        $years = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $result['period']['available_years']
                    ),
                    static fn (int $value): bool => $value >= 2000 && $value <= 2100
                )
            )
        );

        rsort($years, SORT_NUMERIC);
        $result['period']['available_years'] = $years;

        return $result;
    }
}
