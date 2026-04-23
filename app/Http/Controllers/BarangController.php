<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Reseller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Exports\BarangExport;
use App\Imports\BarangImport;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::with(['reseller', 'supplier'])->orderBy('id', 'asc')->get();
        $resellers = Reseller::orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama')->get();
        return view('barangs.index', compact('barangs', 'resellers', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reseller_id' => 'nullable|exists:resellers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'namabarang' => 'required|string|max:255',
            'ukuran' => 'nullable|string|max:50',
            'hpp' => 'nullable|numeric',
            'hargabeli_perpotong' => 'nullable|numeric',
            'hargabeli_perlusin' => 'nullable|numeric',
            'hargajual_perpotong' => 'nullable|numeric',
            'hargajual_perlusin' => 'nullable|numeric',
            'harga_grosir' => 'nullable|numeric',
            'keuntungan' => 'nullable|numeric',
        ]);

        try {
            $data = $request->all();
            foreach (['hpp', 'hargabeli_perpotong', 'hargabeli_perlusin', 'hargajual_perpotong', 'hargajual_perlusin', 'harga_grosir', 'keuntungan'] as $field) {
                if (isset($data[$field])) {
                    $data[$field] = round($data[$field]);
                }
            }
            Barang::create($data);
            return redirect()->back()->with('success', 'Barang berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan barang: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'reseller_id' => 'nullable|exists:resellers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'namabarang' => 'required|string|max:255',
            'ukuran' => 'nullable|string|max:50',
            'hpp' => 'nullable|numeric',
            'hargabeli_perpotong' => 'nullable|numeric',
            'hargabeli_perlusin' => 'nullable|numeric',
            'hargajual_perpotong' => 'nullable|numeric',
            'hargajual_perlusin' => 'nullable|numeric',
            'harga_grosir' => 'nullable|numeric',
            'keuntungan' => 'nullable|numeric',
        ]);

        try {
            $data = $request->all();
            foreach (['hpp', 'hargabeli_perpotong', 'hargabeli_perlusin', 'hargajual_perpotong', 'hargajual_perlusin', 'harga_grosir', 'keuntungan'] as $field) {
                if (isset($data[$field])) {
                    $data[$field] = round($data[$field]);
                }
            }
            $barang->update($data);
            return redirect()->back()->with('success', 'Barang berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui barang: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Barang $barang)
    {
        try {
            $barang->delete();
            return redirect()->back()->with('success', 'Barang berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    public function show(Barang $barang)
    {
        return response()->json($barang);
    }

    public function export()
    {
        $filename = 'data_barang_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new BarangExport, $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new BarangImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data barang berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    public function deleteAll()
    {
        try {
            $count = Barang::count();
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data barang untuk dihapus.'
                ], 400);
            }

            // Gunakan delete() alih-alih truncate() untuk menghindari error foreign key constraint
            Barang::query()->delete();

            return response()->json([
                'success' => true,
                'message' => "Semua data barang ($count data) berhasil dihapus!"
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            // Memberikan pesan yang lebih ramah jika terjadi error relasi database
            if (str_contains($message, 'foreign key constraint')) {
                $message = 'Beberapa barang tidak bisa dihapus karena sudah digunakan dalam transaksi (Reseller Transaction). Hapus data transaksi terkait terlebih dahulu jika ingin mengosongkan daftar barang.';
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus semua data barang: ' . $message
            ], 500);
        }
    }
}
