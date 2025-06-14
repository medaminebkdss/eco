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

if (empty($_POST['product_id'])) {
    json_response(['success' => false, 'message' => 'معرف المنتج مطلوب']);
}

$product_id = intval($_POST['product_id']);

// حذف المنتج
$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            json_response(['success' => true, 'message' => 'تم حذف المنتج بنجاح']);
        } else {
            json_response(['success' => false, 'message' => 'المنتج غير موجود']);
        }
    } else {
        json_response(['success' => false, 'message' => 'خطأ في حذف المنتج: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    json_response(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
}

$conn->close();
?>

