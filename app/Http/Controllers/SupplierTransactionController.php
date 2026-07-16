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
            ->withSum('payments', 'nominal')
            ->withSum(['transactions' => function($q) {
                $q->where('is_retur', false);
            }], 'total_uang')
            ->withSum(['transactions as returns_sum_total_uang' => function($q) {
                $q->where('is_retur', true);
            }], 'total_uang')
            ->withMax('transactions', 'updated_at')
            ->withMax('payments', 'updated_at')
            ->orderBy('nama')
            ->get();
        // $suppliers = Supplier::with(['barangs'])->orderBy('nama')->get();

        $allTransactions = SupplierTransaction::with('details.barang')
            ->whereYear('tgl', $year)
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

            if ($trx->is_retur) {
                $rekapGlobal[$week]['total_tagihan'] += $trx->total_tagihan;
            } else {
                $rekapGlobal[$week]['total_uang'] += $trx->total_uang;
                $rekapGlobal[$week]['bayar'] += $trx->bayar;
                $rekapGlobal[$week]['total_tagihan'] += $trx->total_tagihan;
            }
        }

        foreach ($suppliers as $supplier) {
            $trx = $allTransactions->where('supplier_id', $supplier->id);
            $supplier->total_uang = $trx->where('is_retur', false)->sum('total_uang');
            $supplier->bayar = $trx->sum('bayar');
            
            $supplier->total_lusin = 0;
            $supplier->total_potong = 0;
            foreach($trx->where('is_retur', false) as $t) {
                foreach($t->details as $d) {
                    $unitPrice = $d->subtotal / ($d->jumlah ?: 1);
                    $isLusin = ($d->barang && $d->barang->hargabeli_perlusin > 0 && round($unitPrice) == round($d->barang->hargabeli_perlusin));
                    if($isLusin) $supplier->total_lusin += $d->jumlah;
                    else $supplier->total_potong += $d->jumlah;
                }
            }
            
            // Global balance using ledger formula: Total Payments - Total Costs + Total Returns - Initial Debt
            $supplier->total_tagihan = ($supplier->payments_sum_nominal ?? 0) - ($supplier->transactions_sum_total_uang ?? 0) + ($supplier->returns_sum_total_uang ?? 0) - $supplier->hutang_awal;
        }

        // Orang yang Sisa/Kurang < 0 (berhutang/tagihan) atau memiliki transaksi/pembayaran di bulan ini
        $suppliersWithDebt = $suppliers->filter(function ($r) {
            return $r->total_uang > 0 || $r->bayar > 0 || $r->total_tagihan < 0;
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
            if (!$trx->is_retur && $trx->total_tagihan < 0) {
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

            if ($trx->is_retur) {
                $rekap[$week]['total_tagihan'] += $trx->total_tagihan;
            } else {
                $rekap[$week]['total_uang'] += $trx->total_uang;
                $rekap[$week]['bayar'] += $trx->bayar;
                $rekap[$week]['total_tagihan'] += $trx->total_tagihan;
            }
        }

        $payments = \App\Models\SupplierPayment::where('supplier_id', $supplier->id)
            ->whereYear('tgl', $year)
            ->whereMonth('tgl', $month)
            ->orderBy('tgl', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $globalBalance = $supplier->payments()->sum('nominal') - $supplier->transactions()->where('is_retur', false)->sum('total_uang') + $supplier->transactions()->where('is_retur', true)->sum('total_uang') - $supplier->hutang_awal;

        return view('supplier_transactions.supplier_show', compact('supplier', 'transactions', 'rekap', 'month', 'year', 'hasDebt', 'payments', 'globalBalance'));
    }

    public function create(Request $request)
    {
        $supplierId = $request->query('supplier_id');
        $is_retur = $request->query('is_retur', 0);

        if (!$supplierId) {
            return redirect()->route('supplier_transactions.index')->with('error', 'Silahkan pilih supplier terlebih dahulu.');
        }

        $supplier = Supplier::findOrFail($supplierId);
        $barangs = Barang::where('supplier_id', $supplierId)->orderBy('namabarang')->get();

        return view('supplier_transactions.create', compact('supplier', 'barangs', 'is_retur'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tgl' => 'required|date',
            'bayar' => 'required|integer',
            'retur' => 'nullable|integer',
            'is_retur' => 'nullable|boolean',
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
                'is_retur' => $request->is_retur ?? false,
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

            $total_tagihan = ($request->is_retur ?? false) ? $total_uang : ($request->bayar - $total_uang);

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
        $is_retur = $supplierTransaction->is_retur;

        return view('supplier_transactions.edit', compact('supplierTransaction', 'supplier', 'barangs', 'is_retur'));
    }

    public function update(Request $request, SupplierTransaction $supplierTransaction)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tgl' => 'required|date',
            'bayar' => 'required|integer',
            'retur' => 'nullable|integer',
            'is_retur' => 'nullable|boolean',
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

            $total_tagihan = ($request->is_retur ?? false) ? $total_uang : ($request->bayar - $total_uang);

            $supplierTransaction->update([
                'tgl' => $request->tgl,
                'total_barang' => $total_barang,
                'total_uang' => $total_uang,
                'bayar' => $request->bayar,
                'total_tagihan' => $total_tagihan,
                'retur' => $request->retur ?? 0,
                'is_retur' => $request->is_retur ?? false,
                'bukti_tf' => $buktiTfPath,
            ]);

            // Update or create payment record for the ledger
            $payment = SupplierPayment::where('supplier_transaction_id', $supplierTransaction->id)->first();
            if ($request->bayar > 0) {
                if ($payment) {
                    $payment->update([
                        'tgl' => $request->tgl,
                        'nominal' => $request->bayar,
                        'bukti_tf' => $buktiTfPath,
                    ]);
                } else {
                    SupplierPayment::create([
                        'supplier_id' => $request->supplier_id,
                        'supplier_transaction_id' => $supplierTransaction->id,
                        'tgl' => $request->tgl,
                        'nominal' => $request->bayar,
                        'bukti_tf' => $buktiTfPath,
                        'keterangan' => 'Pembayaran Awal Transaksi',
                    ]);
                }
            } else {
                if ($payment) {
                    $payment->delete();
                }
            }

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
            DB::beginTransaction();

            // Delete associated details first
            SupplierTransactionDetail::where('supplier_transaction_id', $supplierTransaction->id)->delete();

            // Delete associated payment if it exists
            $payment = SupplierPayment::where('supplier_transaction_id', $supplierTransaction->id)->first();
            if ($payment) {
                if ($payment->bukti_tf) {
                    Storage::disk('public')->delete($payment->bukti_tf);
                }
                $payment->delete();
            }

            // Delete the transaction itself
            if ($supplierTransaction->bukti_tf) {
                Storage::disk('public')->delete($supplierTransaction->bukti_tf);
            }
            $supplierTransaction->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
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

        // 2. Jika masih ada sisa nominal, baru potong transaksi
        if ($nominal > 0) {
            $allTransactions = SupplierTransaction::where('supplier_id', $supplier->id)
                ->orderBy('tgl', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            if ($allTransactions->isNotEmpty()) {
                // First, pay all debts
                foreach ($allTransactions as $trx) {
                    if ($nominal <= 0) break;
                    if ($trx->total_tagihan < 0) {
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

                // If still nominal left, add surplus to the LATEST transaction
                if ($nominal > 0) {
                    $latestTrx = $allTransactions->sortByDesc('tgl')->sortByDesc('id')->first();
                    $latestTrx->bayar += $nominal;
                    $latestTrx->total_tagihan += $nominal;
                    $latestTrx->save();
                    $nominal = 0;
                }
            } else {
                // No transactions, just save payment. Balance will be updated automatically by formula.
                $nominal = 0;
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
                        // Just stop here. Balance will be updated by formula because payment record is deleted/nominal changed.
                        $nominalToReverse = 0;
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
                    $allTransactions = SupplierTransaction::where('supplier_id', $supplier->id)
                        ->orderBy('tgl', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                        if ($allTransactions->isNotEmpty()) {
                            // Pay all debts first
                            foreach ($allTransactions as $trx) {
                                if ($nominalToApply <= 0) break;
                                if ($trx->total_tagihan < 0) {
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

                            // Surplus to latest transaction
                            if ($nominalToApply > 0) {
                                $latestTrx = $allTransactions->sortByDesc('tgl')->sortByDesc('id')->first();
                                $latestTrx->bayar += $nominalToApply;
                                $latestTrx->total_tagihan += $nominalToApply;
                                $latestTrx->save();
                                $nominalToApply = 0;
                            }
                        } else {
                            // No transactions, balance handled by ledger
                            $nominalToApply = 0;
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
                    // No more transactions to reverse from. Balance is handled by ledger.
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

    public function invoice(Request $request, Supplier $supplier)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        // 1. Calculate Previous Balance
        $allSalesBefore = SupplierTransaction::where('supplier_id', $supplier->id)
            ->where('is_retur', false)
            ->where('tgl', '<', $startDate)
            ->sum('total_uang');

        $allReturnsBefore = SupplierTransaction::where('supplier_id', $supplier->id)
            ->where('is_retur', true)
            ->where('tgl', '<', $startDate)
            ->sum('total_uang');

        $allPaymentsBefore = SupplierPayment::where('supplier_id', $supplier->id)
            ->where('tgl', '<', $startDate)
            ->sum('nominal');

        $prevBalance = $supplier->hutang_awal + $allSalesBefore - $allReturnsBefore - $allPaymentsBefore;

        // 2. Fetch Line Items (Sales)
        $details = SupplierTransactionDetail::join('supplier_transactions', 'supplier_transaction_details.supplier_transaction_id', '=', 'supplier_transactions.id')
            ->join('barangs', 'supplier_transaction_details.barang_id', '=', 'barangs.id')
            ->where('supplier_transactions.supplier_id', $supplier->id)
            ->whereBetween('supplier_transactions.tgl', [$startDate, $endDate])
            ->select(
                'supplier_transactions.tgl',
                'barangs.namabarang',
                'barangs.ukuran',
                'barangs.hargabeli_perlusin',
                'supplier_transaction_details.jumlah',
                'supplier_transaction_details.subtotal',
                'supplier_transactions.is_retur',
                DB::raw("'sale' as type")
            )
            ->get();

        // 3. Fetch Payments
        $payments = SupplierPayment::where('supplier_id', $supplier->id)
            ->whereBetween('tgl', [$startDate, $endDate])
            ->select(
                'tgl',
                'nominal as subtotal',
                DB::raw("'payment' as type")
            )
            ->get();

        // 4. Merge and Group by Date
        $merged = $details->concat($payments);
        
        $grouped = $merged->groupBy(function($item) {
            return date('Y-m-d', strtotime($item->tgl));
        })->sortKeys();

        $items = [];
        foreach ($grouped as $date => $dayItems) {
            $sales = $dayItems->where('type', 'sale')->values();
            $pay = $dayItems->where('type', 'payment')->values();

            $normalSales = $sales->where('is_retur', false);
            $returnSales = $sales->where('is_retur', true);

            $items[] = (object)[
                'tgl' => $date,
                'sales_details' => $sales,
                'payments' => $pay,
                'tagihan' => $normalSales->sum('subtotal'),
                'retur' => $returnSales->sum('subtotal'),
                'bayar' => $pay->sum('subtotal'),
            ];
        }

        return view('supplier_transactions.invoice', compact('supplier', 'items', 'prevBalance', 'startDate', 'endDate'));
    }
}
