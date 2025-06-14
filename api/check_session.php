<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    json_response(['success' => true, 'logged_in' => true, 'username' => $_SESSION['admin_username']]);
} else {
    json_response(['success' => false, 'logged_in' => false, 'message' => 'غير مسجل الدخول']);
}
?>
