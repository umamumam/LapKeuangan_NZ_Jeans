<?php

namespace App\Http\Controllers;

use App\Models\PenarikanOmset;
use App\Models\Toko;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenarikanOmsetController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $penarikans = PenarikanOmset::with('toko')
            ->whereYear('tgl', $year)
            ->orderBy('tgl', 'desc')
            ->get();
            
        $tokos = Toko::all();

        // Rekap Bulanan logic - Sorted Jan to Dec
        $rekapBulanan = PenarikanOmset::select(
            DB::raw('DATE_FORMAT(tgl, "%Y-%m") as bulan_key'),
            DB::raw('MONTHNAME(tgl) as bulan'),
            DB::raw('YEAR(tgl) as tahun'),
            'toko_id',
            DB::raw('SUM(jumlah) as total_toko')
        )
        ->whereYear('tgl', $year)
        ->groupBy('bulan_key', 'bulan', 'tahun', 'toko_id')
        ->orderBy('bulan_key', 'asc')
        ->get()
        ->groupBy('bulan_key');

        $availableYears = PenarikanOmset::select(DB::raw('YEAR(tgl) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        return view('penarikan_omset.index', compact('penarikans', 'tokos', 'rekapBulanan', 'year', 'availableYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'toko_id' => 'required|exists:tokos,id',
            'tgl' => 'required|date',
            'jumlah' => 'required|numeric',
        ]);

        PenarikanOmset::create($request->all());

        return redirect()->route('penarikan_omset.index')->with('success', 'Data penarikan omset berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'toko_id' => 'required|exists:tokos,id',
            'tgl' => 'required|date',
            'jumlah' => 'required|numeric',
        ]);

        $penarikanOmset = PenarikanOmset::findOrFail($id);
        $penarikanOmset->update($request->all());

        return redirect()->route('penarikan_omset.index')->with('success', 'Data penarikan omset berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $penarikanOmset = PenarikanOmset::findOrFail($id);
        $penarikanOmset->delete();

        return redirect()->route('penarikan_omset.index')->with('success', 'Data penarikan omset berhasil dihapus.');
    }

    public function show(string $id)
    {
        $penarikanOmset = PenarikanOmset::with('toko')->findOrFail($id);
        return response()->json($penarikanOmset);
    }
}
