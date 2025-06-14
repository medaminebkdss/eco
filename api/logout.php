<?php
session_start();
require_once 'db.php';

// تدمير الجلسة
session_unset();
session_destroy();

json_response(['success' => true, 'message' => 'تم تسجيل الخروج بنجاح']);
?>
