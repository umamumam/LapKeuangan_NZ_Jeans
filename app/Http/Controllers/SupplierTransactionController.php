<?php

namespace App\Http\Controllers;

use App\Models\SupplierTransaction;
use App\Models\SupplierTransactionDetail;
use App\Models\Supplier;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\Storage;

class SupplierTransactionController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $suppliers = Supplier::with(['barangs'])
            ->withMax('transactions', 'updated_at')
            ->withMax('payments', 'updated_at')
            ->orderByRaw('GREATEST(
                COALESCE(updated_at, "1970-01-01"),
                COALESCE(transactions_max_updated_at, "1970-01-01"),
                COALESCE(payments_max_updated_at, "1970-01-01")
            ) DESC')
            ->get();
        // $suppliers = Supplier::with(['barangs'])->orderBy('nama')->get();

        $allTransactions = SupplierTransaction::whereYear('tgl', $year)
            ->whereMonth('tgl', $month)
            ->get();

        // 5 Weeks Global Recap
        $rekapGlobal = [
            'minggu_1' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
            'minggu_2' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
            'minggu_3' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
            'minggu_4' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
            'minggu_5' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
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
            $rekapGlobal[$week]['total_tagihan'] += $trx->total_tagihan;
        }

        foreach ($suppliers as $supplier) {
            $trx = $allTransactions->where('supplier_id', $supplier->id);
            $supplier->total_uang = $trx->sum('total_uang');
            $supplier->bayar = $trx->sum('bayar');
            $supplier->total_tagihan = $trx->sum('total_tagihan') - $supplier->hutang_awal;
        }

        // Orang yang Sisa/Kurang < 0 (berhutang/tagihan)
        $suppliersWithDebt = $suppliers->filter(function ($r) {
            return $r->total_tagihan < 0;
        })->values();

        return view('supplier_transactions.index', compact('suppliers', 'rekapGlobal', 'suppliersWithDebt', 'month', 'year'));
    }

    public function supplierShow(Request $request, Supplier $supplier)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $transactions = SupplierTransaction::with('details.barang')
            ->where('supplier_id', $supplier->id)
            ->whereYear('tgl', $year)
            ->whereMonth('tgl', $month)
            ->orderBy('tgl', 'desc')
            ->get();

        $rekap = [
            'minggu_1' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
            'minggu_2' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
            'minggu_3' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
            'minggu_4' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
            'minggu_5' => ['total_uang' => 0, 'bayar' => 0, 'total_tagihan' => 0],
        ];

        $hasDebt = $supplier->hutang_awal > 0;

        foreach ($transactions as $trx) {
            if ($trx->total_tagihan < 0) {
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
            $rekap[$week]['total_tagihan'] += $trx->total_tagihan;
        }

        $payments = \App\Models\SupplierPayment::where('supplier_id', $supplier->id)
            ->whereYear('tgl', $year)
            ->whereMonth('tgl', $month)
            ->orderBy('tgl', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('supplier_transactions.supplier_show', compact('supplier', 'transactions', 'rekap', 'month', 'year', 'hasDebt', 'payments'));
    }

    public function create(Request $request)
    {
        $supplierId = $request->query('supplier_id');

        if (!$supplierId) {
            return redirect()->route('supplier_transactions.index')->with('error', 'Silahkan pilih supplier terlebih dahulu.');
        }

        $supplier = Supplier::findOrFail($supplierId);
        $barangs = Barang::where('supplier_id', $supplierId)->orderBy('namabarang')->get();

        return view('supplier_transactions.create', compact('supplier', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tgl' => 'required|date',
            'bayar' => 'required|integer',
            'retur' => 'nullable|integer',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barangs,id',
            'details.*.jumlah' => 'required|integer|min:1',
            'details.*.subtotal' => 'required|integer',
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

            $transaction = SupplierTransaction::create([
                'supplier_id' => $request->supplier_id,
                'tgl' => $request->tgl,
                'total_barang' => 0,
                'total_uang' => 0,
                'bayar' => $request->bayar,
                'total_tagihan' => 0,
                'retur' => $request->retur ?? 0,
                'bukti_tf' => $buktiTfPath,
            ]);

            foreach ($request->details as $detail) {
                $subtotal = $detail['subtotal'];

                SupplierTransactionDetail::create([
                    'supplier_transaction_id' => $transaction->id,
                    'barang_id' => $detail['barang_id'],
                    'jumlah' => $detail['jumlah'],
                    'subtotal' => $subtotal,
                ]);

                $total_barang += $detail['jumlah'];
                $total_uang += $subtotal;
            }

            $total_tagihan = $request->bayar - $total_uang;

            $transaction->update([
                'total_barang' => $total_barang,
                'total_uang' => $total_uang,
                'total_tagihan' => $total_tagihan
            ]);

            DB::commit();

            if ($request->bayar > 0) {
                SupplierPayment::create([
                    'supplier_id' => $request->supplier_id,
                    'supplier_transaction_id' => $transaction->id,
                    'tgl' => $request->tgl,
                    'nominal' => $request->bayar,
                    'bukti_tf' => $buktiTfPath,
                    'keterangan' => 'Pembayaran Awal Transaksi',
                ]);
            }

            return redirect()->route('supplier_transactions.show_supplier', $request->supplier_id)->with('success', 'Transaksi supplier berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan transaksi: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(SupplierTransaction $supplierTransaction)
    {
        $supplierTransaction->load('details');
        $supplier = Supplier::findOrFail($supplierTransaction->supplier_id);
        $barangs = Barang::where('supplier_id', $supplier->id)->orderBy('namabarang')->get();

        return view('supplier_transactions.edit', compact('supplierTransaction', 'supplier', 'barangs'));
    }

    public function update(Request $request, SupplierTransaction $supplierTransaction)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tgl' => 'required|date',
            'bayar' => 'required|integer',
            'retur' => 'nullable|integer',
            'details' => 'required|array|min:1',
            'details.*.barang_id' => 'required|exists:barangs,id',
            'details.*.jumlah' => 'required|integer|min:1',
            'details.*.subtotal' => 'required|integer',
            'bukti_tf' => 'nullable|image|max:2048',
        ]);

        $buktiTfPath = $supplierTransaction->bukti_tf;
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

            // Delete old details
            SupplierTransactionDetail::where('supplier_transaction_id', $supplierTransaction->id)->delete();

            foreach ($request->details as $detail) {
                $subtotal = $detail['subtotal'];

                SupplierTransactionDetail::create([
                    'supplier_transaction_id' => $supplierTransaction->id,
                    'barang_id' => $detail['barang_id'],
                    'jumlah' => $detail['jumlah'],
                    'subtotal' => $subtotal,
                ]);

                $total_barang += $detail['jumlah'];
                $total_uang += $subtotal;
            }

            $total_tagihan = $request->bayar - $total_uang;

            $supplierTransaction->update([
                'tgl' => $request->tgl,
                'total_barang' => $total_barang,
                'total_uang' => $total_uang,
                'bayar' => $request->bayar,
                'total_tagihan' => $total_tagihan,
                'retur' => $request->retur ?? 0,
                'bukti_tf' => $buktiTfPath,
            ]);

            DB::commit();

            return redirect()->route('supplier_transactions.show_supplier', $supplierTransaction->supplier_id)->with('success', 'Transaksi supplier berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengubah transaksi: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(SupplierTransaction $supplierTransaction)
    {
        try {
            $supplierTransaction->delete();
            return redirect()->back()->with('success', 'Transaksi berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    public function payDebt(Request $request, Supplier $supplier)
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
        if ($supplier->hutang_awal > 0) {
            if ($nominal >= $supplier->hutang_awal) {
                $nominal -= $supplier->hutang_awal;
                $supplier->hutang_awal = 0;
            } else {
                $supplier->hutang_awal -= $nominal;
                $nominal = 0;
            }
            $supplier->save();
        }

        // 2. Jika masih ada sisa nominal, baru potong transaksi
        if ($nominal > 0) {
            $debtTransactions = SupplierTransaction::where('supplier_id', $supplier->id)
                ->where('total_tagihan', '<', 0)
                ->orderBy('tgl', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($debtTransactions as $trx) {
                if ($nominal <= 0) {
                    break;
                }

                $hutang = abs($trx->total_tagihan);

                if ($nominal >= $hutang) {
                    $trx->bayar += $hutang;
                    $trx->total_tagihan = 0;
                    $nominal -= $hutang;
                } else {
                    $trx->bayar += $nominal;
                    $trx->total_tagihan += $nominal;
                    $nominal = 0;
                }

                $trx->save();
            }
        }

        SupplierPayment::create([
            'supplier_id' => $supplier->id,
            'tgl' => $request->tgl,
            'nominal' => $nominalAsli,
            'bukti_tf' => $buktiTfPath,
            'keterangan' => 'Pelunasan Tagihan Otomatis'
        ]);

        return redirect()->back()->with('success', 'Pembayaran tagihan berhasil dicatat.');
    }

    public function updatePayment(Request $request, \App\Models\SupplierPayment $payment)
    {
        $request->validate([
            'tgl' => 'required|date',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'bukti_tf' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $supplier = Supplier::findOrFail($payment->supplier_id);
            $oldNominal = $payment->nominal;
            $newNominal = $request->nominal;

            if ($oldNominal != $newNominal) {
                // 1. REVERSE THE OLD NOMINAL
                if ($payment->supplier_transaction_id) {
                    $trx = SupplierTransaction::find($payment->supplier_transaction_id);
                    if ($trx) {
                        $trx->bayar -= $oldNominal;
                        $trx->total_tagihan -= $oldNominal;
                        $trx->save();
                    }
                } else {
                    $nominalToReverse = $oldNominal;
                    $paidTransactions = SupplierTransaction::where('supplier_id', $supplier->id)
                        ->where('bayar', '>', 0)
                        ->orderBy('tgl', 'desc')
                        ->orderBy('id', 'desc')
                        ->get();

                    foreach ($paidTransactions as $trx) {
                        if ($nominalToReverse <= 0) break;

                        if ($trx->bayar >= $nominalToReverse) {
                            $trx->bayar -= $nominalToReverse;
                            $trx->total_tagihan -= $nominalToReverse;
                            $nominalToReverse = 0;
                        } else {
                            $nominalToReverse -= $trx->bayar;
                            $trx->total_tagihan -= $trx->bayar;
                            $trx->bayar = 0;
                        }
                        $trx->save();
                    }

                    if ($nominalToReverse > 0) {
                        $supplier->hutang_awal += $nominalToReverse;
                        $supplier->save();
                    }
                }

                // 2. APPLY THE NEW NOMINAL
                if ($payment->supplier_transaction_id) {
                    $trx = SupplierTransaction::find($payment->supplier_transaction_id);
                    if ($trx) {
                        $trx->bayar += $newNominal;
                        $trx->total_tagihan += $newNominal;
                        $trx->save();
                    }
                } else {
                    $nominalToApply = $newNominal;
                    if ($supplier->hutang_awal > 0) {
                        if ($nominalToApply >= $supplier->hutang_awal) {
                            $nominalToApply -= $supplier->hutang_awal;
                            $supplier->hutang_awal = 0;
                        } else {
                            $supplier->hutang_awal -= $nominalToApply;
                            $nominalToApply = 0;
                        }
                        $supplier->save();
                    }

                    if ($nominalToApply > 0) {
                        $debtTransactions = SupplierTransaction::where('supplier_id', $supplier->id)
                            ->where('total_tagihan', '<', 0)
                            ->orderBy('tgl', 'asc')
                            ->orderBy('id', 'asc')
                            ->get();

                        foreach ($debtTransactions as $trx) {
                            if ($nominalToApply <= 0) break;

                            $hutang = abs($trx->total_tagihan);
                            if ($nominalToApply >= $hutang) {
                                $trx->bayar += $hutang;
                                $trx->total_tagihan = 0;
                                $nominalToApply -= $hutang;
                            } else {
                                $trx->bayar += $nominalToApply;
                                $trx->total_tagihan += $nominalToApply;
                                $nominalToApply = 0;
                            }
                            $trx->save();
                        }
                    }
                }
            }

            $buktiTfPath = $payment->bukti_tf;
            if ($request->hasFile('bukti_tf')) {
                if ($buktiTfPath) {
                    Storage::disk('public')->delete($buktiTfPath);
                }
                $buktiTfPath = $request->file('bukti_tf')->store('bukti_tf', 'public');
            }

            $payment->update([
                'tgl' => $request->tgl,
                'nominal' => $newNominal,
                'keterangan' => $request->keterangan ?? $payment->keterangan,
                'bukti_tf' => $buktiTfPath,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pembayaran berhasil diubah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengubah pembayaran: ' . $e->getMessage());
        }
    }

    public function destroyPayment(\App\Models\SupplierPayment $payment)
    {
        try {
            DB::beginTransaction();
            $supplier = Supplier::findOrFail($payment->supplier_id);
            $nominalToReverse = $payment->nominal;

            if ($payment->supplier_transaction_id) {
                $trx = SupplierTransaction::find($payment->supplier_transaction_id);
                if ($trx) {
                    $trx->bayar -= $nominalToReverse;
                    $trx->total_tagihan -= $nominalToReverse;
                    $trx->save();
                }
            } else {
                $paidTransactions = SupplierTransaction::where('supplier_id', $supplier->id)
                    ->where('bayar', '>', 0)
                    ->orderBy('tgl', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();

                foreach ($paidTransactions as $trx) {
                    if ($nominalToReverse <= 0) break;

                    if ($trx->bayar >= $nominalToReverse) {
                        $trx->bayar -= $nominalToReverse;
                        $trx->total_tagihan -= $nominalToReverse;
                        $nominalToReverse = 0;
                    } else {
                        $nominalToReverse -= $trx->bayar;
                        $trx->total_tagihan -= $trx->bayar;
                        $trx->bayar = 0;
                    }
                    $trx->save();
                }

                if ($nominalToReverse > 0) {
                    $supplier->hutang_awal += $nominalToReverse;
                    $supplier->save();
                }
            }

            if ($payment->bukti_tf) {
                Storage::disk('public')->delete($payment->bukti_tf);
            }
            $payment->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Pembayaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }
}
