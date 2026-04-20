<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Penggajian;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PenggajianController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $karyawans = Karyawan::all();
        $penggajians = Penggajian::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->keyBy('karyawan_id');

        $first = $penggajians->first();
        $dates = [
            'm1' => $first->tgl_m1 ?? '',
            'm2' => $first->tgl_m2 ?? '',
            'm3' => $first->tgl_m3 ?? '',
            'm4' => $first->tgl_m4 ?? '',
        ];

        return view('penggajian.index', compact('karyawans', 'penggajians', 'bulan', 'tahun', 'dates'));
    }

    public function store(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $dataGaji = $request->gaji;

        $tanggal = Carbon::create($tahun, $bulan, 1)->toDateString();

        foreach ($dataGaji as $karyawan_id => $values) {
            $m1 = (int) ($values['minggu_1'] ?? 0);
            $m2 = (int) ($values['minggu_2'] ?? 0);
            $m3 = (int) ($values['minggu_3'] ?? 0);
            $m4 = (int) ($values['minggu_4'] ?? 0);
            $total = $m1 + $m2 + $m3 + $m4;

            Penggajian::updateOrCreate(
                [
                    'karyawan_id' => $karyawan_id,
                    'tanggal' => $tanggal,
                ],
                [
                    'tgl_m1' => $request->tgl_m1,
                    'tgl_m2' => $request->tgl_m2,
                    'tgl_m3' => $request->tgl_m3,
                    'tgl_m4' => $request->tgl_m4,
                    'minggu_1' => $m1,
                    'minggu_2' => $m2,
                    'minggu_3' => $m3,
                    'minggu_4' => $m4,
                    'nominal' => $total,
                ]
            );
        }

        return redirect()->route('gaji.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', 'Data gaji berhasil disimpan');
    }

    public function destroy(Penggajian $gaji)
    {
        $gaji->delete();
        return redirect()->back()->with('success', 'Data gaji berhasil dihapus');
    }
}
