<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/gateways/create-bank', 'GET');
$response = $kernel->handle($request);
echo "RESPONSE STATUS: " . $response->getStatusCode() . "\n";
file_put_contents('test_getData_output.html', $response->getContent());
echo "Wrote to test_getData_output.html. Searching for currency dropdown content...\n";
