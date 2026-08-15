@extends('admin-all.layout')

@section('admin-page-title', 'Dashboard Admin All')

@section('admin-content')
<div class="admin-page-heading">
    <div>
        <h1>Pusat Administrasi SYNRGYPRO</h1>
        <p>
            Kelola pengguna dan akses module dari satu tempat. Module operasional
            akan diaktifkan bertahap setelah masing-masing integrasi lolos pengujian.
        </p>
    </div>

    @can('users.view')
        <a href="{{ route('admin-all.users.index') }}" class="admin-btn primary">
            Kelola Pengguna →
        </a>
    @endcan
</div>

<div class="admin-grid stats">
    <div class="admin-card admin-stat-card" style="--stat-color:#1669b2">
        <small>Total Pengguna</small>
        <strong>{{ number_format($stats['total_users']) }}</strong>
        <span>Seluruh akun pada database SYNRGYPRO</span>
    </div>

    <div class="admin-card admin-stat-card" style="--stat-color:#17864b">
        <small>Pengguna Aktif</small>
        <strong>{{ number_format($stats['active_users']) }}</strong>
        <span>Dapat mengakses aplikasi sesuai role</span>
    </div>

    <div class="admin-card admin-stat-card" style="--stat-color:#d71920">
        <small>Pengguna Nonaktif</small>
        <strong>{{ number_format($stats['inactive_users']) }}</strong>
        <span>Akses login diblokir oleh sistem</span>
    </div>

    <div class="admin-card admin-stat-card" style="--stat-color:#7a45a7">
        <small>Administrator</small>
        <strong>{{ number_format($stats['administrators']) }}</strong>
        <span>Super Administrator dan Administrator</span>
    </div>
</div>

<h2 class="admin-section-title">Module Admin All</h2>

<div class="admin-grid modules">
    <a href="{{ route('admin-all.users.index') }}" class="admin-card admin-module-card enabled">
        <div class="admin-module-top">
            <span class="admin-module-icon" style="--module-color:#1669b2">U</span>
            <span class="admin-status-pill ready">Aktif</span>
        </div>
        <h3>User Management</h3>
        <p>Tambah pengguna, atur role akses, dan aktif/nonaktifkan akun.</p>
    </a>

    @foreach ([
        ['R', 'Role & Permission', 'Pengaturan hak akses per role.', '#7a45a7'],
        ['S', 'Suggestion System', 'Monitoring workflow QCC, GL, dan SH.', '#d47a09'],
        ['I', 'IFUTS Case Desk', 'Monitoring kasus lintas departemen.', '#176f79'],
        ['M', 'MCU & FU Internal', 'Input MCU dan follow up internal.', '#17864b'],
        ['O', 'Stock Opname Gudang', 'Dashboard dan input opname bulanan.', '#825e24'],
        ['E', 'E-Arsip', 'Katalog folder Google Drive Produksi.', '#455d78'],
        ['A', 'Activity Log', 'Riwayat tindakan seluruh administrator.', '#4d5660'],
    ] as [$icon, $title, $description, $color])
        <div class="admin-card admin-module-card">
            <div class="admin-module-top">
                <span class="admin-module-icon" style="--module-color:{{ $color }}">{{ $icon }}</span>
                <span class="admin-status-pill">Bertahap</span>
            </div>
            <h3>{{ $title }}</h3>
            <p>{{ $description }}</p>
        </div>
    @endforeach
</div>

<div class="admin-grid two" style="margin-top:24px">
    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2>Pengguna Terbaru Diperbarui</h2>
                <small>Delapan akun dengan perubahan terakhir</small>
            </div>
            @can('users.view')
                <a href="{{ route('admin-all.users.index') }}" class="admin-btn secondary">Lihat semua</a>
            @endcan
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role Akses</th>
                        <th>Status</th>
                        <th>Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentUsers as $user)
                        <tr>
                            <td>
                                <div class="admin-user-cell">
                                    <span class="admin-avatar">
                                        @if ($user->avatar)
                                            <img src="{{ $user->avatar }}" alt="">
                                        @else
                                            {{ $user->initials() }}
                                        @endif
                                    </span>
                                    <span>
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->email }}</small>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="admin-role-pill">
                                    {{ $user->accessRole?->name ?? 'Belum diatur' }}
                                </span>
                            </td>
                            <td>
                                <span class="admin-active-pill {{ $user->is_active ? 'active' : 'inactive' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>{{ $user->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2>Ringkasan Role</h2>
                <small>Distribusi user berdasarkan role baru</small>
            </div>
        </div>

        <div class="admin-card-body">
            @foreach ($roleSummary as $role)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #edf0f2">
                    <div>
                        <strong style="display:block;font-size:11px">{{ $role->name }}</strong>
                        <small style="color:#687383;font-size:9px">{{ $role->slug }}</small>
                    </div>
                    <strong style="font-size:18px">{{ $role->users_count }}</strong>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
