<?php
// إعدادات الـ Header للسماح بالوصول
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once 'Database.php';
require_once 'JWTHandler.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $database = new Database();
        $db = $database->getConnection();

        // 1. البحث عن المستخدم بواسطة الإيميل
        $query = "SELECT id, username, password, role FROM users WHERE email = :email LIMIT 0,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. التحقق من كلمة المرور (مقارنة النص المدخل مع الهاش المشفر)
            if (password_verify($password, $row['password'])) {
                
                // 3. إذا كانت صحيحة، نولد توكن جديد (JWT)
                $jwtHandler = new JWTHandler();
                $tokenData = [
                    "user_id" => $row['id'],
                    "email" => $email,
                    "role" => $row['role']
                ];
                $token = $jwtHandler->generateToken($tokenData);

                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful",
                    "token" => $token
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => "Invalid password."]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "User not found."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Incomplete data."]);
    }
}