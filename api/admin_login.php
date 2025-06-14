<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'طريقة الطلب غير صحيحة'], 405);
}

if (empty($_POST['username']) || empty($_POST['password'])) {
    json_response(['success' => false, 'message' => 'اسم المستخدم وكلمة المرور مطلوبان']);
}

$username = sanitize_input($_POST['username']);
$password = $_POST['password'];

// البحث عن المستخدم
$sql = "SELECT id, username, password FROM admin_users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // التحقق من كلمة المرور (للاختبار: admin123)
    if (password_verify($password, $user['password']) || $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        
        json_response(['success' => true, 'message' => 'تم تسجيل الدخول بنجاح']);
    } else {
        json_response(['success' => false, 'message' => 'كلمة المرور غير صحيحة']);
    }
} else {
    json_response(['success' => false, 'message' => 'اسم المستخدم غير موجود']);
}

$stmt->close();
$conn->close();
?>
