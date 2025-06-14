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

// التحقق من البيانات المطلوبة
if (empty($_POST['product_id']) || empty($_POST['name']) || empty($_POST['original_price']) || empty($_POST['discounted_price'])) {
    json_response(['success' => false, 'message' => 'جميع الحقول مطلوبة']);
}

$product_id = intval($_POST['product_id']);
$name = sanitize_input($_POST['name']);
$original_price = floatval($_POST['original_price']);
$discounted_price = floatval($_POST['discounted_price']);

// التحقق من صحة الأسعار
if ($original_price < 0 || $discounted_price < 0) {
    json_response(['success' => false, 'message' => 'الأسعار يجب أن تكون أرقام موجبة']);
}

if ($discounted_price > $original_price) {
    json_response(['success' => false, 'message' => 'السعر بعد التخفيض لا يمكن أن يكون أكبر من السعر الأصلي']);
}

// معالجة الصور
$images = [];
if (isset($_POST['images']) && is_array($_POST['images'])) {
    foreach ($_POST['images'] as $image) {
        $image = trim($image);
        if (!empty($image)) {
            // التحقق من صحة رابط الصورة
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                $images[] = $image;
            }
        }
    }
}

if (empty($images)) {
    json_response(['success' => false, 'message' => 'يجب إضافة صورة واحدة صالحة على الأقل']);
}

$images_json = json_encode($images, JSON_UNESCAPED_UNICODE);

// التحقق من وجود المنتج أولاً
$check_sql = "SELECT id FROM products WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $product_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    json_response(['success' => false, 'message' => 'المنتج المطلوب تحديثه غير موجود']);
}
$check_stmt->close();

// تحديث المنتج في قاعدة البيانات
$sql = "UPDATE products SET name = ?, original_price = ?, discounted_price = ?, images = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sddsi", $name, $original_price, $discounted_price, $images_json, $product_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            json_response(['success' => true, 'message' => 'تم تحديث المنتج بنجاح']);
        } else {
            json_response(['success' => false, 'message' => 'لم يتم تغيير أي بيانات للمنتج']);
        }
    } else {
        json_response(['success' => false, 'message' => 'خطأ في تحديث المنتج: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    json_response(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
}

$conn->close();
?>

