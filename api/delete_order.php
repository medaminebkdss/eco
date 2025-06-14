<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    json_response(['success' => false, 'message' => 'غير مصرح لك بالوصول'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'طريقة الطلب غير صحيحة'], 405);
}

if (empty($_POST['order_id'])) {
    json_response(['success' => false, 'message' => 'معرف الطلب مطلوب']);
}

$order_id = intval($_POST['order_id']);

// حذف الطلب
$sql = "DELETE FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            json_response(['success' => true, 'message' => 'تم حذف الطلب بنجاح']);
        } else {
            json_response(['success' => false, 'message' => 'الطلب غير موجود']);
        }
    } else {
        json_response(['success' => false, 'message' => 'خطأ في حذف الطلب: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    json_response(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
}

$conn->close();
?>