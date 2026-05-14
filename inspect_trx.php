<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ResellerTransaction;
use App\Models\SupplierTransaction;
use App\Models\Barang;

$type = $argv[1]; // 'reseller' or 'supplier'
$id = $argv[2];

if($type == 'reseller') {
    $trx = ResellerTransaction::with('details.barang')->find($id);
} else {
    $trx = SupplierTransaction::with('details.barang')->find($id);
}

if($trx) {
    echo "Trx #$id ($type)\n";
    echo "Total Barang (cached): " . $trx->total_barang . "\n";
    foreach($trx->details as $d) {
        $unitPrice = $d->subtotal / ($d->jumlah ?: 1);
        echo "Detail ID: " . $d->id . "\n";
        echo "Barang: " . ($d->barang ? $d->barang->namabarang : 'NULL') . " (ID: " . $d->barang_id . ")\n";
        echo "Jumlah: " . $d->jumlah . "\n";
        echo "Subtotal: " . $d->subtotal . "\n";
        echo "UnitPrice: " . $unitPrice . "\n";
        if($d->barang) {
            $priceField = ($type == 'reseller' ? 'hargajual_perlusin' : 'hargabeli_perlusin');
            echo "Lusin Price: " . $d->barang->$priceField . "\n";
            $isLusin = ($d->barang->$priceField > 0 && round($unitPrice) == round($d->barang->$priceField));
            echo "Is Lusin: " . ($isLusin ? "YES" : "NO") . "\n";
        } else {
            echo "No Barang\n";
        }
    }
} else {
    echo "Not found\n";
}
