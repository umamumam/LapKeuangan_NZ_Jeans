<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Barang;
$b = Barang::where('namabarang', 'like', '%Cutbray Alice%')->first();
if($b) {
    echo "Product: " . $b->namabarang . "\n";
    echo "Hargabeli Potong: " . $b->hargabeli_perpotong . "\n";
    echo "Hargabeli Lusin: " . $b->hargabeli_perlusin . "\n";
    echo "Hargajual Potong: " . $b->hargajual_perpotong . "\n";
    echo "Hargajual Lusin: " . $b->hargajual_perlusin . "\n";
} else {
    echo "Not found\n";
}
