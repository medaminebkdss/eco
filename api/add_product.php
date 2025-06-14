<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'طريقة الطلب غير صحيحة'], 405);
}

// التحقق من البيانات المطلوبة
if (empty($_POST['name']) || empty($_POST['original_price']) || empty($_POST['discounted_price'])) {
    json_response(['success' => false, 'message' => 'جميع الحقول مطلوبة']);
}

$name = sanitize_input($_POST['name']);
$original_price = floatval($_POST['original_price']);
$discounted_price = floatval($_POST['discounted_price']);

// معالجة الصور
$images = [];
if (isset($_POST['images']) && is_array($_POST['images'])) {
    foreach ($_POST['images'] as $image) {
        $image = trim($image);
        if (!empty($image)) {
            $images[] = $image;
        }
    }
}

if (empty($images)) {
    json_response(['success' => false, 'message' => 'يجب إضافة صورة واحدة على الأقل']);
}

$images_json = json_encode($images, JSON_UNESCAPED_UNICODE);

// إدراج المنتج في قاعدة البيانات
$sql = "INSERT INTO products (name, original_price, discounted_price, images) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sdds", $name, $original_price, $discounted_price, $images_json);
    
    if ($stmt->execute()) {
        json_response(['success' => true, 'message' => 'تم إضافة المنتج بنجاح']);
    } else {
        json_response(['success' => false, 'message' => 'خطأ في إضافة المنتج: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    json_response(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
}

$conn->close();
?>
