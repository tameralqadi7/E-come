<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'Database.php';
require_once 'JWTHandler.php';

$database = new Database();
$db = $database->getConnection();
$jwt = new JWTHandler();

// 1. التحقق من الهوية والصلاحيات (Admin Only)
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
$userData = $jwt->decodeToken($token);

if (!$userData || $userData['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Admin access required"]);
    exit;
}

// 2. استقبال البيانات ونوع العملية
$data = json_decode(file_get_contents("php://input"));
$action = $data->action ?? ''; // (create, update, delete)

switch ($action) {
    // --- عملية الإضافة ---
    case 'create':
        if (!empty($data->name)) {
            $query = "INSERT INTO categories (name) VALUES (:name)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', htmlspecialchars(strip_tags($data->name)));
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Category created"]);
            }
        }
        break;

    // --- عملية التعديل ---
    case 'update':
        if (!empty($data->id) && !empty($data->name)) {
            $query = "UPDATE categories SET name = :name WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', htmlspecialchars(strip_tags($data->name)));
            $stmt->bindParam(':id', $data->id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Category updated"]);
            }
        }
        break;

    // --- عملية الحذف ---
    case 'delete':
        if (!empty($data->id)) {
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $data->id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Category deleted"]);
            }
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}
?>