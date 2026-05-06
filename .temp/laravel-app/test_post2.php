<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/brands', 'POST', ['action' => 'create-new-brand', 'brand-name' => 'testbrand3']);
$response = $kernel->handle($request);
echo "RESPONSE STATUS: " . $response->getStatusCode() . "\n";
echo "RESPONSE IS: " . $response->getContent() . "\n";
