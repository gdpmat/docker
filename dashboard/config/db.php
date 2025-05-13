<?php
// ตั้งค่าการเชื่อมต่อกับฐานข้อมูล
function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $host = 'mysql-db';
        $dbname = 'scraping_db';
        $username = 'scraper';
        $password = 'scraperpassword';
        
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die(json_encode([
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้'
            ]));
        }
    }
    
    return $conn;
}
