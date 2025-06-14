<?php
require_once 'db.php';

// هذا الملف لا يتطلب تسجيل دخول
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT wilaya_name, wilaya_code, shipping_cost, delivery_time FROM shipping_rates ORDER BY wilaya_code ASC";
$result = $conn->query($sql);

$shipping_rates = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $shipping_rates[] = [
            'wilaya_name' => $row['wilaya_name'],
            'wilaya_code' => $row['wilaya_code'],
            'shipping_cost' => floatval($row['shipping_cost']),
            'delivery_time' => $row['delivery_time']
        ];
    }
}

// التأكد من إرجاع JSON صالح حتى لو كانت النتائج فارغة
json_response(['success' => true, 'shipping_rates' => $shipping_rates]);

$conn->close();
?>