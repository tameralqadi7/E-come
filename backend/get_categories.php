<?php
// 1. منع الأخطاء النصية التي تفسد الـ JSON
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. استخدام المسار المطلق الصحيح (بدون مائلة في البداية)
// نحن نبحث عن ملف Database.php داخل مجلد اسمه config
$db_path = __DIR__ . '/Database.php';

if (!file_exists($db_path)) {
    // محاولة ثانية إذا كان الملف في نفس المجلد
    $db_path = __DIR__ . '/Database.php';
}

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "لم يتم العثور على ملف Database.php"]));
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // 3. استعلام جلب الأصناف
    $query = "SELECT id, name FROM categories ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. إرسال النتيجة (مصفوفة فارغة [] أو بيانات)
    echo json_encode($categories);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>