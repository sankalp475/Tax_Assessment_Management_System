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
     * Database constructor - uses PostgreSQL
     */
    private function __construct() {
        // Use environment variables for PostgreSQL
        $host = getenv('PGHOST');
        $port = getenv('PGPORT');
        $dbname = getenv('PGDATABASE');
        $user = getenv('PGUSER');
        $password = getenv('PGPASSWORD');
        
        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $this->conn = new PDO($dsn, $user, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if they don't exist
            $this->createTables();
            
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            die();
        }
    }
    
    /**
     * Creates DB tables if they don't exist
     */
    private function createTables() {
        // Check if users table exists
        $stmt = $this->conn->prepare("SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'users'
        )");
        $stmt->execute();
        $usersTableExists = $stmt->fetchColumn();
        
        if (!$usersTableExists) {
            // Create users table
            $this->conn->exec("CREATE TABLE users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                fullname VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Insert user accounts
            $this->insertUserAccounts();
        }
        
        // Check if clients table exists
        $stmt = $this->conn->prepare("SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'clients'
        )");
        $stmt->execute();
        $tableExists = $stmt->fetchColumn();
        
        if (!$tableExists) {
            // Create clients table
            $this->conn->exec("CREATE TABLE clients (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                pan VARCHAR(10) NOT NULL,
                client_type VARCHAR(50) NOT NULL,
                email VARCHAR(255),
                phone VARCHAR(20),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create tax_returns table
            $this->conn->exec("CREATE TABLE tax_returns (
                id SERIAL PRIMARY KEY,
                client_id INTEGER REFERENCES clients(id),
                assessment_year VARCHAR(10) NOT NULL,
                return_type VARCHAR(10) NOT NULL,
                filing_date DATE NOT NULL,
                total_income DECIMAL(12,2) NOT NULL,
                tax_payable DECIMAL(12,2) NOT NULL,
                is_revised BOOLEAN DEFAULT false,
                original_return_id INTEGER,
                acknowledgement_no VARCHAR(20),
                filing_type VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create trading_accounts table
            $this->conn->exec("CREATE TABLE trading_accounts (
                id SERIAL PRIMARY KEY,
                client_id INTEGER REFERENCES clients(id),
                fiscal_year VARCHAR(10) NOT NULL,
                opening_stock DECIMAL(12,2) DEFAULT 0,
                purchases DECIMAL(12,2) DEFAULT 0,
                direct_expenses DECIMAL(12,2) DEFAULT 0,
                closing_stock DECIMAL(12,2) DEFAULT 0,
                gross_profit DECIMAL(12,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create profit_loss_accounts table
            $this->conn->exec("CREATE TABLE profit_loss_accounts (
                id SERIAL PRIMARY KEY,
                client_id INTEGER REFERENCES clients(id),
                fiscal_year VARCHAR(10) NOT NULL,
                gross_profit DECIMAL(12,2) DEFAULT 0,
                other_income DECIMAL(12,2) DEFAULT 0,
                admin_expenses DECIMAL(12,2) DEFAULT 0,
                selling_expenses DECIMAL(12,2) DEFAULT 0,
                financial_expenses DECIMAL(12,2) DEFAULT 0,
                depreciation DECIMAL(12,2) DEFAULT 0,
                net_profit DECIMAL(12,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create balance_sheets table
            $this->conn->exec("CREATE TABLE balance_sheets (
                id SERIAL PRIMARY KEY,
                client_id INTEGER REFERENCES clients(id),
                fiscal_year VARCHAR(10) NOT NULL,
                capital DECIMAL(12,2) DEFAULT 0,
                reserves DECIMAL(12,2) DEFAULT 0,
                long_term_liabilities DECIMAL(12,2) DEFAULT 0,
                current_liabilities DECIMAL(12,2) DEFAULT 0,
                fixed_assets DECIMAL(12,2) DEFAULT 0,
                investments DECIMAL(12,2) DEFAULT 0,
                current_assets DECIMAL(12,2) DEFAULT 0,
                misc_expenses DECIMAL(12,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create activity_logs table
            $this->conn->exec("CREATE TABLE activity_logs (
                id SERIAL PRIMARY KEY,
                client_id INTEGER REFERENCES clients(id),
                activity_type VARCHAR(50) NOT NULL,
                activity_description TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Insert sample data
            $this->insertSampleData();
        }
    }
    
    /**
     * Insert user accounts into the users table
     */
    private function insertUserAccounts() {
        // Hash passwords - In a real app, use a stronger password hashing algorithm
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        
        // Sample user accounts - 2 normal users and 3 admin users
        $users = [
            // Regular users
            ['user1', $passwordHash, 'John Smith', 'john@example.com', 'user'],
            ['user2', $passwordHash, 'Jane Doe', 'jane@example.com', 'user'],
            
            // Admin users
            ['admin1', $passwordHash, 'Admin User 1', 'admin1@example.com', 'admin'],
            ['admin2', $passwordHash, 'Admin User 2', 'admin2@example.com', 'admin'],
            ['admin3', $passwordHash, 'Admin User 3', 'admin3@example.com', 'admin'],
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO users (username, password, fullname, email, role) VALUES (?, ?, ?, ?, ?)");
        
        foreach($users as $user) {
            $stmt->execute($user);
        }
        
        echo "User accounts created successfully.<br>";
    }
    
    /**
     * Insert sample data into the tables
     */
    private function insertSampleData() {
        // Sample clients
        $clients = [
            ['Rahul Patel', 'ABCDE1234F', 'individual', 'rahul@example.com', '9876543210', 'Mumbai, Maharashtra'],
            ['Priya Sharma', 'FGHIJ5678K', 'individual', 'priya@example.com', '9876543211', 'Delhi, NCR'],
            ['Sharma Enterprises', 'KLMNO9012P', 'partnership', 'sharma@example.com', '9876543212', 'Bangalore, Karnataka'],
            ['Global Trading Co.', 'QRSTU3456V', 'company', 'global@example.com', '9876543213', 'Chennai, Tamil Nadu'],
            ['Kumar Industries', 'WXYZ7890A', 'company', 'kumar@example.com', '9876543214', 'Kolkata, West Bengal'],
            ['Singh Family Trust', 'BCDEF1234G', 'trust', 'singh@example.com', '9876543215', 'Hyderabad, Telangana']
        ];
        
        $clientIds = [];
        
        $stmt = $this->conn->prepare("INSERT INTO clients (name, pan, client_type, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach($clients as $client) {
            $stmt->execute($client);
            $clientIds[] = $this->conn->lastInsertId();
        }
        
        // Sample tax returns
        $returns = [
            // Client 1 returns
            [$clientIds[0], '2023-2024', 'ITR-1', '2023-07-31', 850000, 45000, false, null, '12345678901', 'E-filing'],
            [$clientIds[0], '2022-2023', 'ITR-1', '2022-07-29', 780000, 38000, false, null, '12345678902', 'E-filing'],
            [$clientIds[0], '2021-2022', 'ITR-1', '2021-07-31', 720000, 32000, false, null, '12345678903', 'E-filing'],
            
            // Client 2 returns
            [$clientIds[1], '2023-2024', 'ITR-1', '2023-07-25', 920000, 52000, false, null, '12345678904', 'E-filing'],
            [$clientIds[1], '2022-2023', 'ITR-1', '2022-07-28', 850000, 45000, true, null, '12345678905', 'E-filing'],
            // Revised return for Client 2, 2022-2023
            [$clientIds[1], '2022-2023', 'ITR-1', '2022-09-15', 890000, 48000, true, 5, '12345678906', 'E-filing'],
            
            // Client 3 returns
            [$clientIds[2], '2023-2024', 'ITR-5', '2023-07-20', 4500000, 850000, false, null, '12345678907', 'E-filing'],
            [$clientIds[2], '2022-2023', 'ITR-5', '2022-07-22', 4200000, 780000, false, null, '12345678908', 'E-filing'],
            
            // Client 4 returns
            [$clientIds[3], '2023-2024', 'ITR-6', '2023-07-15', 7800000, 1950000, false, null, '12345678909', 'E-filing'],
            
            // Client 5 returns
            [$clientIds[4], '2023-2024', 'ITR-6', '2023-07-10', 8500000, 2125000, false, null, '12345678910', 'E-filing'],
            
            // Client 6 returns
            [$clientIds[5], '2023-2024', 'ITR-7', '2023-07-05', 5000000, 400000, false, null, '12345678911', 'E-filing'],
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO tax_returns 
            (client_id, assessment_year, return_type, filing_date, total_income, tax_payable, is_revised, original_return_id, acknowledgement_no, filing_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach($returns as $return) {
            $stmt->execute($return);
        }
        
        // Sample trading accounts
        $tradingAccounts = [
            // Client 3 - Sharma Enterprises
            [$clientIds[2], '2023-2024', 150000, 3200000, 350000, 180000, 880000],
            [$clientIds[2], '2022-2023', 120000, 2800000, 320000, 150000, 710000],
            
            // Client 4 - Global Trading Co.
            [$clientIds[3], '2023-2024', 450000, 5600000, 750000, 520000, 2120000],
            
            // Client 5 - Kumar Industries
            [$clientIds[4], '2023-2024', 380000, 6200000, 820000, 450000, 2350000],
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO trading_accounts 
            (client_id, fiscal_year, opening_stock, purchases, direct_expenses, closing_stock, gross_profit) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach($tradingAccounts as $tradingAccount) {
            $stmt->execute($tradingAccount);
        }
        
        // Sample profit & loss accounts
        $profitLossAccounts = [
            // Client 3 - Sharma Enterprises
            [$clientIds[2], '2023-2024', 880000, 120000, 350000, 180000, 120000, 80000, 270000],
            [$clientIds[2], '2022-2023', 710000, 90000, 310000, 150000, 100000, 70000, 170000],
            
            // Client 4 - Global Trading Co.
            [$clientIds[3], '2023-2024', 2120000, 320000, 650000, 450000, 320000, 220000, 800000],
            
            // Client 5 - Kumar Industries
            [$clientIds[4], '2023-2024', 2350000, 350000, 720000, 480000, 350000, 250000, 900000],
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO profit_loss_accounts 
            (client_id, fiscal_year, gross_profit, other_income, admin_expenses, selling_expenses, financial_expenses, depreciation, net_profit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach($profitLossAccounts as $profitLossAccount) {
            $stmt->execute($profitLossAccount);
        }
        
        // Sample balance sheets
        $balanceSheets = [
            // Client 3 - Sharma Enterprises
            [$clientIds[2], '2023-2024', 2500000, 270000, 1200000, 850000, 3000000, 500000, 1250000, 70000],
            [$clientIds[2], '2022-2023', 2300000, 170000, 1100000, 780000, 2800000, 450000, 1050000, 50000],
            
            // Client 4 - Global Trading Co.
            [$clientIds[3], '2023-2024', 5000000, 800000, 3500000, 2100000, 8000000, 1200000, 2150000, 50000],
            
            // Client 5 - Kumar Industries
            [$clientIds[4], '2023-2024', 6000000, 900000, 4200000, 2500000, 9500000, 1500000, 2550000, 50000],
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO balance_sheets 
            (client_id, fiscal_year, capital, reserves, long_term_liabilities, current_liabilities, fixed_assets, investments, current_assets, misc_expenses) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach($balanceSheets as $balanceSheet) {
            $stmt->execute($balanceSheet);
        }
        
        // Sample activity logs
        $activityLogs = [
            [$clientIds[0], 'Return Filed', 'Filed ITR-1 for assessment year 2023-2024'],
            [$clientIds[1], 'Client Updated', 'Updated contact information'],
            [$clientIds[2], 'Trading Account', 'Added trading account for FY 2023-2024'],
            [$clientIds[3], 'Balance Sheet', 'Created balance sheet for FY 2023-2024'],
            [$clientIds[4], 'Return Filed', 'Filed ITR-6 for assessment year 2023-2024'],
            [$clientIds[5], 'Client Added', 'New trust client added'],
            [$clientIds[1], 'Revised Return', 'Filed revised return for AY 2022-2023'],
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO activity_logs 
            (client_id, activity_type, activity_description) 
            VALUES (?, ?, ?)");
        
        foreach($activityLogs as $activityLog) {
            $stmt->execute($activityLog);
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
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
     * Close the database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}