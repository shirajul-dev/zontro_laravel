<?php
$legacyRoot = realpath(base_path('..'));

if ($legacyRoot === false) {
    http_response_code(500);
    echo 'Legacy root path resolution failed.';
    return;
}

$legacyIndex = $legacyRoot . '/index.php';

if (!file_exists($legacyIndex)) {
    http_response_code(404);
    echo 'Legacy index.php not found.';
    return;
}

$_GET['page'] = $legacyPage ?? '';

$req = request();

$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? $req->method();
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? $req->getRequestUri();
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? $req->getHost();
$_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? (string) $req->getPort();
$_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? ($req->isSecure() ? 'on' : 'off');
$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? ($req->ip() ?? '127.0.0.1');

require $legacyIndex;
