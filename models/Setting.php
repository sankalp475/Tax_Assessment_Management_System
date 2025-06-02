<?php
class Setting {
    private $db;
    private $table_name = 'SETTINGS';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllSettings() {
        try {
            $query = "SELECT * FROM {$this->table_name} LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error in Setting::getAllSettings: " . $e->getMessage());
            throw new Exception("Failed to load settings");
        }
    }

    public function updateSettings($settings) {
        try {
            // Check if settings exist
            $query = "SELECT COUNT(*) as count FROM {$this->table_name}";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                // Update existing settings
                $query = "UPDATE {$this->table_name} SET 
                    COMPANY_NAME = :company_name,
                    COMPANY_ADDRESS = :company_address,
                    COMPANY_PHONE = :company_phone,
                    COMPANY_EMAIL = :company_email,
                    TAX_YEAR_START = :tax_year_start,
                    TAX_YEAR_END = :tax_year_end,
                    UPDATED_AT = NOW()";
            } else {
                // Insert new settings
                $query = "INSERT INTO {$this->table_name} (
                    COMPANY_NAME,
                    COMPANY_ADDRESS,
                    COMPANY_PHONE,
                    COMPANY_EMAIL,
                    TAX_YEAR_START,
                    TAX_YEAR_END,
                    CREATED_AT,
                    UPDATED_AT
                ) VALUES (
                    :company_name,
                    :company_address,
                    :company_phone,
                    :company_email,
                    :tax_year_start,
                    :tax_year_end,
                    NOW(),
                    NOW()
                )";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':company_name', $settings['company_name']);
            $stmt->bindParam(':company_address', $settings['company_address']);
            $stmt->bindParam(':company_phone', $settings['company_phone']);
            $stmt->bindParam(':company_email', $settings['company_email']);
            $stmt->bindParam(':tax_year_start', $settings['tax_year_start']);
            $stmt->bindParam(':tax_year_end', $settings['tax_year_end']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in Setting::updateSettings: " . $e->getMessage());
            throw new Exception("Failed to update settings");
        }
    }
} 
