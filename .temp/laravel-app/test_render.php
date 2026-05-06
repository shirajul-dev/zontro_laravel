<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/gateways/create-bank', 'GET');

// mock auth
\Illuminate\Support\Facades\Auth::shouldReceive('guard')->andReturn(
    \Mockery::mock(['check' => true, 'id' => 1])
);

// mock the native admin logic
$controller = app(\App\Http\Controllers\Admin\NativeAdminPageController::class);

$reflection = new \ReflectionClass(\App\Http\Controllers\Admin\NativeAdminPageController::class);
$method = $reflection->getMethod('viewData');
$method->setAccessible(true);
$viewData = $method->invoke($controller, $request);

$viewName = 'legacy.pp-content.pp-admin.pp-root.gateways.create-bank';
if (view()->exists($viewName)) {
    echo view($viewName, $viewData)->render();
} elseif (view()->exists($viewName.'.index')) {
    echo view($viewName.'.index', $viewData)->render();
}

