@extends('admin-all.layout')

@section('admin-page-title', 'User Management')

@section('admin-content')
<div class="admin-page-heading">
    <div>
        <h1>User Management</h1>
        <p>
            Pengguna yang didaftarkan di sini dapat login menggunakan email Google.
            Akun nonaktif akan ditolak oleh sistem pada request berikutnya.
        </p>
    </div>

    <a href="{{ route('admin-all') }}" class="admin-btn secondary">← Dashboard Admin</a>
</div>

@can('users.create')
<section class="admin-card" style="margin-bottom:16px">
    <div class="admin-card-header">
        <div>
            <h2>Tambah Pengguna</h2>
            <small>Gunakan alamat email Google yang benar dan aktif</small>
        </div>
    </div>

    <form method="POST" action="{{ route('admin-all.users.store') }}" class="admin-card-body">
        @csrf

        <div class="admin-form-grid">
            <div class="admin-field">
                <label for="newUserName">Nama Pengguna</label>
                <input
                    id="newUserName"
                    class="admin-input"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    maxlength="120"
                    required
                >
            </div>

            <div class="admin-field">
                <label for="newUserEmail">Email Google</label>
                <input
                    id="newUserEmail"
                    class="admin-input"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    maxlength="190"
                    required
                >
            </div>

            <div class="admin-field">
                <label for="newUserRole">Role Akses</label>
                <select id="newUserRole" class="admin-select" name="role_id" required>
                    <option value="">Pilih role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-field">
                <label>Status Awal</label>
                <input type="hidden" name="is_active" value="0">
                <label class="admin-checkbox" style="margin:0;text-transform:none;letter-spacing:0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')>
                    Aktifkan akun setelah dibuat
                </label>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:13px">
            <button type="submit" class="admin-btn primary">+ Tambah Pengguna</button>
        </div>
    </form>
</section>
@endcan

<section class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2>Daftar Pengguna</h2>
            <small>{{ number_format($users->total()) }} akun ditemukan</small>
        </div>
    </div>

    <form method="GET" action="{{ route('admin-all.users.index') }}" class="admin-card-body" style="border-bottom:1px solid #e1e6eb">
        <div class="admin-filter-bar">
            <div class="admin-field">
                <label for="userSearch">Cari Nama atau Email</label>
                <input
                    id="userSearch"
                    class="admin-input"
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Ketik nama atau email..."
                >
            </div>

            <div class="admin-field">
                <label for="statusFilter">Status</label>
                <select id="statusFilter" class="admin-select" name="status">
                    <option value="">Semua status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div class="admin-field">
                <label for="roleFilter">Role Akses</label>
                <select id="roleFilter" class="admin-select" name="role">
                    <option value="">Semua role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) ($filters['role'] ?? '') === (string) $role->id)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;gap:7px">
                <button type="submit" class="admin-btn primary">Terapkan</button>
                <a href="{{ route('admin-all.users.index') }}" class="admin-btn secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Legacy</th>
                    <th>Role Akses</th>
                    <th>Status</th>
                    <th>Login Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $managedUser)
                    @php($isCurrentUser = auth()->id() === $managedUser->id)
                    <tr>
                        <td>
                            <div class="admin-user-cell">
                                <span class="admin-avatar">
                                    @if ($managedUser->avatar)
                                        <img src="{{ $managedUser->avatar }}" alt="">
                                    @else
                                        {{ $managedUser->initials() }}
                                    @endif
                                </span>
                                <span>
                                    <strong>
                                        {{ $managedUser->name }}
                                        @if ($isCurrentUser) <em style="color:#d71920;font-size:8px">(ANDA)</em> @endif
                                    </strong>
                                    <small>{{ $managedUser->email }}</small>
                                </span>
                            </div>
                        </td>
                        <td>{{ $managedUser->role ?: '-' }}</td>
                        <td>
                            @can('users.assign-role')
                                <form method="POST"
                                      action="{{ route('admin-all.users.role', $managedUser) }}"
                                      class="admin-inline-form">
                                    @csrf
                                    @method('PATCH')

                                    <select class="admin-select" name="role_id" {{ $isCurrentUser ? 'disabled' : '' }}>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" @selected($managedUser->role_id === $role->id)>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="admin-btn secondary" {{ $isCurrentUser ? 'disabled' : '' }}>
                                        Simpan
                                    </button>
                                </form>
                            @else
                                <span class="admin-role-pill">
                                    {{ $managedUser->accessRole?->name ?? 'Belum diatur' }}
                                </span>
                            @endcan
                        </td>
                        <td>
                            <span class="admin-active-pill {{ $managedUser->is_active ? 'active' : 'inactive' }}">
                                {{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            {{ $managedUser->last_login_at?->format('d M Y H:i') ?? 'Belum tercatat' }}
                        </td>
                        <td>
                            @can('users.change-status')
                                <form method="POST" action="{{ route('admin-all.users.status', $managedUser) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $managedUser->is_active ? 0 : 1 }}">

                                    <button
                                        type="submit"
                                        class="admin-btn {{ $managedUser->is_active ? 'warning' : 'success' }}"
                                        {{ $isCurrentUser ? 'disabled' : '' }}
                                        onclick="return confirm('{{ $managedUser->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ addslashes($managedUser->name) }}?')"
                                    >
                                        {{ $managedUser->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:28px;text-align:center;color:#687383">
                            Pengguna tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="admin-pagination">
            <span>
                Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </span>
            <div style="display:flex;gap:7px">
                @if ($users->onFirstPage())
                    <span class="admin-btn secondary" style="opacity:.45">← Sebelumnya</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="admin-btn secondary">← Sebelumnya</a>
                @endif

                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="admin-btn secondary">Berikutnya →</a>
                @else
                    <span class="admin-btn secondary" style="opacity:.45">Berikutnya →</span>
                @endif
            </div>
        </div>
    @endif
</section>
@endsection
