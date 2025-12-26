<?php
// إعدادات الرأس (Headers) للسماح بتبادل البيانات (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 1. استدعاء كل الملفات التي أنشأناها
require_once 'Database.php';
require_once 'JWTHandler.php';
require_once 'VerificationManager.php';
require_once 'EmailVerification.php';


// 2. التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // استقبال البيانات (سواء من Form عادي أو JSON)
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $method   = $_POST['verify_method'] ?? 'email'; // الخيار القادم من الـ Select

    if (!empty($username) && !empty($email) && !empty($password)) {
        
        $database = new Database();
        $db = $database->getConnection();

        try {
            // 3. تشفير كلمة المرور (Security Requirement)
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // 4. حفظ المستخدم في قاعدة البيانات
            $query = "INSERT INTO users (username, email, password, role) VALUES (:u, :e, :p, 'Customer')";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([':u' => $username, ':e' => $email, ':p' => $hashed_password])) {
                $userId = $db->lastInsertId();

                // 5. تطبيق الـ Strategy Pattern بناءً على اختيار المستخدم
                if ($method == 'email') {
                    $verifyContext = new VerificationManager(new EmailVerification());
                } else {
                    $verifyContext = new VerificationManager(new WhatsappVerification());
                }
                $verificationMessage = $verifyContext->execute($email);

                // 6. توليد الـ JWT Token (Authentication Requirement)
                $jwtHandler = new JWTHandler();
                $tokenData = [
                    "user_id" => $userId,
                    "email" => $email,
                    "role" => "Customer",
                    "exp" => time() + 3600 // صلاحية التوكن ساعة واحدة
                ];
                $token = $jwtHandler->generateToken($tokenData);

                // 7. الرد النهائي بالنجاح
                http_response_code(201);
                echo json_encode([
                    "status" => "success",
                    "message" => "Registration successful!",
                    "verification" => $verificationMessage,
                    "token" => $token
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Email might already exist or DB error."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Incomplete data."]);
    }
}