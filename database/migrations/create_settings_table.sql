CREATE TABLE IF NOT EXISTS SETTINGS (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    COMPANY_NAME VARCHAR(255),
    COMPANY_ADDRESS TEXT,
    COMPANY_PHONE VARCHAR(50),
    COMPANY_EMAIL VARCHAR(255),
    TAX_YEAR_START DATE,
    TAX_YEAR_END DATE,
    CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 
