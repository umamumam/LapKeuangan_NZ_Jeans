<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use Illuminate\Http\Request;

class PettyCashController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $data = PettyCash::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc')
            ->get();

        $totalPengeluaran = $data->sum('jumlah');

        return view('petty_cash.index', compact('data', 'bulan', 'tahun', 'totalPengeluaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_barang' => 'required|string',
            'harga_satuan' => 'required|numeric',
        ]);

        $data = $request->all();
        // Pastikan nilai numerik tidak null agar tidak error SQL
        $data['ball'] = $request->ball ?? 0;
        $data['pack'] = $request->pack ?? 0;
        $data['kurang_bayar'] = $request->kurang_bayar ?? 0;
        $data['jumlah'] = $request->jumlah ?? 0;

        PettyCash::create($data);

        return redirect()->back()->with('success', 'Data Petty Cash berhasil ditambahkan');
    }

    public function update(Request $request, PettyCash $pettyCash)
    {
        $pettyCash->update($request->all());
        return redirect()->back()->with('success', 'Data Petty Cash berhasil diperbarui');
    }

    public function destroy(PettyCash $pettyCash)
    {
        $pettyCash->delete();
        return redirect()->back()->with('success', 'Data Petty Cash berhasil dihapus');
    }
}
