<?php
// Database configuration for MariaDB access
class Database {
    private $host = 'localhost';
    private $db_name = 'tax_assessment_db';
    private $username = 'root';
    private $password = 'root@475'; // Use the same password you set above
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            error_log("Attempting database connection");
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
            error_log("Database connection successful");
        } catch(PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }

        return $this->conn;
    }
}
?>
