<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'stats' => [
                ['label' => 'Proyek Aktif', 'value' => '24', 'change' => '+8.2%', 'icon' => 'briefcase'],
                ['label' => 'Personel Bertugas', 'value' => '86', 'change' => '+4 hari ini', 'icon' => 'users'],
                ['label' => 'Safety Compliance', 'value' => '98.7%', 'change' => '+1.4%', 'icon' => 'shield'],
                ['label' => 'Peringatan Aktif', 'value' => '3', 'change' => 'Perlu tindakan', 'icon' => 'alert'],
            ],
            'performance' => [58, 65, 61, 72, 76, 74, 82, 86, 83, 91, 89, 96],
            'systems' => [
                ['name' => 'Dispatch & Fleet', 'status' => 'Normal', 'uptime' => '99.99%'],
                ['name' => 'Safety Monitoring', 'status' => 'Normal', 'uptime' => '99.97%'],
                ['name' => 'Production Server', 'status' => 'Normal', 'uptime' => '99.95%'],
                ['name' => 'CCTV Network', 'status' => 'Maintenance', 'uptime' => '97.82%'],
            ],
            'projects' => [
                ['name' => 'Pit A — Overburden', 'team' => 'Tim Alpha', 'progress' => 84],
                ['name' => 'Hauling Road KM 14', 'team' => 'Tim Bravo', 'progress' => 67],
                ['name' => 'Workshop Expansion', 'team' => 'Tim Engineering', 'progress' => 49],
            ],
            'incidents' => [
                ['id' => 'INC-2407', 'title' => 'Sensor suhu unit DT-18', 'area' => 'Pit A', 'time' => '08:42', 'level' => 'Medium'],
                ['id' => 'INC-2406', 'title' => 'Koneksi CCTV terputus', 'area' => 'Workshop', 'time' => '07:15', 'level' => 'Low'],
                ['id' => 'INC-2405', 'title' => 'Batas kecepatan terlampaui', 'area' => 'Hauling KM 8', 'time' => '06:48', 'level' => 'High'],
            ],
        ]);
    }
}
