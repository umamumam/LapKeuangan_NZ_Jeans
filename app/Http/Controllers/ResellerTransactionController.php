<?php

namespace App\Http\Controllers;

use App\Models\ResellerTransaction;
use App\Models\ResellerTransactionDetail;
use App\Models\Reseller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ResellerPayment;
use Illuminate\Support\Facades\Storage;

class ResellerTransactionController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $resellers = Reseller::with(['barangs'])->orderBy('nama')->get();

        $allTransactions = ResellerTransaction::whereYear('tgl', $year)
            ->whereMonth('tgl', $month)
            ->get();

        // 5 Weeks Global Recap
        $rekapGlobal = [
            'minggu_1' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
            'minggu_2' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
            'minggu_3' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
            'minggu_4' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
            'minggu_5' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
        ];

        foreach ($allTransactions as $trx) {
            $day = Carbon::parse($trx->tgl)->day;
            if ($day <= 7) {
                $week = 'minggu_1';
            } elseif ($day <= 14) {
                $week = 'minggu_2';
            } elseif ($day <= 21) {
                $week = 'minggu_3';
            } elseif ($day <= 28) {
                $week = 'minggu_4';
            } else {
                $week = 'minggu_5';
            }

            $rekapGlobal[$week]['total_uang'] += $trx->total_uang;
            $rekapGlobal[$week]['bayar'] += $trx->bayar;
            $rekapGlobal[$week]['sisa_kurang'] += $trx->sisa_kurang;
            $rekapGlobal[$week]['total_keuntungan'] += $trx->total_keuntungan;
        }

        foreach ($resellers as $reseller) {
            $trx = $allTransactions->where('reseller_id', $reseller->id);
            $reseller->total_uang = $trx->sum('total_uang');
            $reseller->bayar = $trx->sum('bayar');
            $reseller->sisa_kurang = $trx->sum('sisa_kurang') - $reseller->hutang_awal;
            $reseller->total_keuntungan = $trx->sum('total_keuntungan');
        }

        // Orang yang Sisa/Kurang < 0 (berhutang/tagihan)
        $resellersWithDebt = $resellers->filter(function($r) {
            return $r->sisa_kurang < 0;
        })->values();

        return view('reseller_transactions.index', compact('resellers', 'rekapGlobal', 'resellersWithDebt', 'month', 'year'));
    }

    public function resellerShow(Request $request, Reseller $reseller)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $transactions = ResellerTransaction::with('details.barang')
            ->where('reseller_id', $reseller->id)
            ->whereYear('tgl', $year)
            ->whereMonth('tgl', $month)
            ->orderBy('tgl', 'desc')
            ->get();

        $rekap = [
            'minggu_1' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
            'minggu_2' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
            'minggu_3' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
            'minggu_4' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
            'minggu_5' => ['total_uang' => 0, 'bayar' => 0, 'sisa_kurang' => 0, 'total_keuntungan' => 0],
        ];

        $hasDebt = $reseller->hutang_awal > 0;

        foreach ($transactions as $trx) {
            if ($trx->sisa_kurang < 0) {
                $hasDebt = true;
            }

            $day = Carbon::parse($trx->tgl)->day;
            if ($day <= 7) {
                $week = 'minggu_1';
            } elseif ($day <= 14) {
                $week = 'minggu_2';
            } elseif ($day <= 21) {
                $week = 'minggu_3';
            } elseif ($day <= 28) {
                $week = 'minggu_4';
            } else {
                $week = 'minggu_5';
            }

            $rekap[$week]['total_uang'] += $trx->total_uang;
            $rekap[$week]['bayar'] += $trx->bayar;
            $rekap[$week]['sisa_kurang'] += $trx->sisa_kurang;
            $rekap[$week]['total_keuntungan'] += $trx->total_keuntungan;
        }

        $payments = \App\Models\ResellerPayment::where('reseller_id', $reseller->id)
            ->whereYear('tgl', $year)
            ->whereMonth('tgl', $month)
            ->orderBy('tgl', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('reseller_transactions.reseller_show', compact('reseller', 'transactions', 'rekap', 'month', 'year', 'hasDebt', 'payments'));
    }

    public function create(Request $request)
    {
        $resellerId = $request->query('reseller_id');

        if (!$resellerId) {
            return redirect()->route('reseller_transactions.index')->with('error', 'Silahkan pilih reseller terlebih dahulu.');
        }

        $reseller = Reseller::findOrFail($resellerId);
        
        $specificBarangs = Barang::where('reseller_id', $resellerId)->get();
        
        if ($specificBarangs->isNotEmpty()) {
            $barangs = $specificBarangs->sortBy('namabarang');
        } else {
            $barangs = Barang::whereNull('reseller_id')
                            ->whereNull('supplier_id')
                            ->orderBy('namabarang')
                            ->get();
        }

        return view('reseller_transactions.create', compact('reseller', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reseller_id' => 'required|exists:resellers,id',
            'tgl' => 'required|date',
            'bayar' => 'required|integer',
            'retur' => 'nullable|integer',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barangs,id',
            'details.*.jumlah' => 'required|integer|min:1',
            'details.*.subtotal' => 'required|integer',
            'details.*.keuntungan' => 'nullable|integer',
            'bukti_tf' => 'nullable|image|max:2048',
        ]);

        $buktiTfPath = null;
        if ($request->hasFile('bukti_tf')) {
            $buktiTfPath = $request->file('bukti_tf')->store('bukti_tf', 'public');
        }

        try {
            DB::beginTransaction();

            $total_barang = 0;
            $total_uang = 0;
            $total_keuntungan = 0;

            $transaction = ResellerTransaction::create([
                'reseller_id' => $request->reseller_id,
                'tgl' => $request->tgl,
                'total_barang' => 0,
                'total_uang' => 0,
                'total_keuntungan' => 0,
                'bayar' => $request->bayar,
                'sisa_kurang' => 0,
                'retur' => $request->retur ?? 0,
                'bukti_tf' => $buktiTfPath,
            ]);

            foreach ($request->details as $detail) {
                $subtotal = $detail['subtotal'];
                $keuntungan_item = $detail['keuntungan'] ?? 0;

                ResellerTransactionDetail::create([
                    'reseller_transaction_id' => $transaction->id,
                    'barang_id' => $detail['barang_id'],
                    'jumlah' => $detail['jumlah'],
                    'subtotal' => $subtotal,
                    'keuntungan' => $keuntungan_item,
                ]);

                $total_barang += $detail['jumlah'];
                $total_uang += $subtotal;
                $total_keuntungan += $keuntungan_item;
            }

            $sisa_kurang = $request->bayar - $total_uang;

            $transaction->update([
                'total_barang' => $total_barang,
                'total_uang' => $total_uang,
                'total_keuntungan' => $total_keuntungan,
                'sisa_kurang' => $sisa_kurang
            ]);

            DB::commit();

            if ($request->bayar > 0) {
                ResellerPayment::create([
                    'reseller_id' => $request->reseller_id,
                    'reseller_transaction_id' => $transaction->id,
                    'tgl' => $request->tgl,
                    'nominal' => $request->bayar,
                    'bukti_tf' => $buktiTfPath,
                    'keterangan' => 'Pembayaran Awal Transaksi',
                ]);
            }

            return redirect()->route('reseller_transactions.show_reseller', $request->reseller_id)->with('success', 'Transaksi reseller berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan transaksi: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(ResellerTransaction $resellerTransaction)
    {
        $resellerTransaction->load('details');
        $reseller = Reseller::findOrFail($resellerTransaction->reseller_id);
        
        $specificBarangs = Barang::where('reseller_id', $reseller->id)->get();

        if ($specificBarangs->isNotEmpty()) {
            $barangs = $specificBarangs->sortBy('namabarang');
        } else {
            $barangs = Barang::whereNull('reseller_id')
                            ->whereNull('supplier_id')
                            ->orderBy('namabarang')
                            ->get();
        }

        return view('reseller_transactions.edit', compact('resellerTransaction', 'reseller', 'barangs'));
    }

    public function update(Request $request, ResellerTransaction $resellerTransaction)
    {
        $request->validate([
            'reseller_id' => 'required|exists:resellers,id',
            'tgl' => 'required|date',
            'bayar' => 'required|integer',
            'retur' => 'nullable|integer',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barangs,id',
            'details.*.jumlah' => 'required|integer|min:1',
            'details.*.subtotal' => 'required|integer',
            'details.*.keuntungan' => 'nullable|integer',
            'bukti_tf' => 'nullable|image|max:2048',
        ]);

        $buktiTfPath = $resellerTransaction->bukti_tf;
        if ($request->hasFile('bukti_tf')) {
            if ($buktiTfPath) {
                Storage::disk('public')->delete($buktiTfPath);
            }
            $buktiTfPath = $request->file('bukti_tf')->store('bukti_tf', 'public');
        }

        try {
            DB::beginTransaction();

            $total_barang = 0;
            $total_uang = 0;
            $total_keuntungan = 0;

            // Delete old details
            ResellerTransactionDetail::where('reseller_transaction_id', $resellerTransaction->id)->delete();

            foreach ($request->details as $detail) {
                $subtotal = $detail['subtotal'];
                $keuntungan_item = $detail['keuntungan'] ?? 0;

                ResellerTransactionDetail::create([
                    'reseller_transaction_id' => $resellerTransaction->id,
                    'barang_id' => $detail['barang_id'],
                    'jumlah' => $detail['jumlah'],
                    'subtotal' => $subtotal,
                    'keuntungan' => $keuntungan_item,
                ]);

                $total_barang += $detail['jumlah'];
                $total_uang += $subtotal;
                $total_keuntungan += $keuntungan_item;
            }

            $sisa_kurang = $request->bayar - $total_uang;

            $resellerTransaction->update([
                'tgl' => $request->tgl,
                'total_barang' => $total_barang,
                'total_uang' => $total_uang,
                'total_keuntungan' => $total_keuntungan,
                'bayar' => $request->bayar,
                'sisa_kurang' => $sisa_kurang,
                'retur' => $request->retur ?? 0,
                'bukti_tf' => $buktiTfPath,
            ]);

            DB::commit();

            return redirect()->route('reseller_transactions.show_reseller', $resellerTransaction->reseller_id)->with('success', 'Transaksi reseller berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengubah transaksi: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(ResellerTransaction $resellerTransaction)
    {
        try {
            $resellerTransaction->delete();
            return redirect()->back()->with('success', 'Transaksi berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    public function payDebt(Request $request, Reseller $reseller)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'bukti_tf' => 'required|image|max:2048',
            'tgl' => 'required|date'
        ]);

        $buktiTfPath = null;
        if ($request->hasFile('bukti_tf')) {
            $buktiTfPath = $request->file('bukti_tf')->store('bukti_tf', 'public');
        }

        $nominalAsli = $request->nominal;
        $nominal = $request->nominal;

        // 1. Bayar Hutang Awal dulu jika ada
        if ($reseller->hutang_awal > 0) {
            if ($nominal >= $reseller->hutang_awal) {
                $nominal -= $reseller->hutang_awal;
                $reseller->hutang_awal = 0;
            } else {
                $reseller->hutang_awal -= $nominal;
                $nominal = 0;
            }
            $reseller->save();
        }

        // 2. Jika masih ada sisa nominal, baru potong transaksi
        if ($nominal > 0) {
            $debtTransactions = ResellerTransaction::where('reseller_id', $reseller->id)
                ->where('sisa_kurang', '<', 0)
                ->orderBy('tgl', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($debtTransactions as $trx) {
                if ($nominal <= 0) {
                    break;
                }

                $hutang = abs($trx->sisa_kurang);

                if ($nominal >= $hutang) {
                    $trx->bayar += $hutang;
                    $trx->sisa_kurang = 0;
                    $nominal -= $hutang;
                } else {
                    $trx->bayar += $nominal;
                    $trx->sisa_kurang += $nominal; 
                    $nominal = 0;
                }

                $trx->save();
            }
        }

        ResellerPayment::create([
            'reseller_id' => $reseller->id,
            'tgl' => $request->tgl,
            'nominal' => $nominalAsli,
            'bukti_tf' => $buktiTfPath,
            'keterangan' => 'Pelunasan Tagihan Otomatis'
        ]);

        return redirect()->back()->with('success', 'Pembayaran tagihan berhasil dicatat.');
    }
}
