<?php

namespace App\Http\Controllers;

use App\Models\CoachingCounselling;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CoachingCounsellingController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = $this->validMonth(
            $request->input('bulan', now()->format('Y-m'))
        );

        [$tahun, $nomorBulan] = array_map('intval', explode('-', $bulan));
        $search = trim((string) $request->input('search', ''));

        $query = CoachingCounselling::query()
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $nomorBulan);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('nrp', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%")
                    ->orWhere('materi', 'like', "%{$search}%")
                    ->orWhere('perihal', 'like', "%{$search}%")
                    ->orWhere('dibuat_oleh', 'like', "%{$search}%");
            });
        }

        $records = $query
            ->latest('tanggal')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $statistik = [
            'bulanDipilih' => CoachingCounselling::query()
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $nomorBulan)
                ->count(),
            'total' => CoachingCounselling::query()->count(),
        ];

        return view('manpower', [
            'contentView' => 'manpower.cc-st-sp.coaching-counselling',
            'records' => $records,
            'bulan' => $bulan,
            'search' => $search,
            'statistik' => $statistik,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $file = $request->file('file_dokumen');
        $tanggal = Carbon::parse($validated['tanggal']);

        $path = $file->storeAs(
            'cc-st-sp/coaching-counselling/'
                .$tanggal->format('Y/m'),
            Str::uuid().'.pdf',
            'public'
        );

        CoachingCounselling::create([
            'nrp' => $validated['nrp'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'materi' => $validated['materi'],
            'perihal' => $validated['perihal'] ?? null,
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'keterangan' => $validated['keterangan'] ?? null,
            'dibuat_oleh' => $validated['dibuat_oleh'] ?? auth()->user()?->name,
            'file_path' => $path,
            'file_nama_asli' => $file->getClientOriginalName(),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('cc-st-sp.coaching.index', [
                'bulan' => $tanggal->format('Y-m'),
            ])
            ->with('success', 'Data Coaching & Counselling berhasil disimpan.');
    }

    public function update(
        Request $request,
        CoachingCounselling $coachingCounselling
    ): RedirectResponse {
        $validated = $this->validateRequest($request, false);
        $data = [
            'nrp' => $validated['nrp'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'materi' => $validated['materi'],
            'perihal' => $validated['perihal'] ?? null,
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'keterangan' => $validated['keterangan'] ?? null,
            'dibuat_oleh' => $validated['dibuat_oleh'] ?? null,
        ];

        if ($request->hasFile('file_dokumen')) {
            $this->deleteFile($coachingCounselling->file_path);

            $file = $request->file('file_dokumen');
            $tanggal = Carbon::parse($validated['tanggal']);

            $data['file_path'] = $file->storeAs(
                'cc-st-sp/coaching-counselling/'
                    .$tanggal->format('Y/m'),
                Str::uuid().'.pdf',
                'public'
            );
            $data['file_nama_asli'] = $file->getClientOriginalName();
        }

        $coachingCounselling->update($data);

        return redirect()
            ->route('cc-st-sp.coaching.index', [
                'bulan' => Carbon::parse($validated['tanggal'])->format('Y-m'),
            ])
            ->with('success', 'Data Coaching & Counselling berhasil diperbarui.');
    }

    public function file(
        CoachingCounselling $coachingCounselling
    ): BinaryFileResponse {
        return $this->openPdf(
            $coachingCounselling->file_path,
            $coachingCounselling->file_nama_asli
        );
    }

    public function destroy(
        CoachingCounselling $coachingCounselling
    ): RedirectResponse {
        $bulan = $coachingCounselling->tanggal?->format('Y-m')
            ?: now()->format('Y-m');

        $this->deleteFile($coachingCounselling->file_path);
        $coachingCounselling->delete();

        return redirect()
            ->route('cc-st-sp.coaching.index', ['bulan' => $bulan])
            ->with('success', 'Data Coaching & Counselling berhasil dihapus.');
    }

    private function validateRequest(Request $request, bool $fileRequired = true): array
    {
        return $request->validate([
            'nrp' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:150'],
            'materi' => ['required', 'string', 'max:255'],
            'perihal' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'shift' => ['required', Rule::in(['SHIFT 1', 'SHIFT 2'])],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'dibuat_oleh' => ['nullable', 'string', 'max:150'],
            'file_dokumen' => [
                $fileRequired ? 'required' : 'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'file_dokumen.required' => 'File PDF wajib diunggah.',
            'file_dokumen.mimes' => 'Dokumen harus berformat PDF.',
            'file_dokumen.max' => 'Ukuran PDF maksimal 10 MB.',
        ]);
    }

    private function validMonth(mixed $month): string
    {
        $month = (string) $month;

        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)
            ? $month
            : now()->format('Y-m');
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function openPdf(?string $path, ?string $originalName): BinaryFileResponse
    {
        abort_unless(
            $path && Storage::disk('public')->exists($path),
            404,
            'File PDF tidak ditemukan.'
        );

        $name = str_replace(
            ['"', "\r", "\n"],
            '',
            $originalName ?: 'coaching-counselling.pdf'
        );

        return response()->file(
            Storage::disk('public')->path($path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$name.'"',
            ]
        );
    }
}