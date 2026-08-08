<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtrCommitRequest;
use App\Http\Requests\AtrPreviewRequest;
use App\Http\Requests\StoreAtrCoachingRequest;
use App\Models\AtrCoachingAttachment;
use App\Models\AtrCoachingCounseling;
use App\Models\AtrCoachingHistory;
use App\Models\AtrImport;
use App\Models\AtrRecord;
use App\Services\AtrImportService;
use App\Services\AtrPicRosterService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AtrController extends Controller
{
    public function __construct(
        private readonly AtrImportService $importService,
        private readonly AtrPicRosterService $picRosterService
    ) {
    }

    public function summary(Request $request): View
    {
        /*
         * Selalu sediakan nilai periode untuk Blade.
         * Apabila database ATR masih kosong, gunakan bulan berjalan agar
         * resources/views/database/atr/summary.blade.php tidak menerima
         * variabel $period yang tidak tersedia.
         */
        $period = $this->selectedPeriod($request)
            ?? now()->startOfMonth()->format('Y-m-d');

        $latestImportId = $this->latestImportIdForPeriod($period);

        $base = AtrRecord::query()
            ->whereHas(
                'import',
                fn ($query) => $query->where('status', 'COMPLETED')
            )
            ->when(
                $latestImportId !== null,
                fn ($query) => $query->where('atr_import_id', $latestImportId)
            )
            ->whereDate('period', $period);

        $positionOptions = (clone $base)
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->distinct()
            ->orderBy('position')
            ->pluck('position');

        $filtered = $this->applyFilters(clone $base, $request);

        $stats = [
            'total' => (clone $filtered)->count(),
            'aman' => (clone $filtered)->where('status', 'AMAN')->count(),
            'monitoring' => (clone $filtered)->where('status', 'MONITORING')->count(),
            'pemanggilan' => (clone $filtered)->where('status', 'PEMANGGILAN')->count(),
            'no_data' => (clone $filtered)->where('status', 'NO_DATA')->count(),
            'sakit' => (int) (clone $filtered)->sum('sick'),
            'izin' => (int) (clone $filtered)->sum('permission'),
            'alpa' => (int) (clone $filtered)->sum('alpha'),
        ];

        $pemanggilanIds = (clone $filtered)
            ->where('status', 'PEMANGGILAN')
            ->pluck('id');

        $sudah = $pemanggilanIds->isEmpty()
            ? 0
            : AtrCoachingCounseling::query()
                ->whereIn('atr_record_id', $pemanggilanIds)
                ->where('status', 'COMPLETED')
                ->distinct()
                ->count('atr_record_id');

        $progress = [
            'total' => $stats['pemanggilan'],
            'sudah' => $sudah,
            'belum' => max(0, $stats['pemanggilan'] - $sudah),
            'percentage' => $stats['pemanggilan'] > 0
                ? round(($sudah / $stats['pemanggilan']) * 100, 1)
                : 0.0,
        ];

        $topAbsences = (clone $filtered)
            ->orderByRaw('(sick + permission + alpha) DESC')
            ->orderByRaw('CASE WHEN atr IS NULL THEN 1 ELSE 0 END')
            ->orderBy('atr')
            ->limit(10)
            ->get();

        return $this->render(
            'database.atr.summary',
            'atr-summary',
            [
                'period' => $period,
                'periodOptions' => $this->periodOptions($period),
                'positionOptions' => $positionOptions,
                // Alias sementara agar Blade lama yang masih memakai jobOptions tidak error.
                'jobOptions' => $positionOptions,
                'stats' => $stats,
                'progress' => $progress,
                'topAbsences' => $topAbsences,
                'thresholds' => [
                    'aman' => (float) config('atr.aman_minimum', 98.5),
                    'monitoring' => (float) config('atr.monitoring_minimum', 95.0),
                ],
                'hasData' => $stats['total'] > 0,
            ]
        );
    }

    public function upload(Request $request): View
    {
        $preview = null;
        $token = (string) $request->session()->get('atr_last_preview_token', '');

        if ($token !== '') {
            $preview = $request->session()->get('atr_preview.' . $token);
        }

        return $this->render('database.atr.upload', 'atr-upload', [
            'preview' => $preview,
        ]);
    }

    public function preview(AtrPreviewRequest $request): RedirectResponse
    {
        try {
            $preview = $this->importService->createPreview(
                $request->file('atr_file')
            );

            $periods = collect($preview['periods'] ?? [])
                ->filter()
                ->unique()
                ->values();

            if ($periods->count() > 1) {
                $storedPath = (string) ($preview['stored_path'] ?? '');

                if ($storedPath !== '') {
                    Storage::disk('local')->delete($storedPath);
                }

                throw new \RuntimeException(
                    'Satu file ATR hanya boleh berisi satu periode. '
                    . 'Pisahkan file per bulan lalu preview ulang.'
                );
            }

            $preview['period_conflict'] =
                $this->importService->conflictForPreview($preview);

            $token = $preview['preview_token'];
            $sessionPreview = $preview;
            unset($sessionPreview['records']);
            $sessionPreview['row_errors'] = array_slice(
                $sessionPreview['row_errors'] ?? [],
                0,
                100
            );

            $request->session()->put(
                'atr_preview.' . $token,
                $sessionPreview
            );
            $request->session()->put('atr_last_preview_token', $token);

            return redirect()
                ->route('database.atr.upload')
                ->with('success', 'Preview file ATR berhasil dibuat.');
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'atr_file' => 'Preview gagal: ' . $exception->getMessage(),
                ]);
        }
    }

    public function discardPreview(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preview_token' => ['required', 'uuid'],
        ]);

        $token = (string) $validated['preview_token'];
        $preview = $request->session()->get('atr_preview.' . $token);

        if (is_array($preview)) {
            $storedPath = trim((string) ($preview['stored_path'] ?? ''));

            if ($storedPath !== '') {
                Storage::disk('local')->delete($storedPath);
            }
        }

        $request->session()->forget('atr_preview.' . $token);

        if (
            (string) $request->session()->get('atr_last_preview_token', '')
            === $token
        ) {
            $request->session()->forget('atr_last_preview_token');
        }

        return redirect()
            ->route('database.atr.upload')
            ->with('success', 'Preview ATR dibatalkan. Database tidak berubah.');
    }

    public function commit(AtrCommitRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $token = (string) $validated['preview_token'];
        $preview = $request->session()->get('atr_preview.' . $token);

        if (! is_array($preview)) {
            return back()->withErrors([
                'atr_file' => 'Preview tidak ditemukan atau sesi sudah kedaluwarsa.',
            ]);
        }

        try {
            $import = $this->importService->commit(
                $preview,
                auth()->id(),
                (string) $validated['import_action'],
                isset($validated['existing_import_id'])
                    ? (int) $validated['existing_import_id']
                    : null
            );

            $request->session()->forget('atr_preview.' . $token);
            $request->session()->forget('atr_last_preview_token');

            $modeLabel = match ($import->import_mode) {
                'REPLACE' => 'Data periode berhasil diganti',
                'APPEND' => 'Data tambahan berhasil digabungkan',
                default => 'Import berhasil',
            };

            return redirect()
                ->route('database.atr.history')
                ->with(
                    'success',
                    $modeLabel . '. '
                    . $import->imported_rows
                    . ' baris ATR menjadi snapshot aktif.'
                );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'atr_file' => 'Import gagal: ' . $exception->getMessage(),
            ]);
        }
    }

    public function history(Request $request): View
    {
        $year = trim((string) $request->query('year', ''));
        $month = trim((string) $request->query('month', ''));
        $status = strtoupper(trim((string) $request->query('status', '')));
        $search = trim((string) $request->query('search', ''));

        if (! preg_match('/^\d{4}$/', $year)) {
            $year = '';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
            $month = '';
        }

        $allowedStatuses = [
            'COMPLETED',
            'REPLACED',
            'CANCELLED',
            'PROCESSING',
            'FAILED',
        ];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $periodRows = AtrImport::query()
            ->whereNotNull('period_min')
            ->orderByDesc('period_min')
            ->get(['period_min']);

        $yearOptions = $periodRows
            ->map(fn (AtrImport $import) => $import->period_min?->format('Y'))
            ->filter()
            ->unique()
            ->values();

        $monthsByYear = $periodRows
            ->filter(fn (AtrImport $import) => $import->period_min !== null)
            ->groupBy(fn (AtrImport $import) => $import->period_min->format('Y'))
            ->map(
                fn ($rows) => $rows
                    ->map(fn (AtrImport $import) => $import->period_min->format('m'))
                    ->unique()
                    ->sort()
                    ->values()
                    ->all()
            )
            ->all();

        $imports = AtrImport::query()
            ->with([
                'uploader:id,name,email',
                'canceller:id,name,email',
                'replacesImport:id,file_name,status',
                'replacementImport:id,file_name,status,replaces_import_id',
            ])
            ->when(
                $year !== '',
                fn ($query) => $query->whereYear('period_min', (int) $year)
            )
            ->when(
                $month !== '',
                fn ($query) => $query->whereMonth('period_min', (int) $month)
            )
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where('file_name', 'like', '%' . $search . '%');
                }
            )
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return $this->render('database.atr.import-history', 'atr-history', [
            'imports' => $imports,
            'yearOptions' => $yearOptions,
            'monthsByYear' => $monthsByYear,
            'historyFilters' => [
                'year' => $year,
                'month' => $month,
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Membatalkan snapshot import tanpa menghapus histori/file arsip.
     * Data dari import CANCELLED otomatis diabaikan dashboard ATR.
     */
    public function cancelImport(
        Request $request,
        AtrImport $import
    ): RedirectResponse {
        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'cancel_reason.required' => 'Alasan pembatalan import wajib diisi.',
            'cancel_reason.min' => 'Alasan pembatalan import minimal 5 karakter.',
        ]);

        if ($import->status !== 'COMPLETED') {
            return back()->withErrors([
                'import_cancel' => 'Import ini sudah tidak aktif dan tidak dapat dibatalkan lagi.',
            ]);
        }

        $recordIds = $import->records()->pluck('id');

        $activeCoachingCount = $recordIds->isEmpty()
            ? 0
            : AtrCoachingCounseling::query()
                ->whereIn('atr_record_id', $recordIds)
                ->where('status', 'COMPLETED')
                ->count();

        if ($activeCoachingCount > 0) {
            return back()->withErrors([
                'import_cancel' =>
                    'Import tidak dapat dibatalkan karena masih terdapat '
                    . $activeCoachingCount
                    . ' dokumentasi pemanggilan aktif. Batalkan dokumentasi '
                    . 'pemanggilan tersebut terlebih dahulu.',
            ]);
        }

        $reactivatedImport = null;

        DB::transaction(function () use (
            $import,
            $validated,
            &$reactivatedImport
        ): void {
            $import->forceFill([
                'status' => 'CANCELLED',
                'cancellation_reason' => trim((string) $validated['cancel_reason']),
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ])->save();

            if ($import->replaces_import_id) {
                $previous = AtrImport::query()
                    ->lockForUpdate()
                    ->find($import->replaces_import_id);

                if ($previous && $previous->status === 'REPLACED') {
                    $previous->forceFill(['status' => 'COMPLETED'])->save();
                    $reactivatedImport = $previous;
                }
            }
        });

        $message = 'Import ATR berhasil dibatalkan. Riwayat dan file arsip tetap disimpan.';

        if ($reactivatedImport) {
            $message .= ' Snapshot sebelumnya otomatis diaktifkan kembali.';
        }

        return back()->with('success', $message);
    }

    public function calls(Request $request): View
    {
        $period = $this->selectedPeriod($request);
        $latestImportId = $this->latestImportIdForPeriod($period);

        $base = AtrRecord::query()
            ->with([
                'latestCoaching.attachments',
                'latestCancelledCoaching',
            ])
            ->whereHas(
                'import',
                fn ($query) => $query->where('status', 'COMPLETED')
            )
            ->when(
                $latestImportId,
                fn ($query) => $query->where('atr_import_id', $latestImportId)
            )
            ->when($period, fn ($query) => $query->whereDate('period', $period))
            ->where('status', 'PEMANGGILAN');

        $positionOptions = (clone $base)
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->distinct()
            ->orderBy('position')
            ->pluck('position');

        $callStatus = $request->string('call_status')->toString();

        $records = $this->applyFilters($base, $request)
            ->when(
                $callStatus === 'sudah',
                fn ($query) => $query->whereHas(
                    'coachingCounselings',
                    fn ($q) => $q->where('status', 'COMPLETED')
                )
            )
            ->when(
                $callStatus === 'belum',
                fn ($query) => $query
                    ->whereDoesntHave(
                        'coachingCounselings',
                        fn ($q) => $q->where('status', 'COMPLETED')
                    )
                    ->whereDoesntHave(
                        'coachingCounselings',
                        fn ($q) => $q->where('status', 'CANCELLED')
                    )
            )
            ->when(
                $callStatus === 'ulang',
                fn ($query) => $query
                    ->whereDoesntHave(
                        'coachingCounselings',
                        fn ($q) => $q->where('status', 'COMPLETED')
                    )
                    ->whereHas(
                        'coachingCounselings',
                        fn ($q) => $q->where('status', 'CANCELLED')
                    )
            )
            ->orderBy('atr')
            ->paginate(18)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Auto PIC dari MASTER PIC ROSTER
        |--------------------------------------------------------------------------
        |
        | Tidak ada lagi hardcode PC1250/PC2000/DZ/DOZER di controller.
        | Semua posisi ditembakkan ke engine rule Master PIC Roster.
        |
        */
        $records->getCollection()->transform(
            function (AtrRecord $record): AtrRecord {
                $resolved = $this->picRosterService->resolve(
                    (string) $record->position,
                    $record->period
                );

                $record->setAttribute(
                    'pic_roster_resolved',
                    (bool) $resolved['matched']
                );
                $record->setAttribute(
                    'pic_roster_name_resolved',
                    $resolved['pic_primary']
                );
                $record->setAttribute(
                    'pic_roster_group_resolved',
                    $resolved['group_label']
                );
                $record->setAttribute(
                    'pic_roster_rule_resolved',
                    $resolved['rule_pattern']
                );

                return $record;
            }
        );

        $allCallIds = AtrRecord::query()
            ->whereHas(
                'import',
                fn ($query) => $query->where('status', 'COMPLETED')
            )
            ->when(
                $latestImportId,
                fn ($query) => $query->where('atr_import_id', $latestImportId)
            )
            ->when($period, fn ($query) => $query->whereDate('period', $period))
            ->where('status', 'PEMANGGILAN')
            ->pluck('id');

        $completedIds = AtrCoachingCounseling::query()
            ->whereIn('atr_record_id', $allCallIds)
            ->where('status', 'COMPLETED')
            ->pluck('atr_record_id')
            ->unique();

        $recallIds = AtrCoachingCounseling::query()
            ->whereIn('atr_record_id', $allCallIds)
            ->where('status', 'CANCELLED')
            ->when(
                $completedIds->isNotEmpty(),
                fn ($query) => $query->whereNotIn('atr_record_id', $completedIds)
            )
            ->pluck('atr_record_id')
            ->unique();

        $total = $allCallIds->count();
        $sudah = $completedIds->count();
        $ulang = $recallIds->count();
        $belum = max(0, $total - $sudah - $ulang);

        return $this->render(
            'database.atr.call-documentation',
            'atr-calls',
            [
                'records' => $records,
                'period' => $period,
                'periodOptions' => $this->periodOptions(),
                'positionOptions' => $positionOptions,
                // Alias sementara agar Blade lama yang masih memakai jobOptions tidak error.
                'jobOptions' => $positionOptions,
                'callStats' => [
                    'total' => $total,
                    'sudah' => $sudah,
                    'belum' => $belum,
                    'ulang' => $ulang,
                ],
            ]
        );
    }

    public function storeCoaching(
        StoreAtrCoachingRequest $request
    ): RedirectResponse {
        $record = AtrRecord::query()->findOrFail(
            $request->integer('atr_record_id')
        );

        if ($record->status !== 'PEMANGGILAN') {
            return back()->withErrors([
                'atr_record_id' =>
                    'Dokumentasi hanya dapat dibuat untuk status PEMANGGILAN.',
            ]);
        }

        if (
            $record->coachingCounselings()
                ->where('status', 'COMPLETED')
                ->exists()
        ) {
            return back()->withErrors([
                'atr_record_id' =>
                    'Karyawan ini sudah memiliki dokumentasi pemanggilan aktif. '
                    . 'Batalkan dokumentasi sebelumnya jika memang perlu diulang.',
            ]);
        }

        $picResolution = $this->picRosterService->resolve(
            (string) ($record->position ?? ''),
            $record->period
        );

        if (! $picResolution['matched']) {
            return back()->withInput()->withErrors([
                'pic_roster' =>
                    'PIC Roster untuk posisi "'
                    . ($record->position ?: '-')
                    . '" belum terdaftar. '
                    . 'Atur rule pada menu Pengaturan PIC Roster terlebih dahulu.',
            ]);
        }

        $picRosterName = $picResolution['pic_primary'];

        $actorName = $this->currentActorName();
        $leaderName = trim(
            $request->string('leader_name')->toString()
        );

        try {
            DB::transaction(function () use (
                $request,
                $record,
                $picRosterName,
                $actorName,
                $leaderName
            ): void {
                $coaching = AtrCoachingCounseling::query()->create([
                    'atr_record_id' => $record->id,

                    /*
                     * NO FORM resmi perusahaan tetap dipertahankan.
                     * Nomor dokumentasi sistem dibuat otomatis terpisah.
                     */
                    'document_number' => (string) config(
                        'atr.document_number',
                        'PPA-PTBA-F-SHE-14D'
                    ),
                    'system_document_number' => null,

                    'coaching_date' => $request->date('coaching_date'),
                    'shift' => $request->string('shift')->toString(),
                    'location' => $request->string('location')->toString(),
                    'coaching_time' =>
                        $request->string('coaching_time')->toString(),

                    'material_personal' =>
                        $request->boolean('material_personal'),
                    'material_family' =>
                        $request->boolean('material_family'),
                    'material_work' =>
                        $request->boolean('material_work'),

                    'notes' => $request->string('notes')->toString(),

                    /*
                     * PIC Roster = otomatis dari POSISI.
                     * Nama Pimpinan = diisi manual pada form.
                     */
                    'created_by_name' => $picRosterName,
                    'leader_name' => $leaderName,
                    'created_by_user_id' => auth()->id(),

                    'status' => 'COMPLETED',
                    'completed_at' => now(),
                ]);

                $systemDocumentNumber =
                    $this->buildSystemDocumentNumber($coaching);

                $coaching->forceFill([
                    'system_document_number' =>
                        $systemDocumentNumber,
                ])->save();

                AtrCoachingHistory::query()->create([
                    'atr_coaching_counseling_id' => $coaching->id,
                    'action' => 'CREATED',
                    'from_status' => null,
                    'to_status' => 'DRAFT',
                    'actor_user_id' => auth()->id(),
                    'actor_name' => $actorName,
                    'notes' =>
                        'Dokumentasi Coaching & Counseling dibuat.',
                    'meta' => [
                        'system_document_number' =>
                            $systemDocumentNumber,
                        'pic_roster_name' => $picRosterName,
                        'leader_name' => $leaderName,
                    ],
                ]);

                $this->storeAttachment(
                    $request,
                    $coaching,
                    'evidence',
                    'EVIDENCE'
                );

                $this->storeSignatureData(
                    $coaching,
                    $request
                        ->string('creator_signature_data')
                        ->toString(),
                    'EMPLOYEE_SIGNATURE',
                    'tanda-tangan-karyawan'
                );

                $this->storeSignatureData(
                    $coaching,
                    $request
                        ->string('leader_signature_data')
                        ->toString(),
                    'COACH_SIGNATURE',
                    'tanda-tangan-pimpinan'
                );

                AtrCoachingHistory::query()->create([
                    'atr_coaching_counseling_id' => $coaching->id,
                    'action' => 'COMPLETED',
                    'from_status' => 'DRAFT',
                    'to_status' => 'COMPLETED',
                    'actor_user_id' => auth()->id(),
                    'actor_name' => $actorName,
                    'notes' =>
                        'Dokumentasi lengkap: form, bukti, dan tanda tangan tersimpan.',
                    'meta' => [
                        'system_document_number' =>
                            $systemDocumentNumber,
                        'completed_at' =>
                            $coaching->completed_at?->toIso8601String(),
                    ],
                ]);
            });

            return back()->with(
                'success',
                'Dokumentasi Coaching & Counseling berhasil disimpan dan dinyatakan SELESAI.'
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'coaching' =>
                        'Dokumentasi gagal disimpan: '
                        . $exception->getMessage(),
                ]);
        }
    }

    /**
     * Batalkan dokumentasi pemanggilan tanpa menghapus dokumen/lampiran.
     * Setelah status CANCELLED, karyawan dapat didokumentasikan ulang.
     */
    public function cancelCoaching(
        Request $request,
        AtrCoachingCounseling $coaching
    ): RedirectResponse {
        $validated = $request->validate([
            'cancel_reason' => [
                'required',
                'string',
                'min:5',
                'max:500',
            ],
        ], [
            'cancel_reason.required' =>
                'Alasan pembatalan dokumentasi wajib diisi.',
            'cancel_reason.min' =>
                'Alasan pembatalan dokumentasi minimal 5 karakter.',
        ]);

        if ($coaching->status !== 'COMPLETED') {
            return back()->withErrors([
                'coaching_cancel' =>
                    'Dokumentasi ini sudah tidak aktif dan tidak dapat dibatalkan lagi.',
            ]);
        }

        $actorName = $this->currentActorName();
        $reason = trim((string) $validated['cancel_reason']);

        DB::transaction(function () use (
            $coaching,
            $reason,
            $actorName
        ): void {
            $coaching->forceFill([
                'status' => 'CANCELLED',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ])->save();

            AtrCoachingHistory::query()->create([
                'atr_coaching_counseling_id' => $coaching->id,
                'action' => 'CANCELLED',
                'from_status' => 'COMPLETED',
                'to_status' => 'CANCELLED',
                'actor_user_id' => auth()->id(),
                'actor_name' => $actorName,
                'notes' => $reason,
                'meta' => [
                    'system_document_number' =>
                        $coaching->system_document_number,
                    'cancelled_at' =>
                        $coaching->cancelled_at?->toIso8601String(),
                ],
            ]);
        });

        return back()->with(
            'success',
            'Dokumentasi dibatalkan. Status karyawan sekarang PERLU ULANG dan dokumen lama tetap tersimpan untuk audit.'
        );
    }

    public function printCoaching(
        AtrCoachingCounseling $coaching
    ): View {
        $coaching->load(['atrRecord', 'attachments', 'histories.actor', 'creator', 'canceller']);

        return view('database.atr.coaching-print', [
            'coaching' => $coaching,
        ]);
    }


    public function attachment(
        AtrCoachingCounseling $coaching,
        AtrCoachingAttachment $attachment
    ): BinaryFileResponse {
        abort_unless(
            $attachment->atr_coaching_counseling_id === $coaching->id,
            404
        );

        abort_unless(
            Storage::disk('local')->exists($attachment->stored_path),
            404,
            'Lampiran tidak ditemukan.'
        );

        return response()->file(
            Storage::disk('local')->path($attachment->stored_path),
            [
                'Content-Type' => $attachment->mime_type
                    ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'
                    . addslashes($attachment->original_name)
                    . '"',
            ]
        );
    }

    public function downloadTemplate(): StreamedResponse
    {
        $path = resource_path('templates/ATR_IMPORT_PRODUKSI.xlsx');

        /*
         * Kirim file langsung dari resources/templates menggunakan streaming.
         * Nama download diberi versi hash supaya browser tidak membuka file lama
         * yang kebetulan memiliki nama sama di folder Downloads.
         */
        clearstatcache(true, $path);

        abort_unless(
            is_file($path) && is_readable($path),
            404,
            'Template ATR belum tersedia atau tidak dapat dibaca.'
        );

        $size = filesize($path);
        $modifiedAt = filemtime($path);
        $hash = hash_file('sha256', $path);

        abort_unless(
            $size !== false
                && $modifiedAt !== false
                && is_string($hash)
                && $hash !== '',
            500,
            'Informasi template ATR gagal dibaca.'
        );

        $version = strtoupper(substr($hash, 0, 12));
        $downloadName = "ATR_IMPORT_PRODUKSI_{$version}.xlsx";

        return response()->streamDownload(
            function () use ($path): void {
                $stream = fopen($path, 'rb');

                if ($stream === false) {
                    throw new \RuntimeException(
                        'Template ATR gagal dibuka untuk proses download.'
                    );
                }

                try {
                    while (! feof($stream)) {
                        $chunk = fread($stream, 1024 * 1024);

                        if ($chunk === false) {
                            throw new \RuntimeException(
                                'Template ATR gagal dibaca saat proses download.'
                            );
                        }

                        echo $chunk;
                    }
                } finally {
                    fclose($stream);
                }
            },
            $downloadName,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Length' => (string) $size,
                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'Last-Modified' => gmdate(
                    'D, d M Y H:i:s',
                    (int) $modifiedAt
                ) . ' GMT',
                'ETag' => '"' . $hash . '"',
                'X-Template-SHA256' => $hash,
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }


    private function selectedPeriod(Request $request): ?string
    {
        $requested = trim($request->string('period')->toString());

        if ($requested !== '') {
            try {
                return Carbon::createFromFormat('Y-m', $requested)
                    ->startOfMonth()
                    ->format('Y-m-d');
            } catch (\Throwable) {
                // Periode URL tidak valid; gunakan periode terbaru.
            }
        }

        $latest = AtrRecord::query()
            ->whereHas(
                'import',
                fn ($query) => $query->where('status', 'COMPLETED')
            )
            ->max('period');

        if ($latest) {
            return Carbon::parse($latest)
                ->startOfMonth()
                ->format('Y-m-d');
        }

        return null;
    }

    private function latestImportIdForPeriod(?string $period): ?int
    {
        if (! $period) {
            return null;
        }

        $id = AtrRecord::query()
            ->whereHas(
                'import',
                fn ($query) => $query->where('status', 'COMPLETED')
            )
            ->whereDate('period', $period)
            ->max('atr_import_id');

        return $id ? (int) $id : null;
    }

    private function periodOptions(?string $selectedPeriod = null)
    {
        $options = AtrRecord::query()
            ->select('period')
            ->whereHas(
                'import',
                fn ($query) => $query->where('status', 'COMPLETED')
            )
            ->whereNotNull('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period')
            ->map(
                fn ($period) => Carbon::parse($period)->startOfMonth()
            );

        /*
         * Saat ATR belum pernah diimpor, dropdown tetap memiliki bulan
         * berjalan agar halaman ringkasan dapat dibuka tanpa error.
         */
        if ($selectedPeriod !== null) {
            $selected = Carbon::parse($selectedPeriod)->startOfMonth();
            $exists = $options->contains(
                fn (Carbon $option) => $option->format('Y-m') === $selected->format('Y-m')
            );

            if (! $exists) {
                $options->prepend($selected);
            }
        }

        return $options->unique(
            fn (Carbon $period) => $period->format('Y-m')
        )->values();
    }

    private function applyFilters($query, Request $request)
    {
        /*
         * Filter final ATR menggunakan POSISI.
         *
         * Parameter legacy job_title tetap diterima sementara agar URL/Blade
         * lama tidak langsung rusak. Jika parameter position tersedia, itu
         * yang dipakai.
         */
        $position = trim($request->string('position')->toString());

        if ($position === '') {
            $position = trim($request->string('job_title')->toString());
        }

        return $query
            ->when(
                $position !== '',
                fn ($q) => $q->where('position', $position)
            )
            ->when(
                $request->filled('search'),
                function ($q) use ($request): void {
                    $search = trim($request->string('search')->toString());

                    $q->where(function ($nested) use ($search): void {
                        $nested
                            ->where('nrp', 'like', '%' . $search . '%')
                            ->orWhere('employee_name', 'like', '%' . $search . '%');
                    });
                }
            );
    }

    private function storeAttachment(
        StoreAtrCoachingRequest $request,
        AtrCoachingCounseling $coaching,
        string $input,
        string $type
    ): void {
        if (! $request->hasFile($input)) {
            return;
        }

        $file = $request->file($input);
        $directory = 'atr/coaching/' . $coaching->id;
        $name = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs($directory, $name, 'local');

        if (! $path) {
            throw new \RuntimeException("Lampiran {$type} gagal disimpan.");
        }

        AtrCoachingAttachment::query()->create([
            'atr_coaching_counseling_id' => $coaching->id,
            'type' => $type,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);
    }

    /**
     * Simpan tanda tangan dari signature pad sebagai PNG.
     *
     * File fisik disimpan pada disk "local":
     * storage/app/private/atr/coaching/{coaching_id}/...
     *
     * Metadata file disimpan ke tabel atr_coaching_attachments.
     */
    private function storeSignatureData(
        AtrCoachingCounseling $coaching,
        string $dataUrl,
        string $type,
        string $filePrefix
    ): void {
        $dataUrl = trim($dataUrl);

        if ($dataUrl === '') {
            throw new \RuntimeException(
                $type === 'EMPLOYEE_SIGNATURE'
                    ? 'Tanda Tangan Karyawan wajib diisi.'
                    : 'Tanda Tangan Pimpinan wajib diisi.'
            );
        }

        if (! preg_match(
            '/^data:image\/png;base64,(.+)$/s',
            $dataUrl,
            $matches
        )) {
            throw new \RuntimeException(
                'Format tanda tangan tidak valid. Silakan tanda tangan ulang.'
            );
        }

        $base64 = preg_replace('/\s+/', '', $matches[1]);
        $binary = base64_decode($base64, true);

        if ($binary === false || strlen($binary) < 100) {
            throw new \RuntimeException(
                'Data tanda tangan kosong atau tidak dapat dibaca.'
            );
        }

        if (strlen($binary) > (2 * 1024 * 1024)) {
            throw new \RuntimeException(
                'Ukuran tanda tangan terlalu besar.'
            );
        }

        $imageInfo = @getimagesizefromstring($binary);

        if (
            $imageInfo === false
            || ($imageInfo['mime'] ?? '') !== 'image/png'
        ) {
            throw new \RuntimeException(
                'Data tanda tangan bukan gambar PNG yang valid.'
            );
        }

        $directory = 'atr/coaching/' . $coaching->id;
        $fileName = $filePrefix . '-' . Str::uuid() . '.png';
        $storedPath = $directory . '/' . $fileName;

        $stored = Storage::disk('local')->put(
            $storedPath,
            $binary
        );

        if (! $stored) {
            throw new \RuntimeException(
                'Tanda tangan gagal disimpan ke storage.'
            );
        }

        AtrCoachingAttachment::query()->create([
            'atr_coaching_counseling_id' => $coaching->id,
            'type' => $type,
            'original_name' => $filePrefix . '.png',
            'stored_path' => $storedPath,
            'mime_type' => 'image/png',
            'size_bytes' => strlen($binary),
        ]);
    }

    /**
     * PIC roster utama berdasarkan POSISI.
     * Mapping sama dengan menu Pengaturan PIC Roster.
     */
    private function picRosterNameForPosition(string $position): string
    {
        return $this->picRosterService->resolveName($position);
    }

    private function buildSystemDocumentNumber(
        AtrCoachingCounseling $coaching
    ): string {
        $reference = $coaching->completed_at
            ?: $coaching->created_at
            ?: now();

        return 'PPA-ATR-CC-'
            . Carbon::parse($reference)->format('Ym')
            . '-'
            . str_pad(
                (string) $coaching->id,
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    private function currentActorName(): string
    {
        $user = auth()->user();

        if (! $user) {
            return 'SYSTEM';
        }

        $name = trim((string) ($user->name ?? ''));

        if ($name !== '') {
            return $name;
        }

        $email = trim((string) ($user->email ?? ''));

        return $email !== ''
            ? $email
            : 'USER #' . $user->getAuthIdentifier();
    }

    private function render(
        string $contentView,
        string $activePage,
        array $data = []
    ): View {
        return view(
            'database',
            array_merge(
                [
                    'contentView' => $contentView,
                    'activePage' => $activePage,
                ],
                $data
            )
        );
    }
}