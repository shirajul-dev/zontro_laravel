<?php
// 1. Get initial session
$ch1 = curl_init('http://127.0.0.1:8000/admin/dashboard');
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_HEADER, true);
$resp1 = curl_exec($ch1);
curl_close($ch1);

preg_match('/laravel-session=([^;]+)/', $resp1, $m1);
$sessionCookie = $m1[1];

preg_match('/name="csrf_token_default" value="([^"]+)"/', $resp1, $m2);
$token = $m2[1];

echo "Token: $token\n";

// 2. Mock AJAX POST with same token
$ch2 = curl_init('http://127.0.0.1:8000/admin/dashboard');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query([
    'action' => 'customers-info-byID',
    'ItemID' => '1',
    'csrf_token' => $token
]));
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest',
    'Cookie: laravel-session=' . $sessionCookie
]);
$resp2 = curl_exec($ch2);
curl_close($ch2);

echo "Ajax Response: $resp2\n";
