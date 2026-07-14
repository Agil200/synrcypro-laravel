@extends('layouts.app')

@section('title', 'Dashboard — SYNRCYPRO')
@section('body-class', 'dashboard-page')

@section('content')
@php
    $icon = function (string $name): string {
        return match ($name) {
            'briefcase' => '<svg viewBox="0 0 24 24"><path d="M9 6V4h6v2m-9 3h12a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Zm-2 5h16M10 9V7h4v2"/></svg>',
            'users' => '<svg viewBox="0 0 24 24"><path d="M16 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m6.5-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8.5 10v-2a4 4 0 0 0-3-3.87m1-11.95a4 4 0 0 1 0 7.75"/></svg>',
            'shield' => '<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Zm-3.5-10 2.2 2.2 4.8-5"/></svg>',
            default => '<svg viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.3 3.6 2.4 18a2 2 0 0 0 1.75 3h15.7a2 2 0 0 0 1.75-3L13.7 3.6a2 2 0 0 0-3.4 0Z"/></svg>',
        };
    };
@endphp

<div class="dashboard-shell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="mini-brand-mark">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M6 15 18 7l8 6 10-5 7 5-17 11-8-6-12 8Z" fill="currentColor"/>
                    <path d="M6 25 18 17l8 6 10-5 7 5-17 11-8-6-12 8Z" fill="currentColor" opacity=".55"/>
                </svg>
            </div>
            <div>
                <strong>SYNRCYPRO</strong>
                <span>Operation Center</span>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Navigasi utama">
            <p>MAIN MENU</p>
            <a class="active" href="{{ route('dashboard') }}">
                <svg viewBox="0 0 24 24"><path d="M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z"/></svg>
                Dashboard
            </a>
            <a href="#monitoring">
                <svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16M7 15l4-4 3 2 5-7"/></svg>
                Monitoring
                <span class="nav-badge">LIVE</span>
            </a>
            <a href="#projects">
                <svg viewBox="0 0 24 24"><path d="M4 7h16v13H4zM8 7V4h8v3M4 11h16"/></svg>
                Pekerjaan
            </a>
            <a href="#team">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Tim & Personel
            </a>
            <a href="#reports">
                <svg viewBox="0 0 24 24"><path d="M5 3h10l4 4v14H5zM14 3v5h5M8 12h8M8 16h8"/></svg>
                Laporan
            </a>

            <p>MANAGEMENT</p>
            <a href="#settings">
                <svg viewBox="0 0 24 24"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm0-13v2m0 15v2m9.5-9.5h-2m-15 0h-2m16.2-6.2-1.4 1.4M6.7 17.3l-1.4 1.4m13.4 0-1.4-1.4M6.7 6.7 5.3 5.3"/></svg>
                Pengaturan
            </a>
        </nav>

        <div class="sidebar-system">
            <span class="status-dot"></span>
            <div>
                <strong>All Systems Operational</strong>
                <small>Last sync 30 sec ago</small>
            </div>
        </div>
    </aside>

    <section class="dashboard-main">
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Buka navigasi">
                <svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <div class="topbar-search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="search" placeholder="Cari proyek, unit, atau laporan...">
                <kbd>⌘ K</kbd>
            </div>

            <div class="topbar-actions">
                <button class="icon-button" type="button" aria-label="Notifikasi">
                    <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4"/></svg>
                    <span class="notification-dot"></span>
                </button>

                <div class="profile">
                    @if (auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                    @else
                        <div class="profile-initials">{{ auth()->user()->initials() }}</div>
                    @endif
                    <div>
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ auth()->user()->role }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit" title="Keluar">
                        <svg viewBox="0 0 24 24"><path d="M10 17l5-5-5-5m5 5H3m9-9h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <main class="dashboard-content">
            <section class="welcome-row">
                <div>
                    <p class="eyebrow">OPERATION OVERVIEW</p>
                    <h1>Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
                    <p>Pantau aktivitas operasional, keselamatan, dan progres proyek dalam satu layar.</p>
                </div>
                <div class="live-time">
                    <span class="status-dot"></span>
                    <div>
                        <small>WAKTU SERVER</small>
                        <strong id="liveClock">--:--:-- WIB</strong>
                    </div>
                </div>
            </section>

            <section class="stats-grid" aria-label="Ringkasan statistik">
                @foreach ($stats as $index => $stat)
                    <article class="stat-card">
                        <div class="stat-icon">{!! $icon($stat['icon']) !!}</div>
                        <div class="stat-meta">
                            <span>{{ $stat['label'] }}</span>
                            <strong>{{ $stat['value'] }}</strong>
                        </div>
                        <span class="stat-change {{ $index === 3 ? 'warning' : '' }}">{{ $stat['change'] }}</span>
                    </article>
                @endforeach
            </section>

            <section class="dashboard-grid">
                <article class="panel performance-panel" id="monitoring">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">PERFORMANCE</p>
                            <h2>Operational Output</h2>
                        </div>
                        <select aria-label="Rentang grafik">
                            <option>12 Bulan</option>
                            <option>30 Hari</option>
                            <option>7 Hari</option>
                        </select>
                    </div>
                    <div class="chart-summary">
                        <div><strong>96.4%</strong><span>Target tercapai</span></div>
                        <div><strong>+12.8%</strong><span>vs periode lalu</span></div>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="performanceChart" data-values='@json($performance)' aria-label="Grafik performa operasional"></canvas>
                    </div>
                    <div class="chart-labels">
                        @foreach (['Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun','Jul'] as $month)
                            <span>{{ $month }}</span>
                        @endforeach
                    </div>
                </article>

                <article class="panel compliance-panel">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">SAFETY</p>
                            <h2>Compliance Score</h2>
                        </div>
                        <span class="panel-tag">Excellent</span>
                    </div>
                    <div class="compliance-body">
                        <div class="donut" style="--value: 98.7">
                            <div><strong>98.7%</strong><span>Score</span></div>
                        </div>
                        <div class="compliance-list">
                            <div><span><i class="legend green"></i>Inspeksi</span><strong>100%</strong></div>
                            <div><span><i class="legend blue"></i>APD</span><strong>99.2%</strong></div>
                            <div><span><i class="legend yellow"></i>Pelatihan</span><strong>96.8%</strong></div>
                        </div>
                    </div>
                    <div class="compliance-note">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                        Tidak ada insiden fatal selama 184 hari
                    </div>
                </article>
            </section>

            <section class="dashboard-grid lower-grid">
                <article class="panel system-panel">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">INFRASTRUCTURE</p>
                            <h2>System Status</h2>
                        </div>
                        <button class="text-button" type="button">View Details</button>
                    </div>
                    <div class="system-list">
                        @foreach ($systems as $system)
                            <div class="system-row">
                                <div class="system-name">
                                    <span class="status-dot {{ $system['status'] === 'Maintenance' ? 'maintenance' : '' }}"></span>
                                    <div>
                                        <strong>{{ $system['name'] }}</strong>
                                        <span>{{ $system['status'] }}</span>
                                    </div>
                                </div>
                                <div class="uptime">
                                    <small>UPTIME</small>
                                    <strong>{{ $system['uptime'] }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="panel projects-panel" id="projects">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">PROJECTS</p>
                            <h2>Project Progress</h2>
                        </div>
                        <button class="text-button" type="button">All Projects</button>
                    </div>
                    <div class="project-list">
                        @foreach ($projects as $project)
                            <div class="project-row">
                                <div class="project-head">
                                    <div>
                                        <strong>{{ $project['name'] }}</strong>
                                        <span>{{ $project['team'] }}</span>
                                    </div>
                                    <strong>{{ $project['progress'] }}%</strong>
                                </div>
                                <div class="progress-track"><span style="width: {{ $project['progress'] }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="panel incidents-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">ALERT CENTER</p>
                        <h2>Recent Incidents</h2>
                    </div>
                    <button class="primary-button" type="button">+ Buat Laporan</button>
                </div>

                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kejadian</th>
                                <th>Area</th>
                                <th>Waktu</th>
                                <th>Prioritas</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($incidents as $incident)
                                <tr>
                                    <td><strong>{{ $incident['id'] }}</strong></td>
                                    <td>{{ $incident['title'] }}</td>
                                    <td>{{ $incident['area'] }}</td>
                                    <td>{{ $incident['time'] }} WIB</td>
                                    <td><span class="priority {{ strtolower($incident['level']) }}">{{ $incident['level'] }}</span></td>
                                    <td><button class="row-menu" type="button">•••</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </section>
</div>
@endsection
