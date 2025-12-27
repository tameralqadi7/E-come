<?php
// 1. منع ظهور أي أخطاء نصية قد تفسد الـ JSON المرسل للمتصفح
error_reporting(0);
ini_set('display_errors', 0);

// 2. السماح بالوصول وتحديد نوع البيانات كـ JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 3. الاتصال المباشر بقاعدة البيانات (لتجنب أخطاء require_once في المسارات)
// سيقوم النظام بجلب المتغيرات من بيئة Render تلقائياً
$host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 4. استعلام جلب المنتجات مع اسم الصنف
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

    // 5. إرسال البيانات (إذا كانت المصفوفة فارغة سيرسل [] وهذا صحيح)
    http_response_code(200);
    echo json_encode($products);

} catch (Exception $e) {
    // في حال حدوث خطأ، نرسل رسالة JSON نظيفة بدلاً من خطأ HTML
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "خطأ في الاتصال أو جلب البيانات",
        "debug" => $e->getMessage() 
    ]);
}
?>