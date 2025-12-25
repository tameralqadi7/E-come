<?php
// السماح بالوصول من أي مكان (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. استعلام لجلب المعرف والاسم لكل صنف
    $query = "SELECT id, name FROM categories ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    // 2. تحويل النتيجة إلى Array
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. إرسال النتيجة بتنسيق JSON
    http_response_code(200);
    echo json_encode($categories);

} catch (PDOException $e) {
    // في حال حدوث خطأ في القاعدة
    http_response_code(500);
    echo json_encode(["message" => "فشل في جلب الأصناف: " . $e->getMessage()]);
}
?>