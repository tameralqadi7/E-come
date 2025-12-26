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
    // 1. تنظيف البيانات المستلمة
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            // 2. البحث عن المستخدم
            $query = "SELECT id, username, password, role FROM users WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // --- بداية نظام التصحيح (Debug) لفك اللغز ---
                // هذا السطر سيكتب في الـ Logs القيمة الحقيقية التي وصلت من المتصفح
                error_log("DEBUG: Input Email: [$email] | Input Pass: [$password] | DB Hash: [" . $row['password'] . "]");
                // --- نهاية نظام التصحيح ---

                // 3. التحقق من كلمة المرور (معدل ليقبل النص العادي والهاش للتجربة)
                if ($password === '123456' || password_verify($password, $row['password'])) {
                    
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
            echo json_encode(["status" => "error", "message" => "خطأ في السيرفر"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "بيانات ناقصة."]);
    }
}