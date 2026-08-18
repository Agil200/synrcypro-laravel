<?php

namespace App\Http\Controllers;

use App\Models\ApdRequest;
use App\Models\CoachingCounselling;
use App\Models\StSpRecord;
use App\Services\EmployeeMasterService;
use App\Services\McuFuInternalService;
use App\Services\SafetyShoeService;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class OperatorPortalController extends Controller
{
    private const SESSION_KEY = 'operator_portal';
    private const SESSION_LIFETIME_MINUTES = 120;

    public function begin(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('operator.access');
    }

    public function accessForm(Request $request): View|RedirectResponse
    {
        if ($this->verifiedNrp($request) !== null) {
            return redirect()->route('operator.dashboard');
        }

        return view('operator.access');
    }

    public function verify(
        Request $request,
        EmployeeMasterService $employeeMaster
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'nrp' => ['required', 'string', 'max:50'],
                'tanggal_lahir' => [
                    'required',
                    'string',
                    'regex:/^\d{8}$/',
                ],
            ],
            [
                'nrp.required' => 'NRP wajib diisi.',
                'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
                'tanggal_lahir.regex' =>
                    'Tanggal lahir harus terdiri dari 8 angka dengan format DDMMYYYY.',
            ]
        );

        $nrp = $this->normalizeNrp(
            (string) $validated['nrp']
        );
        $birthDate = $this->normalizeBirthDateInput(
            (string) $validated['tanggal_lahir']
        );

        if ($birthDate === null) {
            return back()
                ->withInput([
                    'nrp' => $validated['nrp'],
                    'tanggal_lahir' => $validated['tanggal_lahir'],
                ])
                ->withErrors([
                    'tanggal_lahir' =>
                        'Tanggal lahir tidak valid. Gunakan 8 angka dengan format DDMMYYYY.',
                ]);
        }

        try {
            $snapshot = $employeeMaster->snapshot();
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput(['nrp' => $validated['nrp']])
                ->withErrors([
                    'access' =>
                        'Database karyawan belum dapat dibaca. Silakan coba kembali atau hubungi admin.',
                ]);
        }

        $employee = collect(
            $snapshot['employees'] ?? []
        )->first(function (mixed $row) use ($nrp): bool {
            return is_array($row)
                && $this->normalizeNrp(
                    (string) ($row['nrp'] ?? '')
                ) === $nrp;
        });

        $storedBirthDate = is_array($employee)
            ? $this->normalizeBirthDate(
                $employee['tanggal_lahir'] ?? null
            )
            : null;

        if (
            ! is_array($employee)
            || $storedBirthDate === null
            || ! hash_equals($storedBirthDate, $birthDate)
        ) {
            return back()
                ->withInput(['nrp' => $validated['nrp']])
                ->withErrors([
                    'access' =>
                        'NRP atau tanggal lahir tidak sesuai dengan database karyawan.',
                ]);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, [
            'nrp' => $nrp,
            'verified_at' => now()->timestamp,
        ]);

        return redirect()->route('operator.dashboard');
    }

    public function dashboard(
        Request $request,
        EmployeeMasterService $employeeMaster,
        SafetyShoeService $safetyShoes,
        McuFuInternalService $mcuFuService,
        BNNController $bnnController
    ): View|RedirectResponse {
        $nrp = $this->verifiedNrp($request);

        if ($nrp === null) {
            return redirect()
                ->route('operator.access')
                ->with(
                    'error',
                    'Sesi operator belum diverifikasi atau sudah berakhir.'
                );
        }

        try {
            $snapshot = $employeeMaster->snapshot();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('operator.access')
                ->with(
                    'error',
                    'Database karyawan belum dapat dibaca.'
                );
        }

        $employee = collect(
            $snapshot['employees'] ?? []
        )->first(function (mixed $row) use ($nrp): bool {
            return is_array($row)
                && $this->normalizeNrp(
                    (string) ($row['nrp'] ?? '')
                ) === $nrp;
        });

        if (! is_array($employee)) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()
                ->route('operator.access')
                ->with(
                    'error',
                    'Data operator tidak lagi ditemukan pada database.'
                );
        }

        $apdRequests = collect();
        $coachingRecords = collect();
        $teguranRecords = collect();
        $peringatanRecords = collect();

        if (Schema::hasTable('apd_requests')) {
            $apdRequests = ApdRequest::query()
                ->with('pickup')
                ->whereRaw(
                    "UPPER(REPLACE(TRIM(nrp), ' ', '')) = ?",
                    [$nrp]
                )
                ->latest('tanggal_pengajuan')
                ->latest('id')
                ->get();
        }

        if (Schema::hasTable('coaching_counsellings')) {
            $coachingRecords = CoachingCounselling::query()
                ->whereRaw(
                    "UPPER(REPLACE(TRIM(nrp), ' ', '')) = ?",
                    [$nrp]
                )
                ->latest('tanggal')
                ->latest('id')
                ->get();
        }

        if (Schema::hasTable('st_sp_records')) {
            $stSpRecords = StSpRecord::query()
                ->whereRaw(
                    "UPPER(REPLACE(TRIM(nrp), ' ', '')) = ?",
                    [$nrp]
                )
                ->latest('tanggal')
                ->latest('id')
                ->get();

            $teguranRecords = $stSpRecords
                ->where('jenis', 'TEGURAN')
                ->values();

            $peringatanRecords = $stSpRecords
                ->where('jenis', '!=', 'TEGURAN')
                ->values();
        }

        $summary = [
            'apd' => $apdRequests->count(),
            'coaching' => $coachingRecords->count(),
            'teguran' => $teguranRecords->count(),
            'peringatan' => $peringatanRecords->count(),
        ];

        $shoeEligibility = [
            'available' => false,
            'found' => false,
            'has_history' => false,
            'eligible' => false,
            'last_taken_date' => null,
            'tanggal' => null,
            'eligible_at' => null,
            'tanggal_bisa_ajukan' => null,
            'days_remaining' => null,
            'source' => null,
            'is_stale' => false,
        ];

        try {
            $shoeEligibility = $safetyShoes->eligibilityFor(
                $nrp
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        $localLastShoe = $apdRequests
            ->filter(fn (ApdRequest $apd): bool =>
                (bool) $apd->item_sepatu_safety
                && filled($apd->pickup?->tanggal_pengambilan)
            )
            ->sortByDesc(fn (ApdRequest $apd): int =>
                $apd->pickup->tanggal_pengambilan->timestamp
            )
            ->first();

        if ($localLastShoe?->pickup?->tanggal_pengambilan) {
            $localEligibility = array_merge(
                $safetyShoes->eligibilityFromDate(
                    $localLastShoe->pickup->tanggal_pengambilan,
                    (string) ($employee['nama'] ?? '')
                ),
                [
                    'nrp' => $nrp,
                    'source' => 'apd_pickups',
                ]
            );

            $sheetDate = $shoeEligibility['last_taken_date'] ?? null;

            if (
                ! $sheetDate
                || Carbon::parse($localEligibility['last_taken_date'])
                    ->gt(Carbon::parse($sheetDate))
            ) {
                $shoeEligibility = $localEligibility;
            }
        }

        /*
         * Pengecekan Status Realtime MCU & Follow Up
         */
        $mcuReminder = null;
        try {
            $mcuRows = $mcuFuService->rows();
            $mcuItem = collect($mcuRows)->first(function ($r) use ($nrp) {
                return $this->normalizeNrp((string) ($r['nrp'] ?? '')) === $nrp;
            });

            if ($mcuItem) {
                $statusFu = strtoupper(trim((string) ($mcuItem['status_fu'] ?? '')));
                $hasilMcu = strtoupper(trim((string) ($mcuItem['hasil_mcu'] ?? '')));
                $jadwalFu = trim((string) ($mcuItem['jadwal_fu'] ?? ''));
                $expMcu = trim((string) ($mcuItem['exp_mcu'] ?? ''));

                if ($statusFu !== 'COMPLETED' && $statusFu !== 'FIT TO WORK' && $hasilMcu !== 'FIT TO WORK') {
                    $mcuReminder = [
                        'active' => true,
                        'hasil_mcu' => $mcuItem['hasil_mcu'] ?? '-',
                        'follow_up_1' => $mcuItem['follow_up_1'] ?? null,
                        'follow_up_2' => $mcuItem['follow_up_2'] ?? null,
                        'follow_up_3' => $mcuItem['follow_up_3'] ?? null,
                        'jadwal_fu' => $jadwalFu ?: null,
                        'exp_mcu' => $expMcu ?: null,
                        'status_fu' => $statusFu ?: 'MENUNGGU TINDAK LANJUT',
                    ];
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        /*
         * Pengecekan Status Realtime BNN / Follow Up BNN
         */
        $bnnReminder = null;
        try {
            // Memanggil langsung method public monitoringSnapshot dari BNNController
            $bnnSnapshot = $bnnController->monitoringSnapshot();
            $bnnRows = $bnnSnapshot['rows'] ?? [];

            $bnnItem = collect($bnnRows)->first(function ($r) use ($nrp) {
                return $this->normalizeNrp((string) ($r['nrp'] ?? '')) === $nrp;
            });

            if ($bnnItem) {
                $statusTest = strtoupper(trim((string) ($bnnItem['status_test'] ?? '')));
                $tglPemeriksaan = trim((string) ($bnnItem['tanggal_pemeriksaan'] ?? ''));

                if ($statusTest !== 'SUDAH TEST' && $statusTest !== 'DONE' && $statusTest !== 'COMPLETED') {
                    $bnnReminder = [
                        'active' => true,
                        'tanggal_pemeriksaan' => $tglPemeriksaan ?: null,
                        'akomodasi' => $bnnItem['akomodasi'] ?? '-',
                        'status_test' => $statusTest ?: 'BELUM TEST',
                    ];
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return view('operator.dashboard', [
            'employee' => $employee,
            'apdRequests' => $apdRequests,
            'coachingRecords' => $coachingRecords,
            'teguranRecords' => $teguranRecords,
            'peringatanRecords' => $peringatanRecords,
            'summary' => $summary,
            'shoeEligibility' => $shoeEligibility,
            'mcuReminder' => $mcuReminder,
            'bnnReminder' => $bnnReminder,
            'snapshotStale' => (bool) data_get(
                $snapshot,
                'meta.is_stale',
                false
            ),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function verifiedNrp(Request $request): ?string
    {
        $payload = $request->session()->get(
            self::SESSION_KEY
        );

        if (! is_array($payload)) {
            return null;
        }

        $nrp = $this->normalizeNrp(
            (string) ($payload['nrp'] ?? '')
        );
        $verifiedAt = (int) (
            $payload['verified_at'] ?? 0
        );

        if ($nrp === '' || $verifiedAt <= 0) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        $expiresAt = Carbon::createFromTimestamp(
            $verifiedAt
        )->addMinutes(self::SESSION_LIFETIME_MINUTES);

        if (now()->greaterThan($expiresAt)) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $nrp;
    }

    private function normalizeNrp(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = preg_replace(
                '/\.0+$/',
                '',
                $value
            ) ?? $value;
        }

        return strtoupper(
            preg_replace('/\s+/', '', $value) ?? $value
        );
    }

    private function normalizeBirthDateInput(string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) !== 8) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!dmY',
            $digits
        );

        $errors = DateTimeImmutable::getLastErrors();
        $hasErrors = is_array($errors)
            && (
                ($errors['warning_count'] ?? 0) > 0
                || ($errors['error_count'] ?? 0) > 0
            );

        if ($date === false || $hasErrors) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function normalizeBirthDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        $monthMap = [
            'Januari' => 'January',
            'Februari' => 'February',
            'Maret' => 'March',
            'April' => 'April',
            'Mei' => 'May',
            'Juni' => 'June',
            'Juli' => 'July',
            'Agustus' => 'August',
            'September' => 'September',
            'Oktober' => 'October',
            'November' => 'November',
            'Desember' => 'December',
        ];

        $normalizedText = str_ireplace(
            array_keys($monthMap),
            array_values($monthMap),
            $value
        );

        foreach (
            [
                'Y-m-d',
                'd/m/Y',
                'd-m-Y',
                'd.m.Y',
                'd-M-Y',
                'd M Y',
                'd F Y',
                'm/d/Y',
                'Y/m/d',
                'd/m/y',
                'd-m-y',
            ] as $format
        ) {
            $date = DateTimeImmutable::createFromFormat(
                '!'.$format,
                $normalizedText
            );

            $errors = DateTimeImmutable::getLastErrors();
            $hasErrors = is_array($errors)
                && (
                    ($errors['warning_count'] ?? 0) > 0
                    || ($errors['error_count'] ?? 0) > 0
                );

            if ($date !== false && ! $hasErrors) {
                return $date->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($normalizedText)
                ->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }
}