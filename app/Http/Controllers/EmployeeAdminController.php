<?php

namespace App\Http\Controllers;

use App\Services\EmployeeMasterService;
use App\Services\GoogleSheetsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class EmployeeAdminController extends Controller
{
    /**
     * Pada struktur akun SYNRGYPRO saat ini, akun Google internal
     * menggunakan role Operator, sedangkan mode baca menggunakan Guest.
     * Admin/Administrator tetap disiapkan untuk pengembangan berikutnya.
     */
    private const WRITE_ROLES = [
        'ADMIN',
        'ADMINISTRATOR',
        'SUPER ADMIN',
        'OPERATOR',
    ];

    private const STATUS_TYPES = [
        'MUTASI',
        'PROMOSI',
        'RESIGN',
        'PHK',
    ];

    public function __construct(
        private readonly EmployeeMasterService $employeeMaster,
        private readonly GoogleSheetsService $googleSheets
    ) {
    }

    /**
     * Menyimpan koreksi atau pelengkapan data karyawan ke
     * sheet UPDATE_DATA_KARYAWAN.
     */
    public function storeDataUpdate(
        Request $request
    ): RedirectResponse {
        $this->ensureCanWrite($request);

        $request->merge([
            'nrp_karyawan' => trim(
                (string) $request->input('nrp_karyawan', '')
            ),
            'nama_lengkap_karyawan' => trim(
                (string) $request->input(
                    'nama_lengkap_karyawan',
                    ''
                )
            ),
            'no_hp_aktif' => trim(
                (string) $request->input('no_hp_aktif', '')
            ),
            'email_aktif' => strtolower(
                trim(
                    (string) $request->input('email_aktif', '')
                )
            ),
            'tanggal_lahir' => trim(
                (string) $request->input('tanggal_lahir', '')
            ),
            'status_tempat_tinggal' => strtoupper(
                trim(
                    (string) $request->input(
                        'status_tempat_tinggal',
                        ''
                    )
                )
            ),
            'nomor_gedung' => trim(
                (string) $request->input('nomor_gedung', '')
            ),
            'nomor_kamar_mess' => trim(
                (string) $request->input(
                    'nomor_kamar_mess',
                    ''
                )
            ),
            'pass_foto' => trim(
                (string) $request->input('pass_foto', '')
            ),
            'alasan_perubahan' => trim(
                (string) $request->input(
                    'alasan_perubahan',
                    ''
                )
            ),
        ]);

        $validated = $request->validate([
            'nrp_karyawan' => [
                'required',
                'string',
                'max:50',
            ],
            'nama_lengkap_karyawan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'no_hp_aktif' => [
                'nullable',
                'string',
                'max:50',
            ],
            'email_aktif' => [
                'nullable',
                'email:rfc',
                'max:255',
            ],
            'tanggal_lahir' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'status_tempat_tinggal' => [
                'nullable',
                Rule::in([
                    'MESS',
                    'NON MESS',
                ]),
            ],
            'nomor_gedung' => [
                'nullable',
                'string',
                'max:100',
            ],
            'nomor_kamar_mess' => [
                'nullable',
                'string',
                'max:100',
            ],
            'pass_foto' => [
                'nullable',
                'string',
                'max:2048',
            ],
            'alasan_perubahan' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'nrp_karyawan.required' =>
                'NRP karyawan wajib diisi.',
            'email_aktif.email' =>
                'Format email aktif tidak valid.',
            'tanggal_lahir.date_format' =>
                'Tanggal lahir harus memakai format tanggal yang benar.',
            'status_tempat_tinggal.in' =>
                'Status tempat tinggal hanya boleh MESS atau NON MESS.',
            'alasan_perubahan.required' =>
                'Alasan perubahan wajib diisi.',
        ]);

        $nrp = (string) $validated['nrp_karyawan'];
        $this->findEmployeeOrFail($nrp);

        $changeFields = [
            $validated['nama_lengkap_karyawan'] ?? '',
            $validated['no_hp_aktif'] ?? '',
            $validated['email_aktif'] ?? '',
            $validated['tanggal_lahir'] ?? '',
            $validated['status_tempat_tinggal'] ?? '',
            $validated['nomor_gedung'] ?? '',
            $validated['nomor_kamar_mess'] ?? '',
            $validated['pass_foto'] ?? '',
        ];

        $hasChange = collect($changeFields)
            ->contains(
                fn (mixed $value): bool =>
                    trim((string) $value) !== ''
            );

        if (!$hasChange) {
            throw ValidationException::withMessages([
                'employee_update' =>
                    'Isi minimal satu data yang akan diperbarui.',
            ]);
        }

        $residence = (string) (
            $validated['status_tempat_tinggal'] ?? ''
        );

        $building = (string) (
            $validated['nomor_gedung'] ?? ''
        );

        $room = (string) (
            $validated['nomor_kamar_mess'] ?? ''
        );

        /*
         * MASTER_DATABASE tidak menimpa nilai lama dengan sel kosong.
         * Karena itu, saat admin memilih NON MESS, gedung dan kamar
         * dikirim sebagai __CLEAR__ agar data mess lama benar-benar dihapus.
         */
        if ($residence === 'NON MESS') {
            $building = '__CLEAR__';
            $room = '__CLEAR__';
        }

        /*
         * Timestamp dibuat oleh Laravel saat tombol Simpan ditekan.
         * Nilainya tetap dan mengikuti timezone aplikasi.
         */
        $timestamp = now(
            (string) config(
                'app.timezone',
                'Asia/Jakarta'
            )
        )->format('Y-m-d H:i:s');

        $row = [
            $timestamp,
            $nrp,
            (string) (
                $validated['nama_lengkap_karyawan'] ?? ''
            ),
            (string) ($validated['no_hp_aktif'] ?? ''),
            (string) ($validated['email_aktif'] ?? ''),
            (string) ($validated['tanggal_lahir'] ?? ''),
            $residence,
            $building,
            $room,
            (string) ($validated['pass_foto'] ?? ''),
            (string) $validated['alasan_perubahan'],
            $this->updaterLabel($request),
        ];

        try {
            $this->googleSheets
                ->appendEmployeeDataUpdate($row);

            return redirect()
                ->route('database.employees')
                ->with(
                    'success',
                    'Perubahan data NRP ' .
                    $nrp .
                    ' berhasil disimpan ke UPDATE_DATA_KARYAWAN. ' .
                    'Perubahan akan tampil setelah MASTER_DATABASE ' .
                    'diproses dan data disinkronkan.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan perubahan data karyawan: ' .
                    $exception->getMessage()
                );
        }
    }

    /**
     * Menyimpan MUTASI, PROMOSI, RESIGN, atau PHK ke
     * sheet UPDATE_STATUS_KARYAWAN.
     */
    public function storeStatusUpdate(
        Request $request
    ): RedirectResponse {
        $this->ensureCanWrite($request);

        $request->merge([
            'nrp_karyawan' => trim(
                (string) $request->input('nrp_karyawan', '')
            ),
            'jenis_perubahan' => strtoupper(
                trim(
                    (string) $request->input(
                        'jenis_perubahan',
                        ''
                    )
                )
            ),
            'tanggal_efektif' => trim(
                (string) $request->input('tanggal_efektif', '')
            ),
            'jabatan_baru' => trim(
                (string) $request->input('jabatan_baru', '')
            ),
            'site_baru' => trim(
                (string) $request->input('site_baru', '')
            ),
            'alasan_keterangan' => trim(
                (string) $request->input(
                    'alasan_keterangan',
                    ''
                )
            ),
        ]);

        $validated = $request->validate([
            'nrp_karyawan' => [
                'required',
                'string',
                'max:50',
            ],
            'jenis_perubahan' => [
                'required',
                Rule::in(self::STATUS_TYPES),
            ],
            'tanggal_efektif' => [
                'required',
                'date_format:Y-m-d',
            ],
            'jabatan_baru' => [
                'required_if:jenis_perubahan,MUTASI,PROMOSI',
                'nullable',
                'string',
                'max:255',
            ],
            'site_baru' => [
                'required_if:jenis_perubahan,MUTASI',
                'nullable',
                'string',
                'max:255',
            ],
            'alasan_keterangan' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'nrp_karyawan.required' =>
                'NRP karyawan wajib diisi.',
            'jenis_perubahan.required' =>
                'Jenis perubahan wajib dipilih.',
            'jenis_perubahan.in' =>
                'Jenis perubahan hanya boleh MUTASI, PROMOSI, RESIGN, atau PHK.',
            'tanggal_efektif.required' =>
                'Tanggal efektif wajib diisi.',
            'tanggal_efektif.date_format' =>
                'Tanggal efektif harus memakai format tanggal yang benar.',
            'jabatan_baru.required_if' =>
                'Jabatan baru wajib diisi untuk MUTASI atau PROMOSI.',
            'site_baru.required_if' =>
                'Site baru wajib diisi untuk MUTASI.',
            'alasan_keterangan.required' =>
                'Alasan atau keterangan wajib diisi.',
        ]);

        $nrp = (string) $validated['nrp_karyawan'];
        $employee = $this->findEmployeeOrFail($nrp);
        $changeType = (string) $validated['jenis_perubahan'];

        $newStatus = match ($changeType) {
            'MUTASI', 'PROMOSI' => 'AKTIF',
            'RESIGN' => 'RESIGN',
            'PHK' => 'PHK',
        };

        $newPosition = in_array(
            $changeType,
            ['MUTASI', 'PROMOSI'],
            true
        )
            ? (string) ($validated['jabatan_baru'] ?? '')
            : '';

        $newSite = $changeType === 'MUTASI'
            ? (string) ($validated['site_baru'] ?? '')
            : '';

        /*
         * Timestamp dibuat oleh Laravel saat tombol Simpan ditekan.
         * Nilainya tetap dan mengikuti timezone aplikasi.
         */
        $timestamp = now(
            (string) config(
                'app.timezone',
                'Asia/Jakarta'
            )
        )->format('Y-m-d H:i:s');

        $row = [
            $timestamp,
            $nrp,
            trim((string) ($employee['nama'] ?? '')),
            $changeType,
            (string) $validated['tanggal_efektif'],
            $newStatus,
            $newPosition,
            $newSite,
            (string) $validated['alasan_keterangan'],
            $this->updaterLabel($request),
        ];

        try {
            $this->googleSheets
                ->appendEmployeeStatusUpdate($row);

            return redirect()
                ->route('database.employees')
                ->with(
                    'success',
                    $changeType .
                    ' untuk NRP ' .
                    $nrp .
                    ' berhasil disimpan ke UPDATE_STATUS_KARYAWAN. ' .
                    'Perubahan akan tampil setelah MASTER_DATABASE ' .
                    'diproses dan data disinkronkan.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan perubahan status karyawan: ' .
                    $exception->getMessage()
                );
        }
    }

    /**
     * Mengizinkan penulisan hanya untuk akun internal yang berperan
     * sebagai admin/operator. Guest tetap hanya dapat membaca.
     */
    private function ensureCanWrite(Request $request): void
    {
        $user = $request->user();
        $role = strtoupper(
            trim((string) ($user?->role ?? ''))
        );

        abort_unless(
            $user !== null &&
            in_array($role, self::WRITE_ROLES, true),
            403,
            'Hanya admin Database Karyawan yang dapat melakukan perubahan.'
        );
    }

    /**
     * Memastikan NRP sudah ada di MASTER_DATABASE agar typo NRP tidak
     * membuat data karyawan baru atau duplikat secara tidak sengaja.
     */
    private function findEmployeeOrFail(string $nrp): array
    {
        $snapshot = $this->employeeMaster->snapshot();

        $employee = collect(
            $snapshot['employees'] ?? []
        )->first(
            fn (array $item): bool =>
                trim((string) ($item['nrp'] ?? '')) === $nrp
        );

        if (!is_array($employee)) {
            throw ValidationException::withMessages([
                'nrp_karyawan' =>
                    'NRP ' .
                    $nrp .
                    ' tidak ditemukan di MASTER_DATABASE.',
            ]);
        }

        return $employee;
    }

    private function updaterLabel(Request $request): string
    {
        $user = $request->user();
        $name = trim((string) ($user?->name ?? ''));
        $email = strtolower(
            trim((string) ($user?->email ?? ''))
        );

        if ($name !== '' && $email !== '') {
            return $name . ' <' . $email . '>';
        }

        return $name !== ''
            ? $name
            : $email;
    }
}