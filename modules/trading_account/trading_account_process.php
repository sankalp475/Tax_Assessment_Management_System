<?php
/**
 * Trading Account Process
 * 
 * This file handles all the processing for trading account management operations.
 */

session_start();
require_once '../../config/database.php';

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Check if the form is submitted or action is provided through GET
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// Process based on action
switch ($action) {
    case 'add':
        addTradingAccount();
        break;
        
    case 'edit':
        editTradingAccount();
        break;
        
    case 'delete':
        deleteTradingAccount();
        break;
        
    default:
        // Redirect back to trading account list if no valid action
        $_SESSION['error'] = "Invalid action specified.";
        header("Location: trading_account.php");
        exit;
}

/**
 * Add a new trading account
 */
function addTradingAccount() {
    global $conn, $db;
    
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Validate required fields
        $requiredFields = ['client_id', 'financial_year', 'opening_stock', 'closing_stock', 'sales', 'purchases', 'direct_expenses', 'gross_profit_loss'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field])) {
                throw new Exception("Required field missing: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate client ID
        $clientId = intval($_POST['client_id']);
        if ($clientId <= 0) {
            throw new Exception("Invalid client selected.");
        }
        
        // Check if client exists and is a firm (not individual)
        $stmt = $conn->prepare("SELECT id, name, client_type FROM clients WHERE id = ? AND client_type != 'individual'");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Selected client does not exist or is not a firm client.");
        }
        
        $client = $result->fetch_assoc();
        
        // Check if a trading account already exists for this client and financial year
        $financialYear = $db->escapeString(trim($_POST['financial_year']));
        
        $stmt = $conn->prepare("SELECT id FROM trading_accounts WHERE client_id = ? AND financial_year = ?");
        $stmt->bind_param("is", $clientId, $financialYear);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("A trading account already exists for client {$client['name']} for financial year {$financialYear}.");
        }
        
        // Collect and sanitize form data
        $openingStock = floatval($_POST['opening_stock']);
        $closingStock = floatval($_POST['closing_stock']);
        $sales = floatval($_POST['sales']);
        $purchases = floatval($_POST['purchases']);
        $directExpenses = floatval($_POST['direct_expenses']);
        $grossProfitLoss = floatval($_POST['gross_profit_loss']);
        $notes = isset($_POST['notes']) ? $db->escapeString(trim($_POST['notes'])) : null;
        
        // Process sales details
        $salesDetails = [];
        if (isset($_POST['sales_description']) && isset($_POST['sales_amount'])) {
            $salesDescriptions = $_POST['sales_description'];
            $salesAmounts = $_POST['sales_amount'];
            
            for ($i = 0; $i < count($salesDescriptions); $i++) {
                if (!empty($salesDescriptions[$i]) || floatval($salesAmounts[$i]) > 0) {
                    $salesDetails[] = [
                        'description' => $db->escapeString(trim($salesDescriptions[$i])),
                        'amount' => floatval($salesAmounts[$i])
                    ];
                }
            }
        }
        
        // Process purchases details
        $purchasesDetails = [];
        if (isset($_POST['purchases_description']) && isset($_POST['purchases_amount'])) {
            $purchasesDescriptions = $_POST['purchases_description'];
            $purchasesAmounts = $_POST['purchases_amount'];
            
            for ($i = 0; $i < count($purchasesDescriptions); $i++) {
                if (!empty($purchasesDescriptions[$i]) || floatval($purchasesAmounts[$i]) > 0) {
                    $purchasesDetails[] = [
                        'description' => $db->escapeString(trim($purchasesDescriptions[$i])),
                        'amount' => floatval($purchasesAmounts[$i])
                    ];
                }
            }
        }
        
        // Process direct expenses details
        $directExpensesDetails = [];
        if (isset($_POST['direct_expenses_description']) && isset($_POST['direct_expenses_amount'])) {
            $directExpensesDescriptions = $_POST['direct_expenses_description'];
            $directExpensesAmounts = $_POST['direct_expenses_amount'];
            
            for ($i = 0; $i < count($directExpensesDescriptions); $i++) {
                if (!empty($directExpensesDescriptions[$i]) || floatval($directExpensesAmounts[$i]) > 0) {
                    $directExpensesDetails[] = [
                        'description' => $db->escapeString(trim($directExpensesDescriptions[$i])),
                        'amount' => floatval($directExpensesAmounts[$i])
                    ];
                }
            }
        }
        
        // Convert arrays to JSON
        $salesDetailsJson = json_encode($salesDetails);
        $purchasesDetailsJson = json_encode($purchasesDetails);
        $directExpensesDetailsJson = json_encode($directExpensesDetails);
        
        // Start transaction
        $conn->begin_transaction();
        
        // Insert trading account record
        $stmt = $conn->prepare("
            INSERT INTO trading_accounts (
                client_id, financial_year, opening_stock, closing_stock, 
                sales, purchases, direct_expenses, gross_profit_loss,
                sales_details, purchases_details, direct_expenses_details, 
                notes, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        
        $stmt->bind_param(
            "isddddddssss",
            $clientId,
            $financialYear,
            $openingStock,
            $closingStock,
            $sales,
            $purchases,
            $directExpenses,
            $grossProfitLoss,
            $salesDetailsJson,
            $purchasesDetailsJson,
            $directExpensesDetailsJson,
            $notes
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to add trading account: " . $conn->error);
        }
        
        $accountId = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'success',
                'message' => 'Trading account added successfully.',
                'redirect' => 'trading_account.php?action=view&id=' . $accountId
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Trading account added successfully.";
            header("Location: trading_account.php?action=view&id=" . $accountId);
            exit;
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['error'] = $e->getMessage();
            header("Location: trading_account.php?action=add");
            exit;
        }
    }
}

