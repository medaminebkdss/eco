<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

// الخطوة 1: التحقق من أن المستخدم هو المدير
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    json_response(['success' => false, 'message' => 'غير مصرح لك بالوصول'], 401);
}

// الخطوة 2: التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'طريقة الطلب غير صحيحة'], 405);
}

// الخطوة 3: التحقق من وجود البيانات المطلوبة
if (empty($_POST['wilaya_code']) || !isset($_POST['shipping_cost']) || !isset($_POST['delivery_time'])) {
    json_response(['success' => false, 'message' => 'بيانات ناقصة: لم يتم إرسال كل الحقول المطلوبة.']);
}

// الخطوة 4: تنظيف البيانات
$wilaya_code = sanitize_input($_POST['wilaya_code']);
$shipping_cost = floatval($_POST['shipping_cost']);
$delivery_time = sanitize_input($_POST['delivery_time']);

// التحقق من صحة السعر
if ($shipping_cost < 0) {
    json_response(['success' => false, 'message' => 'سعر الشحن لا يمكن أن يكون سالبًا']);
}

// الخطوة 5: إعداد وتنفيذ استعلام التحديث
// تأكد من أن أسماء الأعمدة في قاعدة البيانات هي 'shipping_cost', 'delivery_time', 'wilaya_code'
$sql = "UPDATE shipping_rates SET shipping_cost = ?, delivery_time = ? WHERE wilaya_code = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // d for double (float), s for string, s for string
    $stmt->bind_param("dss", $shipping_cost, $delivery_time, $wilaya_code);
    
    if ($stmt->execute()) {
        // التحقق مما إذا كان قد تم تحديث أي صف بالفعل
        if ($stmt->affected_rows > 0) {
            json_response(['success' => true, 'message' => 'تم تحديث السعر بنجاح']);
        } else {
            // لم يتم العثور على الولاية أو أن البيانات لم تتغير
            json_response(['success' => false, 'message' => 'لم يتم تغيير أي بيانات (قد تكون القيم المدخلة مطابقة للقيم الحالية)']);
        }
    } else {
        json_response(['success' => false, 'message' => 'خطأ في تنفيذ التحديث: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    json_response(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
}

$conn->close();
?>