<?php
// 1. استيراد الملفات اللازمة
require_once 'Database.php';
require_once 'strategies/VerificationManager.php';
require_once 'strategies/EmailVerification.php';
require_once 'strategies/WhatsappVerification.php';

// 2. استقبال البيانات من الـ Frontend (القادمة من ملف register.html)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $method   = $_POST['verify_method']; // 'email' أو 'whatsapp'

    // 3. تطبيق الأمان: تشفير كلمة المرور (Password Hashing)
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // 4. الاتصال بقاعدة البيانات وحفظ المستخدم
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO users (username, email, password) VALUES (:u, :e, :p)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([':u' => $username, ':e' => $email, ':p' => $hashed_password])) {
        
        // 5. تطبيق الـ Strategy Pattern (هنا نستخدم ما برمجته في الخطوة السابقة)
        if ($method == 'email') {
            $context = new VerificationManager(new EmailVerification());
        } else {
            $context = new VerificationManager(new WhatsappVerification());
        }

        // إرسال الكود وإرجاع النتيجة
        $message = $context->execute($email);

        echo json_encode([
            "status" => "success",
            "message" => "User registered. " . $message
        ]);

    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed."]);
    }
}