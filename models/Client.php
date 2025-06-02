<?php
class Client {
    private $conn;
    private $table_name = "CLIENT_RECORD";

    public $pan;
    public $name;
    public $fathers_name;
    public $dob;
    public $category;
    public $address;
    public $phone;
    public $email;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (PAN, NAME, FATHERS_NAME, DOB, CATEGORY, ADDRESS, PHONE, EMAIL, CREATED_AT)
                VALUES
                (:pan, :name, :fathers_name, :dob, :category, :address, :phone, :email, :created_at)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->pan = htmlspecialchars(strip_tags($this->pan));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->fathers_name = htmlspecialchars(strip_tags($this->fathers_name));
        $this->dob = htmlspecialchars(strip_tags($this->dob));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->created_at = date('Y-m-d H:i:s');

        // Bind values
        $stmt->bindParam(":pan", $this->pan);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":fathers_name", $this->fathers_name);
        $stmt->bindParam(":dob", $this->dob);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":created_at", $this->created_at);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY CREATED_AT DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE PAN = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->pan);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['NAME'];
            $this->fathers_name = $row['FATHERS_NAME'];
            $this->dob = $row['DOB'];
            $this->category = $row['CATEGORY'];
            $this->address = $row['ADDRESS'];
            $this->phone = $row['PHONE'];
            $this->email = $row['EMAIL'];
            $this->created_at = $row['CREATED_AT'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET NAME = :name,
                    FATHERS_NAME = :fathers_name,
                    DOB = :dob,
                    CATEGORY = :category,
                    ADDRESS = :address,
                    PHONE = :phone,
                    EMAIL = :email
                WHERE PAN = :pan";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->fathers_name = htmlspecialchars(strip_tags($this->fathers_name));
        $this->dob = htmlspecialchars(strip_tags($this->dob));
        // Ensure category is one of the valid values
        $valid_categories = ['Individual', 'HUF', 'Partnership Firm', 'LLP', 'Company', 'AOP/BOI', 'Trust'];
        $this->category = in_array($this->category, $valid_categories) ? $this->category : 'Individual';
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->pan = htmlspecialchars(strip_tags($this->pan));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":fathers_name", $this->fathers_name);
        $stmt->bindParam(":dob", $this->dob);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":pan", $this->pan);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE PAN = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->pan);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function isPanUnique() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE PAN = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->pan);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['count'] == 0;
    }

    public function getByPan($pan) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE PAN = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $pan);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error in Client::getTotalCount: " . $e->getMessage());
            return 0;
        }
    }

    public function getRecent($limit = 5) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY CREATED_AT DESC LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in Client::getRecent: " . $e->getMessage());
            return [];
        }
    }
}
?>
