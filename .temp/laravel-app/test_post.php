<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$request = Illuminate\Http\Request::create('/admin/dashboard', 'POST', [
    'action' => 'customers-info-byID',
    'ItemID' => 1
]);

$controller = app(\App\Http\Controllers\Admin\NativeAdminActionController::class);
$response = $controller->handle($request);

echo "RESPONSE STATUS: " . $response->getStatusCode() . "\n";
echo "OUTPUT: " . $response->getContent() . "\n";
