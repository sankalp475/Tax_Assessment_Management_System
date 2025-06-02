<?php
class BalanceSheet {
    private $conn;
    private $table_name = "BALANCE_SHEET";

    public $id;
    public $pan;
    public $assessment_year;
    public $assets;
    public $liabilities;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT ID, PAN, ASSESSMENT_YEAR, ASSETS, LIABILITIES, CREATED_AT, UPDATED_AT 
                 FROM " . $this->table_name . " 
                 ORDER BY ASSESSMENT_YEAR DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT ID, PAN, ASSESSMENT_YEAR, ASSETS, LIABILITIES, CREATED_AT, UPDATED_AT 
                 FROM " . $this->table_name . " 
                 WHERE ID = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['ID'];
            $this->pan = $row['PAN'];
            $this->assessment_year = $row['ASSESSMENT_YEAR'];
            $this->assets = $row['ASSETS'];
            $this->liabilities = $row['LIABILITIES'];
            $this->created_at = $row['CREATED_AT'];
            $this->updated_at = $row['UPDATED_AT'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (PAN, ASSESSMENT_YEAR, ASSETS, LIABILITIES, CREATED_AT, UPDATED_AT)
                VALUES
                (:pan, :assessment_year, :assets, :liabilities, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);

        $this->pan = htmlspecialchars(strip_tags($this->pan));
        $this->assessment_year = htmlspecialchars(strip_tags($this->assessment_year));
        $this->assets = htmlspecialchars(strip_tags($this->assets));
        $this->liabilities = htmlspecialchars(strip_tags($this->liabilities));

        $stmt->bindParam(":pan", $this->pan);
        $stmt->bindParam(":assessment_year", $this->assessment_year);
        $stmt->bindParam(":assets", $this->assets);
        $stmt->bindParam(":liabilities", $this->liabilities);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    PAN = :pan,
                    ASSESSMENT_YEAR = :assessment_year,
                    ASSETS = :assets,
                    LIABILITIES = :liabilities,
                    UPDATED_AT = NOW()
                WHERE
                    ID = :id";

        $stmt = $this->conn->prepare($query);

        $this->pan = htmlspecialchars(strip_tags($this->pan));
        $this->assessment_year = htmlspecialchars(strip_tags($this->assessment_year));
        $this->assets = htmlspecialchars(strip_tags($this->assets));
        $this->liabilities = htmlspecialchars(strip_tags($this->liabilities));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":pan", $this->pan);
        $stmt->bindParam(":assessment_year", $this->assessment_year);
        $stmt->bindParam(":assets", $this->assets);
        $stmt->bindParam(":liabilities", $this->liabilities);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function getByPan($pan) {
        $query = "SELECT ID, PAN, ASSESSMENT_YEAR, ASSETS, LIABILITIES, CREATED_AT, UPDATED_AT 
                 FROM " . $this->table_name . " 
                 WHERE PAN = :pan 
                 ORDER BY ASSESSMENT_YEAR DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pan", $pan);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error in BalanceSheet::getTotalCount: " . $e->getMessage());
            return 0;
        }
    }
}
?>
