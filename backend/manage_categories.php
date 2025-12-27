<?php
// 1. منع ظهور أخطاء نصية تفسد الـ JSON
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// التعامل مع طلبات OPTIONS (Preflight) التي يرسلها المتصفح أحياناً
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. استخدام المسار المطلق للملفات المطلوبة
require_once __DIR__ . 'Database.php';
require_once __DIR__ . '/JWTHandler.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $jwt = new JWTHandler();

    // 3. التحقق من الهوية (Admin Only)
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    // تنظيف التوكن في حال وجود كلمة Bearer
    $token = str_replace('Bearer ', '', $authHeader);
    
    $userData = $jwt->decodeToken($token);

    // التحقق من الرتبة (تأكد أن الحرف A كبير كما في قاعدة بياناتك)
    if (!$userData || $userData['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Unauthorized: Admin access required"]);
        exit;
    }

    // 4. استقبال البيانات
    $data = json_decode(file_get_contents("php://input"));
    $action = $data->action ?? '';

    switch ($action) {
        case 'create':
            if (!empty($data->name)) {
                $query = "INSERT INTO categories (name) VALUES (:name)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', htmlspecialchars(strip_tags($data->name)));
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "تم إضافة الصنف بنجاح"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "فشل إدراج الصنف"]);
                }
            }
            break;

        case 'update':
            if (!empty($data->id) && !empty($data->name)) {
                $query = "UPDATE categories SET name = :name WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', htmlspecialchars(strip_tags($data->name)));
                $stmt->bindParam(':id', $data->id);
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "تم تعديل الصنف"]);
                }
            }
            break;

        case 'delete':
            if (!empty($data->id)) {
                $query = "DELETE FROM categories WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $data->id);
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "تم حذف الصنف"]);
                }
            }
            break;

        default:
            echo json_encode(["status" => "error", "message" => "عملية غير صالحة"]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "خطأ في السيرفر: " . $e->getMessage()]);
}
?>