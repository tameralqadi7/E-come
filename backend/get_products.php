<?php
// 1. منع تداخل أي أخطاء نصية مع بيانات JSON
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. حل ذكي لمشكلة المسارات (يبحث في config أو المجلد الحالي)
$base_dir = __DIR__;
$db_path = $base_dir . '/Database.php';

if (!file_exists($db_path)) {
    $db_path = $base_dir . '/Database.php';
}

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "Database.php not found in backend or backend/config"]));
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("الاتصال بقاعدة البيانات مقطوع");
    }

    // 3. جلب المنتجات مع اسم الصنف الخاص بها
    $query = "SELECT p.id, p.name, p.description, p.price, p.image_url, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. إرسال البيانات (حتى لو كانت المصفوفة فارغة [])
    echo json_encode($products);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "خطأ: " . $e->getMessage()
    ]);
}
?>