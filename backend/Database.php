<?php
// backend/Database.php

class Database {
    // ملاحظة: host هنا هو "db" لأن هذا هو اسم الخدمة في ملف docker-compose.yml
    private $host = "db"; 
    private $db_name = "ecommerce_db";
    private $username = "root";
    private $password = "root_password";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // استخدام PDO للاتصال (أكثر أماناً وحداثة)
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}