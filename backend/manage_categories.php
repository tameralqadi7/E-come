<?php
// 1. إعدادات البيئة ومنع تداخل الأخطاء النصية مع JSON
error_reporting(0); 
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

// 2. تأمين المسارات المطلقة (Absolute Paths)
$db_file = __DIR__ . '/config/Database.php'; // جرب هذا المسار أولاً
if (!file_exists($db_file)) {
    $db_file = __DIR__ . '/Database.php'; // المسار البديل
}
$jwt_file = __DIR__ . '/JWTHandler.php';

if (!file_exists($db_file) || !file_exists($jwt_file)) {
    die(json_encode(["status" => "error", "message" => "Required files missing"]));
}

require_once $db_file;
require_once $jwt_file;

try {
    $database = new Database();
    $db = $database->getConnection();
    $jwt = new JWTHandler();

    // 3. التحقق من التوكن والصلاحيات
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    $userData = $jwt->decodeToken($token);

    if (!$userData || strtolower($userData['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die(json_encode(["status" => "error", "message" => "Unauthorized access"]));
    }

    // 4. استقبال البيانات ومعالجة العمليات (Create, Update, Delete)
    $data = json_decode(file_get_contents("php://input"));
    $action = $data->action ?? '';

    switch ($action) {
        // --- عملية الإضافة ---
        case 'create':
            if (!empty($data->name)) {
                $query = "INSERT INTO categories (name) VALUES (:name)";
                $stmt = $db->prepare($query);
                $name = htmlspecialchars(strip_tags($data->name));
                $stmt->bindParam(':name', $name);
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "تم إضافة الصنف"]);
                }
            }
            break;

        // --- عملية التعديل (هذا الجزء كان مفقوداً لديك) ---
        case 'update':
            if (!empty($data->id) && !empty($data->name)) {
                $query = "UPDATE categories SET name = :name WHERE id = :id";
                $stmt = $db->prepare($query);
                $name = htmlspecialchars(strip_tags($data->name));
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':id', $data->id);
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "تم تحديث الصنف بنجاح"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "فشل التعديل"]);
                }
            }
            break;

        // --- عملية الحذف (هذا الجزء كان مفقوداً لديك) ---
        case 'delete':
            if (!empty($data->id)) {
                $query = "DELETE FROM categories WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $data->id);
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "تم حذف الصنف بنجاح"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "فشل الحذف"]);
                }
            }
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid action specified"]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>