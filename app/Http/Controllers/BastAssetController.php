<?php

namespace App\Http\Controllers;

use App\Models\BastAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BastAssetController extends Controller
{
    // Menampilkan halaman berdasarkan jenis asset
    public function index(Request $request)
    {
        // Ambil filter jenis asset dari query parameter ?category=laptop dsb.
        $category = $request->query('category', 'Senter P101X');
        
        $assets = BastAsset::where('jenis_asset', $category)->latest()->get();

        return view('bast.index', compact('assets', 'category'));
    }

    // Menyimpan data form BAST
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nrp'           => 'required|string|max:50',
            'nama'          => 'required|string|max:255',
            'jabatan'       => 'required|string|max:100',
            'jenis_asset'   => 'required|string',
            'departemen'    => 'required|string',
            'no_asset'      => 'nullable|string',
            'serial_number' => 'nullable|string',
            'tanggal_ambil' => 'required|date',
            'file_pdf'      => 'nullable|mimes:pdf|max:5000',
        ]);

        // Handle upload file PDF BAST
        if ($request->hasFile('file_pdf')) {
            $path = $request->file('file_pdf')->store('bast-documents', 'public');
            $validated['file_pdf'] = $path;
        }

        BastAsset::create($validated);

        return redirect()->back()->with('success', 'Data BAST Berhasil Disimpan!');
    }
}