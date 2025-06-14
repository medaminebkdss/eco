<?php
// إعدادات قاعدة البيانات لـ InfinityFree
$host = 'sql100.infinityfree.com'; // استبدل بالمضيف الصحيح
$username = 'if0_39220452'; // استبدل باسم المستخدم الخاص بك
$password = 'rJmfZPBshfzscZ'; // استبدل بكلمة المرور الخاصة بك
$database = 'if0_39220452_eco'; // استبدل باسم قاعدة البيانات

// إنشاء الاتصال
$conn = new mysqli($host, $username, $password, $database);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// تعيين ترميز UTF-8
$conn->set_charset("utf8mb4");

// دالة مساعدة لتنظيف البيانات
function sanitize_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// دالة مساعدة لإرجاع JSON
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
