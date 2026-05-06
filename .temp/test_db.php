<?php
require "pp-config.php";
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$id = "0034317253";
$res = mysqli_query($conn, "SELECT gateway_id, brand_id, slug, name, tab FROM pp_gateways WHERE gateway_id = '$id'");
$data = mysqli_fetch_all($res, MYSQLI_ASSOC);

echo "<h1>Gateway Info</h1>";
echo "<pre>";
print_r($data);
echo "</pre>";

$res2 = mysqli_query($conn, "SELECT brand_id, identify_name FROM pp_brands");
$brands = mysqli_fetch_all($res2, MYSQLI_ASSOC);
echo "<h1>All Brands</h1>";
echo "<pre>";
print_r($brands);
echo "</pre>";
