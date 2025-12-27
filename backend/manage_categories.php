<?php
// 1. إعدادات البيئة
error_reporting(E_ALL); // سنفعل الأخطاء الآن لنعرف السبب
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

// 2. فحص وجود الملفات قبل تحميلها (لتجنب انهيار السيرفر)
$db_file = __DIR__ . '/Database.php';
$jwt_file = __DIR__ . '/JWTHandler.php';

if (!file_exists($db_file)) {
    die(json_encode(["status" => "error", "message" => "ملف Database.php غير موجود في: " . $db_file]));
}
if (!file_exists($jwt_file)) {
    die(json_encode(["status" => "error", "message" => "ملف JWTHandler.php غير موجود في: " . $jwt_file]));
}

require_once $db_file;
require_once $jwt_file;

try {
    $database = new Database();
    $db = $database->getConnection();
    $jwt = new JWTHandler();

    // 3. معالجة التوكن
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    
    $userData = $jwt->decodeToken($token);

    // فحص الصلاحية (Admin)
    if (!$userData || strtolower($userData['role']) !== 'admin') {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "صلاحيات غير كافية أو جلسة منتهية"]);
        exit;
    }

    // 4. تنفيذ الإضافة
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->action) && $data->action === 'create') {
        $query = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $db->prepare($query);
        $name = htmlspecialchars(strip_tags($data->name));
        $stmt->bindParam(':name', $name);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "تم إضافة الصنف بنجاح"]);
        } else {
            echo json_encode(["status" => "error", "message" => "فشل تنفيذ الاستعلام في قاعدة البيانات"]);
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "حدث خطأ: " . $e->getMessage()]);
}