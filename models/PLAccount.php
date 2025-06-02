<?php
class PLAccount {
    private $conn;
    private $table_name = "PL_ACCOUNT";

    public $id;
    public $pan;
    public $assessment_year;
    public $gross_profit;
    public $indirect_income;
    public $indirect_expenses;
    public $net_profit;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (PAN, ASSESSMENT_YEAR, GROSS_PROFIT, INDIRECT_INCOME, INDIRECT_EXPENSES, NET_PROFIT, CREATED_AT, UPDATED_AT)
                VALUES
                (:pan, :assessment_year, :gross_profit, :indirect_income, :indirect_expenses, :net_profit, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->pan = htmlspecialchars(strip_tags($this->pan));
        $this->assessment_year = htmlspecialchars(strip_tags($this->assessment_year));
        $this->gross_profit = htmlspecialchars(strip_tags($this->gross_profit));
        $this->indirect_income = htmlspecialchars(strip_tags($this->indirect_income));
        $this->indirect_expenses = htmlspecialchars(strip_tags($this->indirect_expenses));
        $this->net_profit = htmlspecialchars(strip_tags($this->net_profit));

        // Bind parameters
        $stmt->bindParam(":pan", $this->pan);
        $stmt->bindParam(":assessment_year", $this->assessment_year);
        $stmt->bindParam(":gross_profit", $this->gross_profit);
        $stmt->bindParam(":indirect_income", $this->indirect_income);
        $stmt->bindParam(":indirect_expenses", $this->indirect_expenses);
        $stmt->bindParam(":net_profit", $this->net_profit);

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE ID = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['ID'];
            $this->pan = $row['PAN'];
            $this->assessment_year = $row['ASSESSMENT_YEAR'];
            $this->gross_profit = $row['GROSS_PROFIT'];
            $this->indirect_income = $row['INDIRECT_INCOME'];
            $this->indirect_expenses = $row['INDIRECT_EXPENSES'];
            $this->net_profit = $row['NET_PROFIT'];
            $this->created_at = $row['CREATED_AT'];
            $this->updated_at = $row['UPDATED_AT'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    PAN = :pan,
                    ASSESSMENT_YEAR = :assessment_year,
                    GROSS_PROFIT = :gross_profit,
                    INDIRECT_INCOME = :indirect_income,
                    INDIRECT_EXPENSES = :indirect_expenses,
                    NET_PROFIT = :net_profit,
                    UPDATED_AT = NOW()
                WHERE
                    ID = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->pan = htmlspecialchars(strip_tags($this->pan));
        $this->assessment_year = htmlspecialchars(strip_tags($this->assessment_year));
        $this->gross_profit = htmlspecialchars(strip_tags($this->gross_profit));
        $this->indirect_income = htmlspecialchars(strip_tags($this->indirect_income));
        $this->indirect_expenses = htmlspecialchars(strip_tags($this->indirect_expenses));
        $this->net_profit = htmlspecialchars(strip_tags($this->net_profit));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameters
        $stmt->bindParam(":pan", $this->pan);
        $stmt->bindParam(":assessment_year", $this->assessment_year);
        $stmt->bindParam(":gross_profit", $this->gross_profit);
        $stmt->bindParam(":indirect_income", $this->indirect_income);
        $stmt->bindParam(":indirect_expenses", $this->indirect_expenses);
        $stmt->bindParam(":net_profit", $this->net_profit);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByPan($pan) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE PAN = ? ORDER BY ASSESSMENT_YEAR DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $pan);
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
            error_log("Error in PLAccount::getTotalCount: " . $e->getMessage());
            return 0;
        }
    }
}
?>
