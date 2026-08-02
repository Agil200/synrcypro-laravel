<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class McuFuController extends Controller
{
    /**
     * Halaman utama Monitoring MCU dan Follow Up.
     */
    public function index(): View
    {
        return view('manpower.mcu-fu.index');
    }

    /**
     * Mengambil data MCU/FU.
     *
     * Untuk sementara dikembalikan sebagai data kosong
     * sampai koneksi Google Sheets MCU dipasang.
     */
    public function data(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data MCU/FU siap dihubungkan.',
            'data' => [],
        ]);
    }

    /**
     * Menjalankan sinkronisasi data MCU/FU.
     *
     * Backend sinkronisasi akan dilengkapi pada tahap MCU.
     */
    public function refresh(): RedirectResponse
    {
        return back()->with(
            'success',
            'Sinkronisasi MCU/FU belum diaktifkan.'
        );
    }
}