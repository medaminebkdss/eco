<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

// --- 1. VALIDATE REQUEST METHOD ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'طريقة الطلب غير صحيحة'], 405);
}

// --- 2. VALIDATE REQUIRED FIELDS (address is now required) ---
$required_fields = ['customer_name', 'phone', 'address', 'product_id', 'wilaya_name'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        json_response(['success' => false, 'message' => "خطأ: حقل '{$field}' مطلوب."], 400);
    }
}

// --- 3. SANITIZE AND PREPARE DATA ---
$customer_name = sanitize_input($_POST['customer_name']);
$phone = sanitize_input($_POST['phone']);
// THE FIX: Get the address from the form
$address = sanitize_input($_POST['address']); 
$notes = isset($_POST['notes']) ? sanitize_input($_POST['notes']) : '';
$product_id = intval($_POST['product_id']);
$wilaya_name = sanitize_input($_POST['wilaya_name']);
$shipping_cost = floatval($_POST['shipping_cost'] ?? 0);
$total_cost = floatval($_POST['total_cost'] ?? 0);


// --- 4. GET PRODUCT INFO FROM DATABASE ---
$product_sql = "SELECT name, discounted_price FROM products WHERE id = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows === 0) {
    json_response(['success' => false, 'message' => 'المنتج المحدد غير موجود']);
}
$product = $product_result->fetch_assoc();
$product_stmt->close();


// --- 5. INSERT THE ORDER INTO THE DATABASE ---
// THE FIX: Added `address` to the INSERT query.
$sql = "INSERT INTO orders (customer_name, phone, address, notes, product_id, product_name, product_price, wilaya, shipping_cost, total_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // THE FIX: Bind the new 'address' variable. The type string 's' is added.
    $stmt->bind_param(
        "ssssisdsdd",
        $customer_name,
        $phone,
        $address, // Variable is now included here
        $notes,
        $product_id,
        $product['name'],
        $product['discounted_price'],
        $wilaya_name,
        $shipping_cost,
        $total_cost
    );
    
    if ($stmt->execute()) {
        json_response(['success' => true, 'message' => 'تم إرسال طلبكم بنجاح!']);
    } else {
        json_response(['success' => false, 'message' => 'خطأ في قاعدة البيانات عند حفظ الطلب: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    json_response(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
}

$conn->close();
?>