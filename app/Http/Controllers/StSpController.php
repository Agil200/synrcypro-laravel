<?php

namespace App\Http\Controllers;

use App\Models\StSpRecord;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StSpController extends Controller
{
    public function teguran(Request $request): View
    {
        return $this->indexByCategory($request, 'teguran');
    }

    public function peringatan(Request $request): View
    {
        return $this->indexByCategory($request, 'peringatan');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $kategori = $this->categoryFromJenis($validated['jenis']);
        $file = $request->file('file_dokumen');
        $tanggal = Carbon::parse($validated['tanggal']);

        $path = $file->storeAs(
            'cc-st-sp/'.$kategori.'/'.$tanggal->format('Y/m'),
            Str::uuid().'.pdf',
            'public'
        );

        StSpRecord::create([
            'nrp' => $validated['nrp'],
            'nama' => $validated['nama'],
            'jenis_pelanggaran' => $validated['jenis_pelanggaran'],
            'tanggal' => $validated['tanggal'],
            'expired_date' => $tanggal
                ->copy()
                ->addDays(180)
                ->toDateString(),
            'tempat_kejadian' =>
                $validated['tempat_kejadian'] ?? null,
            'jenis' => $validated['jenis'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'atasan' => $validated['atasan'] ?? null,
            'file_path' => $path,
            'file_nama_asli' => $file->getClientOriginalName(),
            'status' => 'AKTIF',
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route($this->indexRoute($kategori), [
                'bulan' => $tanggal->format('Y-m'),
            ])
            ->with(
                'success',
                $this->title($kategori).' berhasil disimpan.'
            );
    }

    public function update(
        Request $request,
        StSpRecord $stSpRecord
    ): RedirectResponse {
        $validated = $this->validateRequest($request, false);
        $kategori = $this->categoryFromJenis($validated['jenis']);
        $tanggal = Carbon::parse($validated['tanggal']);

        $data = [
            'nrp' => $validated['nrp'],
            'nama' => $validated['nama'],
            'jenis_pelanggaran' => $validated['jenis_pelanggaran'],
            'tanggal' => $validated['tanggal'],
            'expired_date' => $tanggal
                ->copy()
                ->addDays(180)
                ->toDateString(),
            'tempat_kejadian' =>
                $validated['tempat_kejadian'] ?? null,
            'jenis' => $validated['jenis'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'atasan' => $validated['atasan'] ?? null,
            'status' => $tanggal
                ->copy()
                ->addDays(180)
                ->isBefore(today())
                    ? 'EXPIRED'
                    : 'AKTIF',
        ];

        if ($request->hasFile('file_dokumen')) {
            $this->deleteFile($stSpRecord->file_path);
            $file = $request->file('file_dokumen');

            $data['file_path'] = $file->storeAs(
                'cc-st-sp/'.$kategori.'/'.$tanggal->format('Y/m'),
                Str::uuid().'.pdf',
                'public'
            );

            $data['file_nama_asli'] =
                $file->getClientOriginalName();
        }

        $stSpRecord->update($data);

        return redirect()
            ->route($this->indexRoute($kategori), [
                'bulan' => $tanggal->format('Y-m'),
            ])
            ->with(
                'success',
                $this->title($kategori).' berhasil diperbarui.'
            );
    }

    public function file(
        StSpRecord $stSpRecord
    ): BinaryFileResponse {
        abort_unless(
            $stSpRecord->file_path
                && Storage::disk('public')->exists(
                    $stSpRecord->file_path
                ),
            404,
            'File PDF tidak ditemukan.'
        );

        $name = str_replace(
            ['"', "\r", "\n"],
            '',
            $stSpRecord->file_nama_asli ?: 'st-sp.pdf'
        );

        return response()->file(
            Storage::disk('public')->path(
                $stSpRecord->file_path
            ),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>
                    'inline; filename="'.$name.'"',
            ]
        );
    }

    public function destroy(
        StSpRecord $stSpRecord
    ): RedirectResponse {
        $kategori = $this->categoryFromJenis(
            $stSpRecord->jenis
        );

        $bulan = $stSpRecord->tanggal?->format('Y-m')
            ?: now()->format('Y-m');

        $this->deleteFile($stSpRecord->file_path);
        $stSpRecord->delete();

        return redirect()
            ->route(
                $this->indexRoute($kategori),
                ['bulan' => $bulan]
            )
            ->with(
                'success',
                $this->title($kategori).' berhasil dihapus.'
            );
    }

    private function indexByCategory(
        Request $request,
        string $kategori
    ): View {
        $bulan = $this->validMonth(
            $request->input(
                'bulan',
                now()->format('Y-m')
            )
        );

        [$tahun, $nomorBulan] = array_map(
            'intval',
            explode('-', $bulan)
        );

        $search = trim(
            (string) $request->input('search', '')
        );

        $jenisList = $this->jenisList($kategori);

        StSpRecord::query()
            ->whereDate(
                'expired_date',
                '<',
                now()->toDateString()
            )
            ->where('status', '!=', 'EXPIRED')
            ->update(['status' => 'EXPIRED']);

        $query = StSpRecord::query()
            ->whereIn('jenis', $jenisList)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $nomorBulan);

        if ($search !== '') {
            $query->where(
                function ($subQuery) use ($search) {
                    $subQuery
                        ->where(
                            'nrp',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'nama',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'jenis_pelanggaran',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'tempat_kejadian',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'atasan',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        $records = $query
            ->latest('tanggal')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $statistik = [
            'bulanDipilih' => StSpRecord::query()
                ->whereIn('jenis', $jenisList)
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $nomorBulan)
                ->count(),

            'aktif' => StSpRecord::query()
                ->whereIn('jenis', $jenisList)
                ->where('status', 'AKTIF')
                ->count(),
        ];

        return view('manpower', [
            'contentView' => 'manpower.cc-st-sp.st-sp',
            'records' => $records,
            'bulan' => $bulan,
            'search' => $search,
            'statistik' => $statistik,
            'kategori' => $kategori,
            'pageTitle' => $this->title($kategori),
            'jenisList' => $jenisList,
            'allJenisList' => $this->allJenisList(),
            'nextNumber' =>
                ((int) StSpRecord::query()->max('id')) + 1,
        ]);
    }

    private function validateRequest(
        Request $request,
        bool $fileRequired = true
    ): array {
        return $request->validate(
            [
                'nrp' => ['required', 'string', 'max:50'],

                'nama' => ['required', 'string', 'max:150'],

                'jenis_pelanggaran' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'tanggal' => ['required', 'date'],

                'tempat_kejadian' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'jenis' => [
                    'required',
                    Rule::in($this->allJenisList()),
                ],

                'deskripsi' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'atasan' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'file_dokumen' => [
                    $fileRequired ? 'required' : 'nullable',
                    'file',
                    'mimes:pdf',
                    'max:10240',
                ],
            ],
            [
                'file_dokumen.required' =>
                    'File PDF wajib diunggah.',

                'file_dokumen.mimes' =>
                    'Dokumen harus berformat PDF.',

                'file_dokumen.max' =>
                    'Ukuran PDF maksimal 10 MB.',
            ]
        );
    }

    private function allJenisList(): array
    {
        return [
            'TEGURAN',
            'PERINGATAN KEDUA',
            'PERINGATAN PERTAMA',
            'PERINGATAN KETIGA',
        ];
    }

    private function jenisList(string $kategori): array
    {
        return $kategori === 'teguran'
            ? ['TEGURAN']
            : [
                'PERINGATAN PERTAMA',
                'PERINGATAN KEDUA',
                'PERINGATAN KETIGA',
            ];
    }

    private function categoryFromJenis(
        ?string $jenis
    ): string {
        return $jenis === 'TEGURAN'
            ? 'teguran'
            : 'peringatan';
    }

    private function validMonth(mixed $month): string
    {
        $month = (string) $month;

        return preg_match(
            '/^\d{4}-(0[1-9]|1[0-2])$/',
            $month
        )
            ? $month
            : now()->format('Y-m');
    }

    private function indexRoute(string $kategori): string
    {
        return $kategori === 'teguran'
            ? 'cc-st-sp.teguran.index'
            : 'cc-st-sp.peringatan.index';
    }

    private function title(string $kategori): string
    {
        return $kategori === 'teguran'
            ? 'Surat Teguran'
            : 'Surat Peringatan';
    }

    private function deleteFile(?string $path): void
    {
        if (
            $path
            && Storage::disk('public')->exists($path)
        ) {
            Storage::disk('public')->delete($path);
        }
    }
}