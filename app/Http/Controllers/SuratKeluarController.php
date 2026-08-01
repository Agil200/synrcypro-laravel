<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SuratKeluarController extends Controller
{
    /**
     * Daftar tujuan sesuai arahan operasional.
     */
    private const TUJUAN_SURAT = [
        'CSA PIT 1 (SHE)',
        'CSA PIT 1 (ICT)',
        'CSA PIT 1 (MEDIC)',
        'OFFICE PLANT',
        'MESS PPA RESIDENCE',
    ];

    /**
     * Jenis dokumen keluar sesuai daftar dari WhatsApp.
     */
    private const JENIS_DOKUMEN = [
        'BERITA ACARA',
        'FORM KOPERASI',
        'KWITANSI / CLAIM',
        'BERITA ACARA ADVANCE',
        'DEKLARASI',
        'PERJALANAN DINAS',
        'HM UNIT',
        'FORM LAINNYA',
    ];

    /**
     * Menampilkan monitoring surat keluar berdasarkan bulan.
     */
    public function index(Request $request): View
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $bulan)) {
            $bulan = now()->format('Y-m');
        }

        [$tahun, $nomorBulan] = array_map(
            'intval',
            explode('-', $bulan)
        );

        $suratKeluar = SuratKeluar::query()
            ->whereYear('tanggal_surat', $tahun)
            ->whereMonth('tanggal_surat', $nomorBulan)
            ->latest('tanggal_surat')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $statistik = [
            'bulanDipilih' => SuratKeluar::query()
                ->whereYear('tanggal_surat', $tahun)
                ->whereMonth('tanggal_surat', $nomorBulan)
                ->count(),

            'total' => SuratKeluar::query()->count(),
        ];

        return view('manpower', [
            'contentView' => 'manpower.document-out.monitoring',
            'suratKeluar' => $suratKeluar,
            'bulan' => $bulan,
            'statistik' => $statistik,
            'daftarTujuan' => self::TUJUAN_SURAT,
            'daftarJenisDokumen' => self::JENIS_DOKUMEN,
        ]);
    }

    /**
     * Menyimpan surat keluar baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'tanggal_surat' => ['required', 'date'],
                // Nomor surat tidak wajib karena beberapa dokumen tidak bernomor.
                'nomor_surat' => [
                    'nullable',
                    'string',
                    'max:150',
                    'unique:surat_keluar,nomor_surat',
                ],
                'tujuan_surat' => [
                    'required',
                    'string',
                    Rule::in(self::TUJUAN_SURAT),
                ],
                'nama' => ['required', 'string', 'max:150'],
                'nrp' => ['nullable', 'string', 'max:50'],
                'jenis_surat' => [
                    'required',
                    'string',
                    Rule::in(self::JENIS_DOKUMEN),
                ],
                'file_surat' => [
                    'required',
                    'file',
                    'mimes:pdf',
                    'max:10240',
                ],
            ],
            [
                'tujuan_surat.in' => 'Tujuan surat yang dipilih tidak tersedia.',
                'jenis_surat.in' => 'Jenis dokumen yang dipilih tidak tersedia.',
                'file_surat.required' => 'File dokumen PDF wajib diunggah.',
                'file_surat.mimes' => 'File dokumen harus berformat PDF.',
                'file_surat.max' => 'Ukuran file PDF maksimal 10 MB.',
                'nomor_surat.unique' => 'Nomor surat sudah digunakan.',
            ]
        );

        $file = $request->file('file_surat');
        $tanggal = Carbon::parse($validated['tanggal_surat']);

        $folder = sprintf(
            'surat-keluar/%s/%s',
            $tanggal->format('Y'),
            $tanggal->format('m')
        );

        $filePath = $file->storeAs(
            $folder,
            Str::uuid().'.pdf',
            'public'
        );

        SuratKeluar::create([
            'tanggal_surat' => $validated['tanggal_surat'],
            'nomor_surat' => $this->normalisasiNomorSurat(
                $validated['nomor_surat'] ?? null
            ),
            'tujuan_surat' => $validated['tujuan_surat'],
            'nama' => $validated['nama'],
            'nrp' => $validated['nrp'] ?? null,
            'jenis_surat' => $validated['jenis_surat'],
            'file_path' => $filePath,
            'file_nama_asli' => $file->getClientOriginalName(),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('document-out.index', [
                'bulan' => $tanggal->format('Y-m'),
            ])
            ->with('success', 'Dokumen keluar berhasil disimpan.');
    }

    /**
     * Memperbarui surat keluar.
     */
    public function update(
        Request $request,
        SuratKeluar $suratKeluar
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'tanggal_surat' => ['required', 'date'],
                'nomor_surat' => [
                    'nullable',
                    'string',
                    'max:150',
                    Rule::unique(
                        'surat_keluar',
                        'nomor_surat'
                    )->ignore($suratKeluar->id),
                ],
                'tujuan_surat' => [
                    'required',
                    'string',
                    Rule::in(self::TUJUAN_SURAT),
                ],
                'nama' => ['required', 'string', 'max:150'],
                'nrp' => ['nullable', 'string', 'max:50'],
                'jenis_surat' => [
                    'required',
                    'string',
                    Rule::in(self::JENIS_DOKUMEN),
                ],
                'file_surat' => [
                    'nullable',
                    'file',
                    'mimes:pdf',
                    'max:10240',
                ],
            ],
            [
                'tujuan_surat.in' => 'Tujuan surat yang dipilih tidak tersedia.',
                'jenis_surat.in' => 'Jenis dokumen yang dipilih tidak tersedia.',
                'file_surat.mimes' => 'File dokumen harus berformat PDF.',
                'file_surat.max' => 'Ukuran file PDF maksimal 10 MB.',
                'nomor_surat.unique' => 'Nomor surat sudah digunakan.',
            ]
        );

        $data = [
            'tanggal_surat' => $validated['tanggal_surat'],
            'nomor_surat' => $this->normalisasiNomorSurat(
                $validated['nomor_surat'] ?? null
            ),
            'tujuan_surat' => $validated['tujuan_surat'],
            'nama' => $validated['nama'],
            'nrp' => $validated['nrp'] ?? null,
            'jenis_surat' => $validated['jenis_surat'],
        ];

        if ($request->hasFile('file_surat')) {
            if (
                $suratKeluar->file_path &&
                Storage::disk('public')->exists(
                    $suratKeluar->file_path
                )
            ) {
                Storage::disk('public')->delete(
                    $suratKeluar->file_path
                );
            }

            $file = $request->file('file_surat');
            $tanggal = Carbon::parse($validated['tanggal_surat']);

            $folder = sprintf(
                'surat-keluar/%s/%s',
                $tanggal->format('Y'),
                $tanggal->format('m')
            );

            $data['file_path'] = $file->storeAs(
                $folder,
                Str::uuid().'.pdf',
                'public'
            );

            $data['file_nama_asli'] =
                $file->getClientOriginalName();
        }

        $suratKeluar->update($data);

        return redirect()
            ->route('document-out.index', [
                'bulan' => Carbon::parse(
                    $validated['tanggal_surat']
                )->format('Y-m'),
            ])
            ->with('success', 'Dokumen keluar berhasil diperbarui.');
    }

    /**
     * Membuka file PDF di browser.
     */
    public function file(
        SuratKeluar $suratKeluar
    ): BinaryFileResponse {
        abort_unless(
            $suratKeluar->file_path &&
            Storage::disk('public')->exists(
                $suratKeluar->file_path
            ),
            404,
            'File PDF tidak ditemukan.'
        );

        $namaFile = str_replace(
            ['"', "\r", "\n"],
            '',
            $suratKeluar->file_nama_asli
                ?: 'dokumen-keluar.pdf'
        );

        return response()->file(
            Storage::disk('public')->path(
                $suratKeluar->file_path
            ),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>
                    'inline; filename="'.$namaFile.'"',
            ]
        );
    }

    /**
     * Menghapus data surat dan file PDF.
     */
    public function destroy(
        SuratKeluar $suratKeluar
    ): RedirectResponse {
        $bulan = $suratKeluar->tanggal_surat
            ? $suratKeluar->tanggal_surat->format('Y-m')
            : now()->format('Y-m');

        if (
            $suratKeluar->file_path &&
            Storage::disk('public')->exists(
                $suratKeluar->file_path
            )
        ) {
            Storage::disk('public')->delete(
                $suratKeluar->file_path
            );
        }

        $suratKeluar->delete();

        return redirect()
            ->route('document-out.index', ['bulan' => $bulan])
            ->with('success', 'Dokumen keluar berhasil dihapus.');
    }

    /**
     * Mengubah string kosong menjadi null.
     */
    private function normalisasiNomorSurat(?string $nomorSurat): ?string
    {
        $nomorSurat = trim((string) $nomorSurat);

        return $nomorSurat !== '' ? $nomorSurat : null;
    }
}