<?php
// السماح بالوصول العام لأن الزبائن أيضاً سيشاهدون المنتجات
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // استعلام لجلب بيانات المنتج مع اسم الصنف التابع له
    // نستخدم JOIN لربط جدول المنتجات بجدول الأصناف
    $query = "SELECT 
                p.id, 
                p.name, 
                p.description, 
                p.price, 
                p.image_url, 
                p.category_id,
                c.name as category_name 
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // إرسال البيانات بتنسيق JSON
    http_response_code(200);
    echo json_encode($products);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "خطأ في جلب المنتجات: " . $e->getMessage()]);
}
?>