<?php
/**
 * Database Configuration
 * 
 * This file handles the database connection for the Tax Assessment Management System.
 */

class Database {
    private static $instance = null;
    private $conn;
    
    /**
     * Database constructor - uses MariaDB
     */
    private function __construct() {
        $host = 'localhost';
        $dbname = 'tax_db';
        $user = 'root';
        $password = ''; // In production, set this to a secure password and use environment variables
        
        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, $user, $password, $options);
            
            // Create tables if they don't exist
            $this->createTables();
            
        } catch(PDOException $e) {
            // In production, log the error instead of displaying it
            error_log("Database connection failed: " . $e->getMessage());
            die("A database error occurred. Please contact support.");
        }
    }
    
    /**
     * Get the database instance
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get the database connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Create database tables if they don't exist
     */
    private function createTables() {
        $queries = [
            // Client Master Record
            "CREATE TABLE IF NOT EXISTS clients (
                pan VARCHAR(10) PRIMARY KEY,
                name VARCHAR(30) NOT NULL,
                fathers_name VARCHAR(30),
                dob DATE,
                address VARCHAR(40),
                telephone VARCHAR(12),
                sex VARCHAR(6),  -- 'male', 'female', or 'other'
                category TINYINT UNSIGNED,  -- 0: INDV, 1: HUF, 2: FIRM, 3: AOP, 4: LA
                residence_status TINYINT UNSIGNED,  -- 0: RESIDENT, 1: NR, 2: NOR
                ward_circle VARCHAR(20)
            )",
            
            // Income Tax Records
            "CREATE TABLE IF NOT EXISTS income_tax_records (
                pan VARCHAR(10),
                ass_year_1 DATE,
                ass_year_2 DATE,
                prev_income DECIMAL(15,2),
                return_type BOOLEAN,  -- 0: original, 1: revised
                income_salary DECIMAL(15,2),
                income_house DECIMAL(15,2),
                income_business DECIMAL(15,2),
                short_term_gain DECIMAL(15,2),
                long_term_gain DECIMAL(15,2),
                capital_gains DECIMAL(15,2),
                other_income DECIMAL(15,2),
                added_income DECIMAL(15,2),
                gross_income DECIMAL(15,2),
                deductions_via DECIMAL(15,2),
                total_income DECIMAL(15,2),
                agr_income DECIMAL(15,2),
                exempt_income DECIMAL(15,2),
                normal_income DECIMAL(15,2),
                normal_tax DECIMAL(15,2),
                special_income DECIMAL(15,2),
                special_tax DECIMAL(15,2),
                tax_on_total DECIMAL(15,2),
                rebate DECIMAL(15,2),
                tax_payable DECIMAL(15,2),
                surcharge DECIMAL(15,2),
                total_tax_payable DECIMAL(15,2),
                relief DECIMAL(15,2),
                net_tax_payable DECIMAL(15,2),
                tan_source DECIMAL(15,2),
                advance_tax_paid DECIMAL(15,2),
                interest_payable DECIMAL(15,2),
                assessment_paid_1 DECIMAL(15,2),
                assessment_paid_2 DECIMAL(15,2),
                total_assessment_paid DECIMAL(15,2),
                bsr_code VARCHAR(15),
                deposit_date DATE,
                challan_serial VARCHAR(10),
                amount DECIMAL(15,2),
                bank_name VARCHAR(20),
                balance_refund DECIMAL(15,2),
                balance_tax DECIMAL(15,2),
                enc1 VARCHAR(20),
                enc2 VARCHAR(20),
                enc3 VARCHAR(20),
                enc4 VARCHAR(20),
                enc5 VARCHAR(20),
                enc6 VARCHAR(20),
                PRIMARY KEY (pan, ass_year_1, ass_year_2),
                FOREIGN KEY (pan) REFERENCES clients(pan) ON DELETE CASCADE
            )",
            
            // Trading Account
            "CREATE TABLE IF NOT EXISTS trading_accounts (
                pan VARCHAR(10),
                ass_year_1 DATE,
                ass_year_2 DATE,
                opening_stock DECIMAL(15,2),
                purchases DECIMAL(15,2),
                carriage DECIMAL(15,2),
                octroi DECIMAL(15,2),
                customs_duty DECIMAL(15,2),
                wages DECIMAL(15,2),
                coal_water_gas DECIMAL(15,2),
                power_heating DECIMAL(15,2),
                manufacturing_costs DECIMAL(15,2),
                consumable_supplies DECIMAL(15,2),
                factory_expenses DECIMAL(15,2),
                royalty DECIMAL(15,2),
                gross_profit DECIMAL(15,2),
                sales DECIMAL(15,2),
                closing_stock DECIMAL(15,2),
                gross_loss DECIMAL(15,2),
                PRIMARY KEY (pan, ass_year_1, ass_year_2),
                FOREIGN KEY (pan) REFERENCES clients(pan) ON DELETE CASCADE
            )",
            
            // Profit & Loss Account
            "CREATE TABLE IF NOT EXISTS pl_accounts (
                pan VARCHAR(10),
                ass_year_1 DATE,
                ass_year_2 DATE,
                gross_loss DECIMAL(15,2),
                salaries_wages DECIMAL(15,2),
                office_godown_rent DECIMAL(15,2),
                office_expenses DECIMAL(15,2),
                miscellaneous_expenses DECIMAL(15,2),
                insurance DECIMAL(15,2),
                stationery DECIMAL(15,2),
                staff_welfare DECIMAL(15,2),
                lighting_water DECIMAL(15,2),
                establishment_expenses DECIMAL(15,2),
                postage_telegram DECIMAL(15,2),
                law_charges DECIMAL(15,2),
                repairs DECIMAL(15,2),
                distribution_expenses DECIMAL(15,2),
                traveling DECIMAL(15,2),
                general_expenses DECIMAL(15,2),
                stable_expenses DECIMAL(15,2),
                selling_expenses DECIMAL(15,2),
                carriage_outward DECIMAL(15,2),
                carriage_sales DECIMAL(15,2),
                indirect_wages DECIMAL(15,2),
                audit_fees DECIMAL(15,2),
                entertainment DECIMAL(15,2),
                interest_paid DECIMAL(15,2),
                discount_allowed DECIMAL(15,2),
                bad_debts DECIMAL(15,2),
                bad_debts_reserve DECIMAL(15,2),
                depreciation DECIMAL(15,2),
                interest_capital DECIMAL(15,2),
                discount_charges DECIMAL(15,2),
                bank_charges DECIMAL(15,2),
                export_charges DECIMAL(15,2),
                trade_expenses DECIMAL(15,2),
                administration DECIMAL(15,2),
                financial_expenses DECIMAL(15,2),
                commission DECIMAL(15,2),
                advertisement DECIMAL(15,2),
                charity DECIMAL(15,2),
                sample_expenses DECIMAL(15,2),
                license_fee DECIMAL(15,2),
                delivery_charges DECIMAL(15,2),
                brokerage DECIMAL(15,2),
                sales_tax DECIMAL(15,2),
                loss_assets DECIMAL(15,2),
                loss_fire_theft DECIMAL(15,2),
                net_profit DECIMAL(15,2),
                PRIMARY KEY (pan, ass_year_1, ass_year_2),
                FOREIGN KEY (pan) REFERENCES clients(pan) ON DELETE CASCADE
            )",
            
            // Balance Sheet
            "CREATE TABLE IF NOT EXISTS balance_sheets (
                pan VARCHAR(10),
                ass_year_1 DATE,
                ass_year_2 DATE,
                bills_payable DECIMAL(15,2),
                creditors DECIMAL(15,2),
                loans DECIMAL(15,2),
                expenses_outstanding DECIMAL(15,2),
                capital DECIMAL(15,2),
                net_profit DECIMAL(15,2),
                interest_capital DECIMAL(15,2),
                drawings DECIMAL(15,2),
                net_loss DECIMAL(15,2),
                income_tax DECIMAL(15,2),
                cash_hand DECIMAL(15,2),
                cash_bank DECIMAL(15,2),
                investments DECIMAL(15,2),
                bills_receivable DECIMAL(15,2),
                debtors DECIMAL(15,2),
                stock_closing DECIMAL(15,2),
                stores DECIMAL(15,2),
                plant_machinery DECIMAL(15,2),
                freehold_premises DECIMAL(15,2),
                unexpired_expenses DECIMAL(15,2),
                goodwill DECIMAL(15,2),
                PRIMARY KEY (pan, ass_year_1, ass_year_2),
                FOREIGN KEY (pan) REFERENCES clients(pan) ON DELETE CASCADE
            )"
        ];
        
        foreach ($queries as $query) {
            try {
                $this->conn->exec($query);
            } catch (PDOException $e) {
                error_log("Table creation failed: " . $e->getMessage());
            }
        }
    }
}
