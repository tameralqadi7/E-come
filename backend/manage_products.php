<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'Database.php';
require_once 'JWTHandler.php';
require_once 'ProductRepository.php'; // الملف الجديد
require_once 'AuthStrategy.php';      // الملف الجديد

$database = new Database();
$db = $database->getConnection();
$jwt = new JWTHandler();

// 1. تطبيق الـ Strategy Pattern للتحقق من الصلاحيات
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

// نختار "إستراتيجية الأدمن" للتحقق
$authContext = new AuthContext(new AdminAuth($jwt)); 
if (!$authContext->authenticate($token)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

// 2. إعداد الـ Repository للتعامل مع قاعدة البيانات
$productRepo = new ProductRepository($db);

// 3. استقبال البيانات
$action = $_POST['action'] ?? '';
$product_id = $_POST['id'] ?? null;

// 4. منطق الصورة (نفسه لم يتغير لأنه منطق رفع ملفات)
$image_path = "";
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $target_dir = "uploads/products/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $file_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;
    }
}

// 5. تنفيذ العمليات باستخدام الـ Repository
switch ($action) {
    case 'create':
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? '',
            'category_id' => $_POST['category_id'] ?? '',
            'image_url' => $image_path
        ];
        
        if ($productRepo->create($data)) {
            echo json_encode(["status" => "success", "message" => "Product added via Repository!"]);
        }
        break;

    case 'delete':
        if ($product_id && $productRepo->delete($product_id)) {
            echo json_encode(["status" => "success", "message" => "Product deleted via Repository!"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}
?>