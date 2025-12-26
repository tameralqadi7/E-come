<?php
// backend/login_api.php

// إعدادات الـ Header للسماح بالوصول
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'Database.php';
require_once 'JWTHandler.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // استخدام trim لحذف أي مسافات فارغة قد تأتي من المتصفح بالخطأ
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // التحقق من وصول البيانات
    if (!empty($email) && !empty($password)) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            if ($db === null) {
                throw new Exception("فشل الاتصال بقاعدة البيانات.");
            }

            // 1. البحث عن المستخدم بواسطة الإيميل (استخدام TRIM لضمان الدقة)
            $query = "SELECT id, username, password, role FROM users WHERE TRIM(email) = :email LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // 2. التحقق من كلمة المرور
                if (password_verify($password, $row['password'])) {
                    
                    // 3. توليد توكن جديد (JWT)
                    $jwtHandler = new JWTHandler();
                    $tokenData = [
                        "user_id" => $row['id'],
                        "email" => $email,
                        "role" => $row['role'] ?? 'Customer'
                    ];
                    $token = $jwtHandler->generateToken($tokenData);

                    echo json_encode([
                        "status" => "success",
                        "message" => "Login successful",
                        "token" => $token
                    ]);
                } else {
                    // تسجيل الخطأ في الـ Logs لمساعدتك في التتبع
                    error_log("Login Failed: Password mismatch for user: " . $email);
                    
                    http_response_code(401);
                    echo json_encode(["status" => "error", "message" => "كلمة المرور غير صحيحة."]);
                }
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "المستخدم غير موجود."]);
            }
        } catch (Exception $e) {
            error_log("Server Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "خطأ في السيرفر: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "يرجى إدخال البريد الإلكتروني وكلمة المرور."]);
    }
}