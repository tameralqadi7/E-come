<?php
// backend/Database.php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // قمنا بتغيير الأسماء لتطابق مفاتيح Render التي أنشأناها
        $this->host     = getenv('DB_HOST'); 
        $this->db_name  = getenv('DB_NAME');
        $this->username = getenv('DB_USER');
        $this->password = getenv('DB_PASSWORD');
        $this->port     = getenv('DB_PORT') ?: "4000"; // القيمة الافتراضية لـ TiDB هي 4000
    }

    public function getConnection() {
        $this->conn = null;
        
        // التحقق من وجود البيانات لتجنب الاتصال بعنوان فارغ
        if (!$this->host || !$this->username) {
            echo "خطأ: متغيرات البيئة (DB_HOST, DB_USER) غير موجودة في Render.";
            return null;
        }

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}