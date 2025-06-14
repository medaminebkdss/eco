<?php
session_start();
require_once 'db.php';

// -- الذكاء الاصطناعي للملف: تحديد ما إذا كان الطلب يريد JSON أم HTML --
$is_json_request = (isset($_GET['format']) && $_GET['format'] === 'json') || (isset($_GET['id']));

if ($is_json_request) {
    header('Content-Type: application/json; charset=utf-8');
    
    // --- الوضع 1: طلب منتج واحد بواسطة ID (لصفحة product.html) ---
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $product_id = intval($_GET['id']);
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $product['images'] = json_decode($product['images'], true) ?: [];
            json_response(['success' => true, 'product' => $product]);
        } else {
            json_response(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    // --- الوضع 2: طلب كل المنتجات بصيغة JSON (للوحة التحكم) ---
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            json_response(['success' => false, 'message' => 'غير مصرح لك بالوصول'], 401);
            exit;
        }
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $row['images'] = json_decode($row['images'], true) ?: [];
                $products[] = $row;
            }
        }
        json_response(['success' => true, 'products' => $products]);
        $conn->close();
        exit;
    }
}


// --- الوضع 3 (الافتراضي): طلب كل المنتجات بصيغة HTML (للصفحة الرئيسية index.html) ---
header('Content-Type: text/html; charset=utf-8');
$sql_all = "SELECT * FROM products ORDER BY created_at DESC";
$result_all = $conn->query($sql_all);

$html = '';
if ($result_all && $result_all->num_rows > 0) {
    while ($row = $result_all->fetch_assoc()) {
        $images_array = json_decode($row['images'], true) ?: [];
        $main_image = !empty($images_array) ? $images_array[0] : 'https://placehold.co/300x200?text=No+Image';
        
        // ** THE FIX IS HERE: Re-added the prices block **
        $html .= '
        <div class="product-card">
            <a href="product.html?id=' . $row['id'] . '" class="product-link">
                <div class="product-image">
                    <img src="' . htmlspecialchars($main_image) . '" alt="' . htmlspecialchars($row['name']) . '">
                </div>
                <div class="product-info">
                    <h3 class="product-name">' . htmlspecialchars($row['name']) . '</h3>
                    <div class="product-prices">
                        ' . ($row['original_price'] != $row['discounted_price'] ? 
                            '<span class="original-price">' . number_format($row['original_price'], 0) . ' دج</span>' : '') . '
                        <span class="discounted-price">' . number_format($row['discounted_price'], 0) . ' دج</span>
                    </div>
                </div>
            </a>
            <div class="product-action">
                <a href="product.html?id=' . $row['id'] . '" class="btn-view">
                    أطلب الآن
                </a>
            </div>
        </div>';
    }
} else {
    $html = '<div class="no-products">لا توجد منتجات متاحة حالياً</div>';
}
echo $html;
$conn->close();
?>