/**
 * Edit an existing trading account
 */
function editTradingAccount() {
    global $conn, $db;
    
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Validate required fields
        $requiredFields = ['id', 'client_id', 'financial_year', 'opening_stock', 'closing_stock', 'sales', 'purchases', 'direct_expenses', 'gross_profit_loss'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field])) {
                throw new Exception("Required field missing: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        $accountId = intval($_POST['id']);
        $clientId = intval($_POST['client_id']);
        $financialYear = $db->escapeString(trim($_POST['financial_year']));
        
        // Check if trading account exists
        $stmt = $conn->prepare("SELECT * FROM trading_accounts WHERE id = ?");
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Trading account not found.");
        }
        
        $existingAccount = $result->fetch_assoc();
        
        // Check if changing financial year would create a duplicate
        if ($existingAccount['financial_year'] != $financialYear || $existingAccount['client_id'] != $clientId) {
            $stmt = $conn->prepare("
                SELECT id FROM trading_accounts 
                WHERE client_id = ? AND financial_year = ? AND id != ?
            ");
            $stmt->bind_param("isi", $clientId, $financialYear, $accountId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception("A trading account already exists for this client and financial year.");
            }
        }
        
        // Collect and sanitize form data
        $openingStock = floatval($_POST['opening_stock']);
        $closingStock = floatval($_POST['closing_stock']);
        $sales = floatval($_POST['sales']);
        $purchases = floatval($_POST['purchases']);
        $directExpenses = floatval($_POST['direct_expenses']);
        $grossProfitLoss = floatval($_POST['gross_profit_loss']);
        $notes = isset($_POST['notes']) ? $db->escapeString(trim($_POST['notes'])) : null;
        
        // Process sales details
        $salesDetails = [];
        if (isset($_POST['sales_description']) && isset($_POST['sales_amount'])) {
            $salesDescriptions = $_POST['sales_description'];
            $salesAmounts = $_POST['sales_amount'];
            
            for ($i = 0; $i < count($salesDescriptions); $i++) {
                if (!empty($salesDescriptions[$i]) || floatval($salesAmounts[$i]) > 0) {
                    $salesDetails[] = [
                        'description' => $db->escapeString(trim($salesDescriptions[$i])),
                        'amount' => floatval($salesAmounts[$i])
                    ];
                }
            }
        }
        
        // Process purchases details
        $purchasesDetails = [];
        if (isset($_POST['purchases_description']) && isset($_POST['purchases_amount'])) {
            $purchasesDescriptions = $_POST['purchases_description'];
            $purchasesAmounts = $_POST['purchases_amount'];
            
            for ($i = 0; $i < count($purchasesDescriptions); $i++) {
                if (!empty($purchasesDescriptions[$i]) || floatval($purchasesAmounts[$i]) > 0) {
                    $purchasesDetails[] = [
                        'description' => $db->escapeString(trim($purchasesDescriptions[$i])),
                        'amount' => floatval($purchasesAmounts[$i])
                    ];
                }
            }
        }
        
        // Process direct expenses details
        $directExpensesDetails = [];
        if (isset($_POST['direct_expenses_description']) && isset($_POST['direct_expenses_amount'])) {
            $directExpensesDescriptions = $_POST['direct_expenses_description'];
            $directExpensesAmounts = $_POST['direct_expenses_amount'];
            
            for ($i = 0; $i < count($directExpensesDescriptions); $i++) {
                if (!empty($directExpensesDescriptions[$i]) || floatval($directExpensesAmounts[$i]) > 0) {
                    $directExpensesDetails[] = [
                        'description' => $db->escapeString(trim($directExpensesDescriptions[$i])),
                        'amount' => floatval($directExpensesAmounts[$i])
                    ];
                }
            }
        }
        
        // Convert arrays to JSON
        $salesDetailsJson = json_encode($salesDetails);
        $purchasesDetailsJson = json_encode($purchasesDetails);
        $directExpensesDetailsJson = json_encode($directExpensesDetails);
        
        // Start transaction
        $conn->begin_transaction();
        
        // Update trading account record
        $stmt = $conn->prepare("
            UPDATE trading_accounts SET
                client_id = ?,
                financial_year = ?,
                opening_stock = ?,
                closing_stock = ?,
                sales = ?,
                purchases = ?,
                direct_expenses = ?,
                gross_profit_loss = ?,
                sales_details = ?,
                purchases_details = ?,
                direct_expenses_details = ?,
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "isdddddssssi",
            $clientId,
            $financialYear,
            $openingStock,
            $closingStock,
            $sales,
            $purchases,
            $directExpenses,
            $grossProfitLoss,
            $salesDetailsJson,
            $purchasesDetailsJson,
            $directExpensesDetailsJson,
            $notes,
            $accountId
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to update trading account: " . $conn->error);
        }
        
        // Check if there's a related profit and loss account that needs to be updated
        $stmt = $conn->prepare("
            SELECT id FROM profit_loss_accounts 
            WHERE client_id = ? AND financial_year = ?
        ");
        $stmt->bind_param("is", $clientId, $financialYear);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $plAccount = $result->fetch_assoc();
            
            // Update the gross profit/loss in the profit & loss account
            $stmt = $conn->prepare("
                UPDATE profit_loss_accounts SET
                    gross_profit_loss = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("di", $grossProfitLoss, $plAccount['id']);
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'success',
                'message' => 'Trading account updated successfully.',
                'redirect' => 'trading_account.php?action=view&id=' . $accountId
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Trading account updated successfully.";
            header("Location: trading_account.php?action=view&id=" . $accountId);
            exit;
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['error'] = $e->getMessage();
            header("Location: trading_account.php?action=edit&id=" . (isset($_POST['id']) ? intval($_POST['id']) : 0));
            exit;
        }
    }
}

/**
 * Delete a trading account
 */
function deleteTradingAccount() {
    global $conn;
    
    try {
        // Check if trading account ID is provided
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("Invalid trading account ID.");
        }
        
        $accountId = intval($_GET['id']);
        
        // Check if trading account exists
        $stmt = $conn->prepare("
            SELECT ta.*, c.name as client_name, c.client_type
            FROM trading_accounts ta
            JOIN clients c ON ta.client_id = c.id
            WHERE ta.id = ?
        ");
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Trading account not found.");
        }
        
        $account = $result->fetch_assoc();
        
        // Check if there are dependent profit and loss accounts
        $stmt = $conn->prepare("
            SELECT id FROM profit_loss_accounts 
            WHERE client_id = ? AND financial_year = ?
        ");
        $stmt->bind_param("is", $account['client_id'], $account['financial_year']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Cannot delete this trading account because there are related profit & loss accounts. Please delete those records first.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Delete trading account
        $stmt = $conn->prepare("DELETE FROM trading_accounts WHERE id = ?");
        $stmt->bind_param("i", $accountId);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to delete trading account: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Trading account deleted successfully.";
        header("Location: trading_account.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['error'] = $e->getMessage();
        header("Location: trading_account.php");
        exit;
    }
}

