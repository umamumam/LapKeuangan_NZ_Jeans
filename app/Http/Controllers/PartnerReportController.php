<?php

namespace App\Http\Controllers;

use App\Models\ResellerTransaction;
use App\Models\SupplierTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerReportController extends Controller
{
    public function reseller(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        // Overall Summary (Months as columns)
        $transactions = ResellerTransaction::whereYear('tgl', $tahun)
            ->select(
                DB::raw('MONTH(tgl) as bulan'),
                DB::raw('COUNT(DISTINCT reseller_id) as total_reseller'),
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_uang) as total_uang'),
                DB::raw('SUM(total_keuntungan) as total_keuntungan'),
                DB::raw('SUM(bayar) as total_bayar'),
                DB::raw('SUM(sisa_kurang) as total_piutang'),
                DB::raw('SUM(retur) as total_retur')
            )
            ->groupBy(DB::raw('MONTH(tgl)'))
            ->get()
            ->keyBy('bulan');

        $hasil = [];
        foreach ($bulanList as $index => $bulan) {
            $monthNum = $index + 1;
            $data = $transactions->get($monthNum);
            $hasil[$bulan] = $data ? [
                'total_reseller' => $data->total_reseller,
                'total_barang' => $data->total_barang,
                'total_penjualan' => $data->total_uang,
                'total_keuntungan' => $data->total_keuntungan,
                'total_bayar' => $data->total_bayar,
                'total_piutang' => $data->total_piutang,
                'total_retur' => $data->total_retur,
            ] : [
                'total_reseller' => 0, 'total_barang' => 0, 'total_penjualan' => 0,
                'total_keuntungan' => 0, 'total_bayar' => 0, 'total_piutang' => 0, 'total_retur' => 0,
            ];
        }

        // Per Reseller Summary for the Year
        $resellerData = ResellerTransaction::whereYear('tgl', $tahun)
            ->join('resellers', 'reseller_transactions.reseller_id', '=', 'resellers.id')
            ->select(
                'resellers.nama',
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_uang) as total_penjualan'),
                DB::raw('SUM(total_keuntungan) as total_keuntungan'),
                DB::raw('SUM(bayar) as total_bayar'),
                DB::raw('SUM(sisa_kurang) as total_piutang')
            )
            ->groupBy('reseller_id', 'resellers.nama')
            ->orderBy('total_penjualan', 'desc')
            ->get();

        return view('reports.reseller', compact('hasil', 'resellerData', 'tahun', 'bulanList'));
    }

    public function supplier(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        // Overall Summary
        $transactions = SupplierTransaction::whereYear('tgl', $tahun)
            ->select(
                DB::raw('MONTH(tgl) as bulan'),
                DB::raw('COUNT(DISTINCT supplier_id) as total_supplier'),
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_uang) as total_uang'),
                DB::raw('SUM(bayar) as total_bayar'),
                DB::raw('SUM(total_tagihan) as total_hutang'),
                DB::raw('SUM(retur) as total_retur')
            )
            ->groupBy(DB::raw('MONTH(tgl)'))
            ->get()
            ->keyBy('bulan');

        $hasil = [];
        foreach ($bulanList as $index => $bulan) {
            $monthNum = $index + 1;
            $data = $transactions->get($monthNum);
            $hasil[$bulan] = $data ? [
                'total_supplier' => $data->total_supplier,
                'total_barang' => $data->total_barang,
                'total_pembelian' => $data->total_uang,
                'total_bayar' => $data->total_bayar,
                'total_hutang' => $data->total_hutang,
                'total_retur' => $data->total_retur,
            ] : [
                'total_supplier' => 0, 'total_barang' => 0, 'total_pembelian' => 0,
                'total_bayar' => 0, 'total_hutang' => 0, 'total_retur' => 0,
            ];
        }

        // Per Supplier Summary for the Year
        $supplierData = SupplierTransaction::whereYear('tgl', $tahun)
            ->join('suppliers', 'supplier_transactions.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.nama',
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_uang) as total_pembelian'),
                DB::raw('SUM(bayar) as total_bayar'),
                DB::raw('SUM(total_tagihan) as total_hutang')
            )
            ->groupBy('supplier_id', 'suppliers.nama')
            ->orderBy('total_pembelian', 'desc')
            ->get();

        return view('reports.supplier', compact('hasil', 'supplierData', 'tahun', 'bulanList'));
    }
}
