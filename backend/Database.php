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
        $this->host     = getenv('DB_HOST'); 
        $this->db_name  = getenv('DB_NAME');
        $this->username = getenv('DB_USER');
        $this->password = getenv('DB_PASSWORD');
        $this->port     = getenv('DB_PORT') ?: "4000";
    }

    public function getConnection() {
        $this->conn = null;
        
        if (!$this->host || !$this->username) {
            echo "خطأ: متغيرات البيئة غير مكتملة في Render.";
            return null;
        }

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            
            // إضافة خيارات الـ SSL هنا لحل مشكلة Insecure transport
            $options = array(
                PDO::MYSQL_ATTR_SSL_CA => true,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            );
            
            // نمرر الـ options كمعامل رابع في PDO
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}