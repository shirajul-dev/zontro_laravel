<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$brand = \DB::table('pp_brands')->first();
echo "DEFAULT BRAND ID: " . ($brand ? $brand->brand_id : 'NULL') . "\n";
echo "CURRENCY COUNT GLOBALLY: " . \DB::table('pp_currency')->count() . "\n";
if ($brand) {
    echo "CURRENCY FOR BRAND: " . \DB::table('pp_currency')->where('brand_id', $brand->brand_id)->count() . "\n";
}
