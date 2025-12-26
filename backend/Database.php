<?php
// backend/Database.php

class Database {
    // استخدام getenv لقراءة المتغيرات من Railway، وإذا لم تكن موجودة يستخدم القيم الافتراضية
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // إذا كان الكود شغال على Railway سيقرأ القيم منها، وإذا كان محلياً سيأخذ القيم الثانية
        $this->host     = getenv('MYSQLHOST') ?: "db"; 
        $this->db_name  = getenv('MYSQLDATABASE') ?: "ecommerce_db";
        $this->username = getenv('MYSQLUSER') ?: "root";
        $this->password = getenv('MYSQLPASSWORD') ?: "root_password";
        $this->port     = getenv('MYSQLPORT') ?: "3306";
    }

    public function getConnection() {
        $this->conn = null;
        try {
            // إضافة المنفذ (Port) مهم جداً في Railway
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // في الإنتاج، يفضل عدم طباعة الخطأ كاملاً، لكن للمناقشة اتركها هكذا
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}