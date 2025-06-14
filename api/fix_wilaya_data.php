<?php
require_once 'api/db.php';

// إصلاح الطلبيات التي لا تحتوي على اسم الولاية
$sql = "SELECT o.id, o.wilaya, sr.wilaya_name 
        FROM orders o 
        LEFT JOIN shipping_rates sr ON o.wilaya = sr.wilaya_code 
        WHERE (o.wilaya IS NULL OR o.wilaya = '' OR o.wilaya = '0')";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['wilaya_name'])) {
            $update_sql = "UPDATE orders SET wilaya = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $row['wilaya_name'], $row['id']);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
    echo "تم إصلاح البيانات بنجاح";
} else {
    echo "لا توجد بيانات تحتاج إصلاح";
}

$conn->close();
?>
