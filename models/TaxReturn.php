<?php
class TaxReturn {
    private $conn;
    private $table_name = "INCOME_TAX_RECORD";

    public $id;
    public $pan;
    public $assessment_year;
    public $return_original_revised;
    public $gross_income;
    public $deductions;
    public $taxable_income;
    public $tax_paid;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (PAN, ASSESSMENT_YEAR, RETURN_ORIGINAL_REVISED, GROSS_INCOME, 
                DEDUCTIONS, TAXABLE_INCOME, TAX_PAID, CREATED_AT, UPDATED_AT)
                VALUES
                (:pan, :assessment_year, :return_original_revised, :gross_income,
                :deductions, :taxable_income, :tax_paid, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->pan = htmlspecialchars(strip_tags($this->pan));
        $this->assessment_year = htmlspecialchars(strip_tags($this->assessment_year));
        $this->return_original_revised = htmlspecialchars(strip_tags($this->return_original_revised));
        $this->gross_income = htmlspecialchars(strip_tags($this->gross_income));
        $this->deductions = htmlspecialchars(strip_tags($this->deductions));
        $this->taxable_income = htmlspecialchars(strip_tags($this->taxable_income));
        $this->tax_paid = htmlspecialchars(strip_tags($this->tax_paid));

        // Bind parameters
        $stmt->bindParam(":pan", $this->pan);
        $stmt->bindParam(":assessment_year", $this->assessment_year);
        $stmt->bindParam(":return_original_revised", $this->return_original_revised);
        $stmt->bindParam(":gross_income", $this->gross_income);
        $stmt->bindParam(":deductions", $this->deductions);
        $stmt->bindParam(":taxable_income", $this->taxable_income);
        $stmt->bindParam(":tax_paid", $this->tax_paid);

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE ID = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['ID'];
            $this->pan = $row['PAN'];
            $this->assessment_year = $row['ASSESSMENT_YEAR'];
            $this->return_original_revised = $row['RETURN_ORIGINAL_REVISED'];
            $this->gross_income = $row['GROSS_INCOME'];
            $this->deductions = $row['DEDUCTIONS'];
            $this->taxable_income = $row['TAXABLE_INCOME'];
            $this->tax_paid = $row['TAX_PAID'];
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
                    RETURN_ORIGINAL_REVISED = :return_original_revised,
                    GROSS_INCOME = :gross_income,
                    DEDUCTIONS = :deductions,
                    TAXABLE_INCOME = :taxable_income,
                    TAX_PAID = :tax_paid,
                    UPDATED_AT = NOW()
                WHERE
                    ID = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->pan = htmlspecialchars(strip_tags($this->pan));
        $this->assessment_year = htmlspecialchars(strip_tags($this->assessment_year));
        $this->return_original_revised = htmlspecialchars(strip_tags($this->return_original_revised));
        $this->gross_income = htmlspecialchars(strip_tags($this->gross_income));
        $this->deductions = htmlspecialchars(strip_tags($this->deductions));
        $this->taxable_income = htmlspecialchars(strip_tags($this->taxable_income));
        $this->tax_paid = htmlspecialchars(strip_tags($this->tax_paid));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameters
        $stmt->bindParam(":pan", $this->pan);
        $stmt->bindParam(":assessment_year", $this->assessment_year);
        $stmt->bindParam(":return_original_revised", $this->return_original_revised);
        $stmt->bindParam(":gross_income", $this->gross_income);
        $stmt->bindParam(":deductions", $this->deductions);
        $stmt->bindParam(":taxable_income", $this->taxable_income);
        $stmt->bindParam(":tax_paid", $this->tax_paid);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByPan($pan) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE PAN = :pan ORDER BY ASSESSMENT_YEAR DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pan", $pan);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByPanAndYear($pan, $year) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE PAN = :pan AND ASSESSMENT_YEAR = :year LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pan", $pan);
        $stmt->bindParam(":year", $year);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isDuplicate() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE PAN = :pan AND ASSESSMENT_YEAR = :year AND RETURN_ORIGINAL_REVISED = :type AND ID != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pan", $this->pan);
        $stmt->bindParam(":year", $this->assessment_year);
        $stmt->bindParam(":type", $this->return_original_revised);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error in TaxReturn::getTotalCount: " . $e->getMessage());
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
            error_log("Error in TaxReturn::getRecent: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalTaxPaid() {
        try {
            $query = "SELECT SUM(TAX_PAID) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error in TaxReturn::getTotalTaxPaid: " . $e->getMessage());
            return 0;
        }
    }

    public function getAverageTaxPaid() {
        try {
            $query = "SELECT AVG(TAX_PAID) as average FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['average'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error in TaxReturn::getAverageTaxPaid: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalIncome() {
        try {
            $query = "SELECT SUM(GROSS_INCOME) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error in TaxReturn::getTotalIncome: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalDeductions() {
        try {
            $query = "SELECT SUM(DEDUCTIONS) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error in TaxReturn::getTotalDeductions: " . $e->getMessage());
            return 0;
        }
    }
}
?>
