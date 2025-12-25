<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config/Database.php';
require_once 'config/JWTHandler.php';

$database = new Database();
$db = $database->getConnection();
$jwt = new JWTHandler();

// 1. التحقق من التوكن وصلاحية الأدمن
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
$userData = $jwt->decodeToken($token);

if (!$userData || $userData['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

// 2. استقبال البيانات (هنا نستخدم $_POST لأننا نرسل صوراً)
$action = $_POST['action'] ?? '';
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? '';
$category_id = $_POST['category_id'] ?? '';
$product_id = $_POST['id'] ?? null;

// 3. منطق التعامل مع الصورة (Upload Logic)
$image_path = "";
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $target_dir = "uploads/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // إنشاء المجلد إذا لم يكن موجوداً
    }
    
    $file_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;
    }
}

// 4. تنفيذ العمليات (Create / Update / Delete)
switch ($action) {
    case 'create':
        $query = "INSERT INTO products (name, description, price, category_id, image_url) 
                  VALUES (:name, :description, :price, :category_id, :image)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':category_id' => $category_id,
            ':image' => $image_path
        ]);
        echo json_encode(["status" => "success", "message" => "Product added successfully"]);
        break;

    case 'delete':
        if ($product_id) {
            $query = "DELETE FROM products WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $product_id]);
            echo json_encode(["status" => "success", "message" => "Product deleted"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}
?>