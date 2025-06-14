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

if (empty($_POST['order_id']) || empty($_POST['status'])) {
    json_response(['success' => false, 'message' => 'معرف الطلب والحالة مطلوبان']);
}

$order_id = intval($_POST['order_id']);
$status = sanitize_input($_POST['status']);

// قائمة بالحالات المسموح بها للأمان
$allowed_statuses = ['جديد', 'قيد المعالجة', 'تم الشحن', 'تم التسليم', 'ملغى'];
if (!in_array($status, $allowed_statuses)) {
    json_response(['success' => false, 'message' => 'حالة غير صالحة']);
}

$sql = "UPDATE orders SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            json_response(['success' => true, 'message' => 'تم تحديث حالة الطلب بنجاح']);
        } else {
            // قد لا يتغير شيء إذا كانت الحالة المحددة هي نفسها الحالية
            json_response(['success' => false, 'message' => 'لم يتم تغيير أي بيانات']);
        }
    } else {
        json_response(['success' => false, 'message' => 'خطأ في تحديث الحالة: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    json_response(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
}

$conn->close();
?>