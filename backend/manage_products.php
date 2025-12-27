<?php
// 1. إعدادات البيئة ومنع تداخل الأخطاء
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

// 2. تصحيح المسارات المطلقة (Absolute Paths)
$base_dir = __DIR__;
require_once $base_dir . '/config/Database.php'; // تأكد من المسار حسب مجلدك
require_once $base_dir . '/JWTHandler.php';
require_once $base_dir . '/ProductRepository.php';
require_once $base_dir . '/AuthStrategy.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $jwt = new JWTHandler();

    // 3. التحقق من الصلاحيات (مع تنظيف التوكن)
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    $authContext = new AuthContext(new AdminAuth($jwt)); 
    if (!$authContext->authenticate($token)) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Unauthorized access: Admin only"]);
        exit;
    }

    $productRepo = new ProductRepository($db);

    // 4. استقبال البيانات (دعم FormData و JSON)
    // ملاحظة: رفع الصور يتطلب استخدام $_POST و $_FILES
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['id'] ?? null;

    // 5. منطق رفع الصورة
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = $base_dir . "/uploads/products/"; // مسار مطلق للمجلد
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // نخزن المسار النسبي لقاعدة البيانات ليتم عرضه في الفرونت إند
            $image_path = "backend/uploads/products/" . $file_name;
        }
    }

    // 6. تنفيذ العمليات
    switch ($action) {
        case 'create':
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => $_POST['price'] ?? '',
                'category_id' => $_POST['category_id'] ?? '',
                'image_url' => $image_path
            ];
            
            if (empty($data['name']) || empty($data['price'])) {
                echo json_encode(["status" => "error", "message" => "Name and Price are required"]);
                break;
            }

            if ($productRepo->create($data)) {
                echo json_encode(["status" => "success", "message" => "تم إضافة المنتج بنجاح!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "فشل إضافة المنتج في القاعدة"]);
            }
            break;

        case 'delete':
            if ($product_id && $productRepo->delete($product_id)) {
                echo json_encode(["status" => "success", "message" => "تم حذف المنتج بنجاح!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "فشل حذف المنتج"]);
            }
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid action: " . $action]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server Error: " . $e->getMessage()]);
}
?>