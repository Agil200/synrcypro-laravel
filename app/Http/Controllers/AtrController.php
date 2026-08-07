<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtrCommitRequest;
use App\Http\Requests\AtrPreviewRequest;
use App\Http\Requests\StoreAtrCoachingRequest;
use App\Models\AtrCoachingAttachment;
use App\Models\AtrCoachingCounseling;
use App\Models\AtrImport;
use App\Models\AtrRecord;
use App\Services\AtrImportService;
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
        private readonly AtrImportService $importService
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
            ->when(
                $latestImportId !== null,
                fn ($query) => $query->where('atr_import_id', $latestImportId)
            )
            ->whereDate('period', $period);

        $jobOptions = (clone $base)
            ->whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->distinct()
            ->orderBy('job_title')
            ->pluck('job_title');

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
                'jobOptions' => $jobOptions,
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

    public function commit(AtrCommitRequest $request): RedirectResponse
    {
        $token = (string) $request->validated('preview_token');
        $preview = $request->session()->get('atr_preview.' . $token);

        if (! is_array($preview)) {
            return back()->withErrors([
                'atr_file' => 'Preview tidak ditemukan atau sesi sudah kedaluwarsa.',
            ]);
        }

        try {
            $import = $this->importService->commit(
                $preview,
                auth()->id()
            );

            $request->session()->forget('atr_preview.' . $token);
            $request->session()->forget('atr_last_preview_token');

            return redirect()
                ->route('database.atr.history')
                ->with(
                    'success',
                    "Import berhasil. {$import->imported_rows} baris ATR tersimpan."
                );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'atr_file' => 'Import gagal: ' . $exception->getMessage(),
            ]);
        }
    }

    public function history(): View
    {
        $imports = AtrImport::query()
            ->with('uploader:id,name,email')
            ->latest('id')
            ->paginate(20);

        return $this->render('database.atr.import-history', 'atr-history', [
            'imports' => $imports,
        ]);
    }

    public function calls(Request $request): View
    {
        $period = $this->selectedPeriod($request);
        $latestImportId = $this->latestImportIdForPeriod($period);

        $base = AtrRecord::query()
            ->with('latestCoaching.attachments')
            ->when($latestImportId, fn ($query) => $query->where('atr_import_id', $latestImportId))
            ->when($period, fn ($query) => $query->whereDate('period', $period))
            ->where('status', 'PEMANGGILAN');

        $jobOptions = (clone $base)
            ->whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->distinct()
            ->orderBy('job_title')
            ->pluck('job_title');

        $records = $this->applyFilters($base, $request)
            ->when(
                $request->filled('call_status'),
                function ($query) use ($request): void {
                    if ($request->string('call_status')->toString() === 'sudah') {
                        $query->whereHas('coachingCounselings', fn ($q) => $q->where('status', 'COMPLETED'));
                    }

                    if ($request->string('call_status')->toString() === 'belum') {
                        $query->whereDoesntHave('coachingCounselings', fn ($q) => $q->where('status', 'COMPLETED'));
                    }
                }
            )
            ->orderBy('atr')
            ->paginate(18)
            ->withQueryString();

        $allCallIds = AtrRecord::query()
            ->when($latestImportId, fn ($query) => $query->where('atr_import_id', $latestImportId))
            ->when($period, fn ($query) => $query->whereDate('period', $period))
            ->where('status', 'PEMANGGILAN')
            ->pluck('id');

        $completed = AtrCoachingCounseling::query()
            ->whereIn('atr_record_id', $allCallIds)
            ->where('status', 'COMPLETED')
            ->distinct('atr_record_id')
            ->count('atr_record_id');

        return $this->render(
            'database.atr.call-documentation',
            'atr-calls',
            [
                'records' => $records,
                'period' => $period,
                'periodOptions' => $this->periodOptions(),
                'jobOptions' => $jobOptions,
                'callStats' => [
                    'total' => $allCallIds->count(),
                    'sudah' => $completed,
                    'belum' => max(0, $allCallIds->count() - $completed),
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
                'atr_record_id' => 'Dokumentasi hanya dapat dibuat untuk status PEMANGGILAN.',
            ]);
        }

        try {
            DB::transaction(function () use ($request, $record): void {
                $coaching = AtrCoachingCounseling::query()->create([
                    'atr_record_id' => $record->id,
                    'document_number' => (string) config(
                        'atr.document_number',
                        'PPA-PTBA-F-SHE-14D'
                    ),
                    'coaching_date' => $request->date('coaching_date'),
                    'shift' => $request->string('shift')->toString(),
                    'location' => $request->string('location')->toString(),
                    'coaching_time' => $request->string('coaching_time')->toString(),
                    'material_personal' => $request->boolean('material_personal'),
                    'material_family' => $request->boolean('material_family'),
                    'material_work' => $request->boolean('material_work'),
                    'notes' => $request->string('notes')->toString(),
                    'created_by_name' => $request->string('created_by_name')->toString(),
                    'created_by_user_id' => auth()->id(),
                    'status' => 'COMPLETED',
                    'completed_at' => now(),
                ]);

                $this->storeAttachment($request, $coaching, 'evidence', 'EVIDENCE');
                $this->storeAttachment(
                    $request,
                    $coaching,
                    'employee_signature',
                    'EMPLOYEE_SIGNATURE'
                );
                $this->storeAttachment(
                    $request,
                    $coaching,
                    'coach_signature',
                    'COACH_SIGNATURE'
                );
            });

            return back()->with(
                'success',
                'Dokumentasi Coaching & Counseling berhasil disimpan.'
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'coaching' => 'Dokumentasi gagal disimpan: ' . $exception->getMessage(),
            ]);
        }
    }

    public function printCoaching(
        AtrCoachingCounseling $coaching
    ): View {
        $coaching->load(['atrRecord', 'attachments']);

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

        $latest = AtrRecord::query()->max('period');

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
            ->whereDate('period', $period)
            ->max('atr_import_id');

        return $id ? (int) $id : null;
    }

    private function periodOptions(?string $selectedPeriod = null)
    {
        $options = AtrRecord::query()
            ->select('period')
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
        return $query
            ->when(
                $request->filled('job_title'),
                fn ($q) => $q->where(
                    'job_title',
                    $request->string('job_title')->toString()
                )
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