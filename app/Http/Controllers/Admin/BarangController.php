<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;

class BarangController extends Controller
{
    /**
     * Menampilkan halaman utama Dashboard Stock Opname.
     */
    public function index()
    {
        return view('admin-all.stock-opname.index');
    }

    /**
     * Menampilkan halaman Form Pengambilan Barang (UI Publik/Input).
     */
    public function form()
    {
        return view('admin-all.stock-opname.form');
    }

    /**
     * Mengambil konfigurasi publik (daftar barang aktif & data karyawan untuk autofill NRP).
     */
    public function getPublicConfig()
    {
        try {
            $items = DB::table('master_barangs')
                ->where('aktif', true)
                ->orderBy('urutan')
                ->pluck('nama_barang')
                ->toArray();

            if (empty($items)) {
                $items = [
                    'Pulpen Gel', 'Pulpen Pilot', 'Spidol Putih Permanen',
                    'Spidol Hitam Permanen', 'Spidol Hitam Whiteboard',
                    'Buku Saku', 'Isolasi Bening Kecil', 'Isolasi Bening Besar'
                ];
            }

            $karyawanRows = DB::table('employees')->get();
            $karyawan = [];
            foreach ($karyawanRows as $emp) {
                // Normalisasi NRP menjadi huruf besar tanpa spasi berlebih
                $nrpKey = strtoupper(trim($emp->nrp ?? $emp->NRP ?? ''));
                if (!empty($nrpKey)) {
                    $karyawan[$nrpKey] = [
                        'nama' => $emp->nama ?? $emp->NAMA ?? '-',
                        'jabatan' => $emp->jabatan ?? $emp->JABATAN ?? '-'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'appName' => 'PPA Production Inventory System',
                'items' => $items,
                'karyawan' => $karyawan,
                'today' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
                'maxPhotoMb' => 5,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan data pengambilan barang (mendukung multi-item & foto base64).
     */
    public function storePickup(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:100',
                'nrp' => ['required', 'string', 'regex:/^[A-Za-z0-9._\/-]{2,30}$/'],
                'jabatan' => 'nullable|string|max:100',
                'lokasi' => 'required|string|max:100',
                'date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.item' => 'required|string|max:100',
                'items.*.qty' => 'required|integer|min:1',
                'items.*.unit' => 'required|string|max:20',
                'photoDataUrl' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $batchId = 'BRG-' . Carbon::now('Asia/Jakarta')->format('Ymd-His') . '-' . strtoupper(Str::random(6));
            $photoUrl = null;
            $photoPath = null;

            if (!empty($validated['photoDataUrl'])) {
                $dataUrl = $validated['photoDataUrl'];
                if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
                    $image = substr($dataUrl, strpos($dataUrl, ',') + 1);
                    $decodedImage = base64_decode($image);
                    
                    if ($decodedImage !== false) {
                        $extension = strtolower($type[1]);
                        $filename = $batchId . '-' . preg_replace('/[^A-Za-z0-9_-]/', '', $validated['nrp']) . '.' . ($extension === 'png' ? 'png' : 'jpg');
                        $path = 'foto_barang/' . $filename;
                        
                        Storage::disk('public')->put($path, $decodedImage);
                        $photoPath = $path;
                        $photoUrl = asset('storage/' . $path);
                    }
                }
            }

            foreach ($validated['items'] as $index => $entry) {
                $quantityText = $entry['qty'] . ' ' . $entry['unit'];
                $rowId = count($validated['items']) > 1 ? $batchId . '-' . ($index + 1) : $batchId;

                DB::table('pengambilan_barang')->insert([
                    'id' => $rowId,
                    'timestamp' => Carbon::now('Asia/Jakarta'),
                    'nama' => $validated['name'],
                    'nrp' => strtoupper($validated['nrp']),
                    'jabatan' => $validated['jabatan'] ?? '-',
                    'tanggal' => $validated['date'],
                    'barang' => $entry['item'],
                    'jumlah' => $quantityText,
                    'lokasi' => $validated['lokasi'],
                    'foto_url' => $photoUrl,
                    'foto_file_path' => $photoPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $batchId,
                'message' => 'Data berhasil disimpan.'
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Menambahkan data pengambilan secara manual dari Dashboard Admin.
     */
    public function addDashboardTransaction(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:100',
                'nrp' => 'required|string|max:30',
                'jabatan' => 'nullable|string|max:100',
                'lokasi' => 'required|string|max:100',
                'date' => 'required|date',
                'item' => 'required|string|max:100',
                'qty' => 'required|integer|min:1',
                'unit' => 'required|string|max:20',
            ]);

            $id = 'BRG-' . Carbon::now('Asia/Jakarta')->format('Ymd-His') . '-' . strtoupper(Str::random(6));
            $quantityText = $validated['qty'] . ' ' . $validated['unit'];

            DB::table('pengambilan_barang')->insert([
                'id' => $id,
                'timestamp' => Carbon::now('Asia/Jakarta'),
                'nama' => $validated['name'],
                'nrp' => strtoupper($validated['nrp']),
                'jabatan' => $validated['jabatan'] ?? '-',
                'tanggal' => $validated['date'],
                'barang' => $validated['item'],
                'jumlah' => $quantityText,
                'lokasi' => $validated['lokasi'],
                'foto_url' => null,
                'foto_file_path' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Data riwayat berhasil ditambahkan.']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Memperbarui data transaksi yang sudah ada berdasarkan ID.
     */
    public function updateDashboardTransaction(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:100',
                'nrp' => 'required|string|max:30',
                'jabatan' => 'nullable|string|max:100',
                'lokasi' => 'required|string|max:100',
                'date' => 'required|date',
                'item' => 'required|string|max:100',
                'qty' => 'required|integer|min:1',
                'unit' => 'required|string|max:20',
            ]);

            $quantityText = $validated['qty'] . ' ' . $validated['unit'];

            $updated = DB::table('pengambilan_barang')->where('id', $id)->update([
                'nama' => $validated['name'],
                'nrp' => strtoupper($validated['nrp']),
                'jabatan' => $validated['jabatan'] ?? '-',
                'tanggal' => $validated['date'],
                'barang' => $validated['item'],
                'jumlah' => $quantityText,
                'lokasi' => $validated['lokasi'],
                'updated_at' => now(),
            ]);

            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
            }

            return response()->json(['success' => true, 'message' => 'Data riwayat berhasil diperbarui.']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mengambil data untuk Dashboard Admin Barang.
     */
    public function getDashboardData(Request $request)
    {
        try {
            $period = $request->query('period', 'week');
            $anchorDateText = $request->query('anchorDate', Carbon::now('Asia/Jakarta')->format('Y-m-d'));
            $anchorDate = Carbon::parse($anchorDateText, 'Asia/Jakarta')->startOfDay();

            if ($period === 'month') {
                $start = $anchorDate->copy()->startOfMonth();
                $endExclusive = $anchorDate->copy()->addMonth()->startOfMonth();
                $periodLabel = $start->translatedFormat('F Y');
            } else {
                $start = $anchorDate->copy()->startOfWeek(Carbon::MONDAY);
                $endExclusive = $start->copy()->addWeeks(1);
                $endInclusive = $start->copy()->addDays(6);
                $periodLabel = $start->format('d M Y') . ' – ' . $endInclusive->format('d M Y');
            }

            $transactions = DB::table('pengambilan_barang')
                ->whereBetween('tanggal', [$start->format('Y-m-d'), $endExclusive->copy()->subDay()->format('Y-m-d')])
                ->orderBy('timestamp', 'desc')
                ->get();

            $activeItems = DB::table('master_barangs')->where('aktif', true)->pluck('nama_barang')->toArray();
            $breakdown = array_fill_keys($activeItems, 0);
            
            $trendMap = [];
            $uniqueNrp = [];
            $formattedTransactions = [];
            $totalQtySum = 0;

            foreach ($transactions as $row) {
                $item = $row->barang;
                preg_match('/^(\d+)/', $row->jumlah, $matches);
                $numericQty = isset($matches[1]) ? (int)$matches[1] : 1;

                $totalQtySum += $numericQty;
                $uniqueNrp[$row->nrp] = true;

                $dateKey = Carbon::parse($row->tanggal)->format('Y-m-d');
                $trendMap[$dateKey] = ($trendMap[$dateKey] ?? 0) + $numericQty;
                
                if (!isset($breakdown[$item])) {
                    $breakdown[$item] = 0;
                }
                $breakdown[$item] += $numericQty;

                $formattedTransactions[] = [
                    'id' => $row->id,
                    'timestamp' => Carbon::parse($row->timestamp)->format('d M Y, H:i'),
                    'timestampValue' => Carbon::parse($row->timestamp)->timestamp,
                    'name' => $row->nama,
                    'nrp' => $row->nrp,
                    'jabatan' => $row->jabatan ?? '-',
                    'date' => Carbon::parse($row->tanggal)->format('d M Y'),
                    'rawDate' => $row->tanggal,
                    'item' => $item,
                    'qty' => $row->jumlah,
                    'numericQty' => $numericQty,
                    'lokasi' => $row->lokasi ?? '-',
                    'hasPhoto' => !empty($row->foto_url),
                    'photoId' => $row->id,
                ];
            }

            $trend = [];
            $cursor = $start->copy();
            $activeDays = 0;
            while ($cursor < $endExclusive) {
                $key = $cursor->format('Y-m-d');
                $val = $trendMap[$key] ?? 0;
                if ($val > 0) $activeDays++;

                $trend[] = [
                    'key' => $key,
                    'label' => $period === 'week' ? $cursor->format('D') : $cursor->format('j'),
                    'value' => $val,
                ];
                $cursor->addDay();
            }

            $itemBreakdown = [];
            foreach ($breakdown as $name => $val) {
                $itemBreakdown[] = ['name' => $name, 'value' => $val];
            }
            usort($itemBreakdown, function($a, $b) {
                if ($b['value'] !== $a['value']) return $b['value'] - $a['value'];
                return strcmp($a['name'], $b['name']);
            });

            $topItem = (count($itemBreakdown) > 0 && $itemBreakdown[0]['value'] > 0) ? $itemBreakdown[0]['name'] : '-';

            return response()->json([
                'success' => true,
                'period' => $period,
                'anchorDate' => $anchorDate->format('Y-m-d'),
                'periodLabel' => $periodLabel,
                'summary' => [
                    'total' => $totalQtySum,
                    'employees' => count($uniqueNrp),
                    'topItem' => $topItem,
                    'averagePerActiveDay' => $activeDays ? round($totalQtySum / $activeDays, 1) : 0,
                ],
                'trend' => $trend,
                'breakdown' => $itemBreakdown,
                'transactions' => array_slice($formattedTransactions, 0, 500),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus riwayat transaksi dari dashboard admin.
     */
    public function destroyPickup($id)
    {
        try {
            $tx = DB::table('pengambilan_barang')->where('id', $id)->first();
            if ($tx) {
                if (!empty($tx->foto_file_path) && Storage::disk('public')->exists($tx->foto_file_path)) {
                    Storage::disk('public')->delete($tx->foto_file_path);
                }
                DB::table('pengambilan_barang')->where('id', $id)->delete();
            }

            return response()->json(['success' => true, 'message' => 'Data riwayat berhasil dihapus.']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengambil seluruh daftar master barang untuk admin.
     */
    public function getAllAdminItems()
    {
        try {
            $items = DB::table('master_barangs')
                ->orderBy('urutan')
                ->get()
                ->map(function ($row) {
                    return [
                        'kode' => $row->kode,
                        'nama' => $row->nama_barang,
                        'aktif' => (bool)$row->aktif,
                        'urutan' => (int)$row->urutan
                    ];
                });

            return response()->json($items);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menambahkan barang baru ke tabel master_barangs.
     */
    public function addAdminItem(Request $request)
    {
        try {
            $name = trim($request->input('name'));
            if (mb_strlen($name) < 2) {
                return response()->json(['success' => false, 'message' => 'Nama barang minimal 2 karakter.'], 422);
            }

            $exists = DB::table('master_barangs')->where('nama_barang', 'LIKE', $name)->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Barang tersebut sudah ada di master.'], 422);
            }

            $lastOrder = DB::table('master_barangs')->max('urutan') ?? 0;
            $newOrder = $lastOrder + 1;
            $code = 'BRG-' . str_pad($newOrder, 3, '0', STR_PAD_LEFT);

            DB::table('master_barangs')->insert([
                'kode' => $code,
                'nama_barang' => $name,
                'aktif' => true,
                'urutan' => $newOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Barang baru berhasil ditambahkan!']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengubah nama atau status aktif/nonaktif barang.
     */
    public function editAdminItem(Request $request, $code)
    {
        try {
            $name = trim($request->input('nama'));
            $aktif = (bool)$request->input('aktif', true);

            if (mb_strlen($name) < 2) {
                return response()->json(['success' => false, 'message' => 'Nama barang minimal 2 karakter.'], 422);
            }

            DB::table('master_barangs')->where('kode', $code)->update([
                'nama_barang' => $name,
                'aktif' => $aktif,
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Barang berhasil diperbarui.']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Mengambil file foto bukti dari storage publik.
     */
    public function getPhotoData($id)
    {
        try {
            $tx = DB::table('pengambilan_barang')->where('id', $id)->first();
            if (!$tx || empty($tx->foto_file_path)) {
                return response()->json(['success' => false, 'message' => 'Foto tidak ditemukan.'], 404);
            }

            if (!Storage::disk('public')->exists($tx->foto_file_path)) {
                return response()->json(['success' => false, 'message' => 'File foto fisik tidak ada di storage.'], 404);
            }

            $fileContents = Storage::disk('public')->get($tx->foto_file_path);
            $mimeType = Storage::disk('public')->mimeType($tx->foto_file_path);
            $dataUrl = 'data:' . $mimeType . ';base64,' . base64_encode($fileContents);

            return response()->json([
                'success' => true,
                'name' => basename($tx->foto_file_path),
                'dataUrl' => $dataUrl
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}