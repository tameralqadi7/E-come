<?php
// 1. تفعيل الأخطاء لنعرف إذا كان الـ require هو سبب الـ 500
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. استخدام المسار المطلق لضمان العثور على ملف Database.php
// __DIR__ تجعل السيرفر يبحث داخل مجلد backend الحالي
$db_file = __DIR__ . '/config/Database.php';

if (file_exists($db_file)) {
    require_once $db_file;
} else {
    // إذا لم يجد الملف، سيعطيك هذه الرسالة بدلاً من انهيار السيرفر
    die(json_encode(["status" => "error", "message" => "لم يتم العثور على ملف Database.php في المسار: " . $db_file]));
}

try {
    // 3. إنشاء كائن قاعدة البيانات كما هو معرف في ملفك Database.php
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("فشل الحصول على اتصال من getConnection()");
    }

    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "خطأ في قاعدة البيانات: " . $e->getMessage()
    ]);
}
?>