<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$db_prefix = config('database.connections.mysql.prefix', 'pp_');
$brand_id = "6657227357";

$result = getData($db_prefix . 'currency', 'WHERE brand_id ="'.$brand_id.'" ORDER BY 1 DESC');

echo "Result length: " . strlen($result) . "\n";
$decoded = json_decode($result, true);
echo "Status: " . ($decoded['status'] ? 'true' : 'false') . "\n";
echo "Response count: " . count($decoded['response']) . "\n";

