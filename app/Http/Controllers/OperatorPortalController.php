<?php

namespace App\Http\Controllers;

use App\Models\ApdRequest;
use App\Models\CoachingCounselling;
use App\Models\StSpRecord;
use App\Services\EmployeeMasterService;
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

    /**
     * Tombol SIGN IN AS GUEST diarahkan ke halaman verifikasi operator.
     */
    public function begin(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('operator.access');
    }

    /**
     * Form NRP dan tanggal lahir sebelum data operator ditampilkan.
     */
    public function accessForm(Request $request): View|RedirectResponse
    {
        if ($this->verifiedNrp($request) !== null) {
            return redirect()->route('operator.dashboard');
        }

        return view('operator.access');
    }

    /**
     * Memverifikasi operator terhadap MASTER_DATABASE Google Sheets.
     */
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

        /*
         * Pesan sengaja dibuat sama agar sistem tidak membocorkan apakah
         * NRP atau tanggal lahir yang salah.
         */
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

    /**
     * Dashboard pribadi operator. Seluruh data bersifat read-only.
     */
    public function dashboard(
        Request $request,
        EmployeeMasterService $employeeMaster,
        SafetyShoeService $safetyShoes
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

        return view('operator.dashboard', [
            'employee' => $employee,
            'apdRequests' => $apdRequests,
            'coachingRecords' => $coachingRecords,
            'teguranRecords' => $teguranRecords,
            'peringatanRecords' => $peringatanRecords,
            'summary' => $summary,
            'shoeEligibility' => $shoeEligibility,
            'snapshotStale' => (bool) data_get(
                $snapshot,
                'meta.is_stale',
                false
            ),
        ]);
    }

    /**
     * Keluar dari portal operator tanpa memengaruhi akun Google/admin.
     */
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

    /**
     * Mengubah input operator DDMMYYYY menjadi Y-m-d.
     */
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

    /**
     * Mengubah berbagai format tanggal dari Spreadsheet menjadi Y-m-d.
     */
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
