# Database 

## Overview
The Tax Assessment Management System uses MySQL as its database management system. The database is designed to store and manage tax-related information, client details, and various financial records.

## Configuration

### Database Connection
The database connection is configured in `config/database.php`:
```php
class Database {
    private $host = "localhost";
    private $db_name = "tax_assessment_db";
    private $username = "your_username";
    private $password = "your_password";
    public $conn;
}
```

### Connection Parameters
- Host: Database server address
- Database Name: Name of the database
- Username: Database user credentials
- Password: Database user password

## Database Schema

### Tables

#### 1. Clients
```sql
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pan VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. Tax Returns
```sql
CREATE TABLE tax_returns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pan VARCHAR(10) NOT NULL,
    assessment_year VARCHAR(9) NOT NULL,
    return_original_revised BOOLEAN DEFAULT FALSE,
    gross_income DECIMAL(15,2) NOT NULL,
    deductions DECIMAL(15,2) NOT NULL,
    taxable_income DECIMAL(15,2) NOT NULL,
    tax_paid DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pan) REFERENCES clients(pan)
);
```

#### 3. Trading Accounts
```sql
CREATE TABLE trading_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pan VARCHAR(10) NOT NULL,
    asses_year_1 VARCHAR(4) NOT NULL,
    asses_year_2 VARCHAR(4) NOT NULL,
    opening_stock DECIMAL(15,2) NOT NULL,
    purchases DECIMAL(15,2) NOT NULL,
    direct_expenses DECIMAL(15,2) NOT NULL,
    closing_stock DECIMAL(15,2) NOT NULL,
    gross_profit DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pan) REFERENCES clients(pan)
);
```

#### 4. P&L Accounts
```sql
CREATE TABLE pl_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pan VARCHAR(10) NOT NULL,
    assessment_year VARCHAR(9) NOT NULL,
    gross_profit DECIMAL(15,2) NOT NULL,
    indirect_income DECIMAL(15,2) NOT NULL,
    indirect_expenses DECIMAL(15,2) NOT NULL,
    net_profit DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pan) REFERENCES clients(pan)
);
```

#### 5. Balance Sheets
```sql
CREATE TABLE balance_sheets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pan VARCHAR(10) NOT NULL,
    assessment_year VARCHAR(9) NOT NULL,
    assets TEXT NOT NULL,
    liabilities TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pan) REFERENCES clients(pan)
);
```

## Relationships

### Foreign Keys
- All tables reference the `clients` table through the `pan` field
- This ensures referential integrity and data consistency

### Indexes
- Primary keys on all tables
- Unique index on client PAN
- Indexes on frequently queried fields

## Data Types

### Numeric Types
- DECIMAL(15,2) for monetary values
- INT for IDs and counts
- BOOLEAN for flags

### String Types
- VARCHAR for fixed-length strings
- TEXT for variable-length text

### Date/Time Types
- TIMESTAMP for created_at and updated_at fields

## Security Considerations

### Data Protection
- Passwords are hashed
- Sensitive data is encrypted
- Regular backups are maintained

### Access Control
- Role-based access control
- User permissions management
- Audit logging

## Backup and Recovery

### Backup Procedures
1. Daily automated backups
2. Weekly full backups
3. Monthly archive backups

### Recovery Procedures
1. Point-in-time recovery
2. Full database restore
3. Selective data recovery

## Performance Optimization

### Indexing Strategy
- Primary keys on all tables
- Foreign key indexes
- Composite indexes for common queries

### Query Optimization
- Prepared statements
- Efficient joins
- Proper indexing

## Maintenance

### Regular Tasks
1. Database optimization
2. Index maintenance
3. Statistics updates
4. Log rotation

### Monitoring
1. Performance metrics
2. Error logging
3. Resource usage
4. Query performance 
