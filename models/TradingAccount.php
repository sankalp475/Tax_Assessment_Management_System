<?php
class TradingAccount {
    private $db;
    private $table_name = "TRADING_ACCOUNT";

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY ASSES_YEAR_1 DESC, ASSES_YEAR_2 DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ID = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (PAN, ASSES_YEAR_1, ASSES_YEAR_2, OPENING_STOCK, PURCHASES, 
                 DIRECT_EXPENSES, CLOSING_STOCK, GROSS_PROFIT, CREATED_AT, UPDATED_AT) 
                 VALUES (:pan, :asses_year_1, :asses_year_2, :opening_stock, :purchases, 
                 :direct_expenses, :closing_stock, :gross_profit, NOW(), NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':pan', $data['PAN']);
        $stmt->bindParam(':asses_year_1', $data['ASSES_YEAR_1']);
        $stmt->bindParam(':asses_year_2', $data['ASSES_YEAR_2']);
        $stmt->bindParam(':opening_stock', $data['OPENING_STOCK']);
        $stmt->bindParam(':purchases', $data['PURCHASES']);
        $stmt->bindParam(':direct_expenses', $data['DIRECT_EXPENSES']);
        $stmt->bindParam(':closing_stock', $data['CLOSING_STOCK']);
        $stmt->bindParam(':gross_profit', $data['GROSS_PROFIT']);
        
        return $stmt->execute();
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                 PAN = :pan,
                 ASSES_YEAR_1 = :asses_year_1,
                 ASSES_YEAR_2 = :asses_year_2,
                 OPENING_STOCK = :opening_stock,
                 PURCHASES = :purchases,
                 DIRECT_EXPENSES = :direct_expenses,
                 CLOSING_STOCK = :closing_stock,
                 GROSS_PROFIT = :gross_profit,
                 UPDATED_AT = NOW()
                 WHERE ID = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':pan', $data['PAN']);
        $stmt->bindParam(':asses_year_1', $data['ASSES_YEAR_1']);
        $stmt->bindParam(':asses_year_2', $data['ASSES_YEAR_2']);
        $stmt->bindParam(':opening_stock', $data['OPENING_STOCK']);
        $stmt->bindParam(':purchases', $data['PURCHASES']);
        $stmt->bindParam(':direct_expenses', $data['DIRECT_EXPENSES']);
        $stmt->bindParam(':closing_stock', $data['CLOSING_STOCK']);
        $stmt->bindParam(':gross_profit', $data['GROSS_PROFIT']);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error in TradingAccount::getTotalCount: " . $e->getMessage());
            return 0;
        }
    }
}
?>
