<?php
session_start();
require_once 'db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    json_response(['success' => false, 'message' => 'غير مصرح لك بالوصول'], 401);
}

$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

json_response(['success' => true, 'orders' => $orders]);

$conn->close();
?>
