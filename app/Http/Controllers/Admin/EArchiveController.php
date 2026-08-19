<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EArchiveLink;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EArchiveController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->query(
                'q',
                ''
            )
        );

        $category = strtoupper(
            trim(
                (string) $request->query(
                    'category',
                    ''
                )
            )
        );

        $status = strtolower(
            trim(
                (string) $request->query(
                    'status',
                    'all'
                )
            )
        );

        if (
            !in_array(
                $status,
                ['all', 'active', 'inactive'],
                true
            )
        ) {
            $status = 'all';
        }

        $requestedPerPage = (int) $request->query(
            'per_page',
            20
        );

        $perPage = in_array(
            $requestedPerPage,
            [20, 50, 100],
            true
        )
            ? $requestedPerPage
            : 20;

        $query = EArchiveLink::query();

        if ($search !== '') {
            $query->where(
                function ($builder) use ($search): void {
                    $builder
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'category',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if ($category !== '') {
            $query->where(
                'category',
                $category
            );
        }

        if ($status === 'active') {
            $query->where(
                'is_active',
                true
            );
        } elseif ($status === 'inactive') {
            $query->where(
                'is_active',
                false
            );
        }

        $archives = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $categories =
            EArchiveLink::query()
                ->select('category')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');

        $stats = [
            'total' =>
                EArchiveLink::query()->count(),

            'active' =>
                EArchiveLink::query()
                    ->where('is_active', true)
                    ->count(),

            'inactive' =>
                EArchiveLink::query()
                    ->where('is_active', false)
                    ->count(),

            'categories' =>
                EArchiveLink::query()
                    ->whereNotNull('category')
                    ->where('category', '!=', '')
                    ->distinct('category')
                    ->count('category'),
        ];

        return view(
            'admin-all.e-arsip.index',
            compact(
                'archives',
                'categories',
                'stats',
                'search',
                'category',
                'status',
                'perPage'
            )
        );
    }

    public function create(): View
    {
        return view(
            'admin-all.e-arsip.create',
            [
                'archive' =>
                    new EArchiveLink([
                        'category' => 'LAINNYA',
                        'sort_order' => 10,
                        'is_active' => true,
                    ]),

                'categories' =>
                    $this->categoryOptions(),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validatedData(
                $request
            );

        $actor =
            $this->actor($request);

        $validated['created_by'] =
            $actor;

        $validated['updated_by'] =
            $actor;

        EArchiveLink::query()->create(
            $validated
        );

        return redirect()
            ->route(
                'admin-all.e-arsip.index'
            )
            ->with(
                'success',
                'Link E-Arsip berhasil ditambahkan. Folder Google Drive tetap dikelola di Google Drive.'
            );
    }

    public function edit(
        EArchiveLink $eArchiveLink
    ): View {
        return view(
            'admin-all.e-arsip.edit',
            [
                'archive' => $eArchiveLink,
                'categories' =>
                    $this->categoryOptions(),
            ]
        );
    }

    public function update(
        Request $request,
        EArchiveLink $eArchiveLink
    ): RedirectResponse {
        $validated =
            $this->validatedData(
                $request,
                $eArchiveLink
            );

        $validated['updated_by'] =
            $this->actor($request);

        $eArchiveLink->update(
            $validated
        );

        return redirect()
            ->route(
                'admin-all.e-arsip.index'
            )
            ->with(
                'success',
                'Data E-Arsip berhasil diperbarui.'
            );
    }

    public function toggle(
        Request $request,
        EArchiveLink $eArchiveLink
    ): RedirectResponse {
        $nextState =
            !$eArchiveLink->is_active;

        $eArchiveLink->update([
            'is_active' => $nextState,
            'updated_by' =>
                $this->actor($request),
        ]);

        return back()->with(
            'success',
            $nextState
                ? 'Arsip diaktifkan kembali.'
                : 'Arsip dinonaktifkan. Link tidak dihapus dan Google Drive tetap aman.'
        );
    }

    public function destroy(
        Request $request,
        EArchiveLink $eArchiveLink
    ): RedirectResponse {
        /*
         * Soft delete hanya menghapus registry dari SYNRGYPRO.
         * Tidak ada panggilan Google Drive delete.
         */
        $eArchiveLink->update([
            'updated_by' =>
                $this->actor($request),
        ]);

        $name =
            $eArchiveLink->name;

        $eArchiveLink->delete();

        return redirect()
            ->route(
                'admin-all.e-arsip.index'
            )
            ->with(
                'success',
                "{$name} dihapus dari registry E-Arsip. Folder/file Google Drive TIDAK dihapus."
            );
    }

    private function validatedData(
        Request $request,
        ?EArchiveLink $archive = null
    ): array {
        $uniqueUrl =
            Rule::unique(
                'e_archive_links',
                'drive_url'
            )
                ->whereNull('deleted_at');

        if ($archive !== null) {
            $uniqueUrl =
                $uniqueUrl->ignore(
                    $archive->getKey()
                );
        }

        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'category' => [
                    'required',
                    'string',
                    'max:80',
                ],

                'drive_url' => [
                    'required',
                    'url',
                    'max:2048',
                    $uniqueUrl,

                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ): void {
                        if (
                            !$this->isAllowedDriveUrl(
                                (string) $value
                            )
                        ) {
                            $fail(
                                'Link harus menggunakan domain Google Drive / Google Docs.'
                            );
                        }
                    },
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'sort_order' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:9999',
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        $validated['name'] =
            trim($validated['name']);

        $validated['category'] =
            strtoupper(
                trim(
                    $validated['category']
                )
            );

        $validated['drive_url'] =
            trim(
                $validated['drive_url']
            );

        $validated['description'] =
            isset($validated['description'])
                ? trim(
                    (string) $validated[
                        'description'
                    ]
                )
                : null;

        $validated['is_active'] =
            $request->boolean(
                'is_active'
            );

        return $validated;
    }

    private function isAllowedDriveUrl(
        string $url
    ): bool {
        $host = strtolower(
            (string) parse_url(
                $url,
                PHP_URL_HOST
            )
        );

        return in_array(
            $host,
            [
                'drive.google.com',
                'docs.google.com',
            ],
            true
        );
    }

    private function categoryOptions()
    {
        return EArchiveLink::query()
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->merge([
                'PROSEDUR',
                'FORM ADMIN',
                'MCU & FU',
                'IFUTS',
                'APD',
                'ATR',
                'MANPOWER',
                'LAINNYA',
            ])
            ->unique()
            ->sort()
            ->values();
    }

    private function actor(
        Request $request
    ): ?string {
        $user =
            $request->user();

        if ($user === null) {
            return null;
        }

        $email = trim(
            (string) ($user->email ?? '')
        );

        if ($email !== '') {
            return $email;
        }

        $name = trim(
            (string) ($user->name ?? '')
        );

        return $name !== ''
            ? $name
            : null;
    }
}
