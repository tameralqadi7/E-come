<?php
// إعدادات عرض الأخطاء مؤقتاً لنعرف السبب الحقيقي إذا فشل
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// --- بيانات قاعدة البيانات (اكتبها هنا مباشرة للتأكد) ---
$host     = 'اكتب_هنا_host_قاعدة_بياناتك'; // مثال: tidb-cloud-sql...
$db_name  = 'اكتب_هنا_اسم_القاعدة';
$username = 'اكتب_هنا_اسم_المستخدم';
$password = 'اكتب_هنا_كلمة_المرور';

try {
    // الاتصال بقاعدة البيانات
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // استعلام جلب المنتجات
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // إرسال البيانات
    echo json_encode($products);

} catch (Exception $e) {
    http_response_code(500);
    // سيطبع لك السبب الحقيقي للخطأ (مثل: Access denied أو Table not found)
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>