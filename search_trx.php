<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ResellerTransaction;

$trxs = ResellerTransaction::orderBy('id', 'desc')->take(10)->get();
foreach($trxs as $t) {
    echo "ID: {$t->id}, Total: {$t->total_uang}, Barang: {$t->total_barang}, Tgl: {$t->tgl}\n";
}
