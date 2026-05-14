<?php

use App\Models\ResellerTransaction;
use App\Models\SupplierTransaction;
use App\Models\Barang;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing Reseller Transactions...\n";
$resellerTrxs = ResellerTransaction::with('details.barang')->get();
foreach ($resellerTrxs as $trx) {
    $total_barang = 0;
    foreach ($trx->details as $detail) {
        $unitPrice = $detail->subtotal / ($detail->jumlah ?: 1);
        $barang = $detail->barang;
        if ($barang && $barang->hargajual_perlusin > 0 && round($unitPrice) == round($barang->hargajual_perlusin)) {
            $total_barang += $detail->jumlah * 12;
        } else {
            $total_barang += $detail->jumlah;
        }
    }
    if ($trx->total_barang != $total_barang) {
        echo "Updating Reseller Trx #{$trx->id}: {$trx->total_barang} -> {$total_barang}\n";
        $trx->total_barang = $total_barang;
        $trx->save();
    }
}

echo "Fixing Supplier Transactions...\n";
$supplierTrxs = SupplierTransaction::with('details.barang')->get();
foreach ($supplierTrxs as $trx) {
    $total_barang = 0;
    foreach ($trx->details as $detail) {
        $unitPrice = $detail->subtotal / ($detail->jumlah ?: 1);
        $barang = $detail->barang;
        if ($barang && $barang->hargabeli_perlusin > 0 && round($unitPrice) == round($barang->hargabeli_perlusin)) {
            $total_barang += $detail->jumlah * 12;
        } else {
            $total_barang += $detail->jumlah;
        }
    }
    if ($trx->total_barang != $total_barang) {
        echo "Updating Supplier Trx #{$trx->id}: {$trx->total_barang} -> {$total_barang}\n";
        $trx->total_barang = $total_barang;
        $trx->save();
    }
}

echo "Done!\n";
