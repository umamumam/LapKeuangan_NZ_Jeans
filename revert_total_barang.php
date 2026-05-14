<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ResellerTransaction;
use App\Models\SupplierTransaction;

echo "Fixing Reseller Transactions back to simple sum...\n";
$resellerTrxs = ResellerTransaction::with('details')->get();
foreach ($resellerTrxs as $trx) {
    $sum = $trx->details->sum('jumlah');
    if ($trx->total_barang != $sum) {
        echo "Updating Reseller Trx #{$trx->id}: {$trx->total_barang} -> {$sum}\n";
        $trx->total_barang = $sum;
        $trx->save();
    }
}

echo "Fixing Supplier Transactions back to simple sum...\n";
$supplierTrxs = SupplierTransaction::with('details')->get();
foreach ($supplierTrxs as $trx) {
    $sum = $trx->details->sum('jumlah');
    if ($trx->total_barang != $sum) {
        echo "Updating Supplier Trx #{$trx->id}: {$trx->total_barang} -> {$sum}\n";
        $trx->total_barang = $sum;
        $trx->save();
    }
}

echo "Done!\n";
