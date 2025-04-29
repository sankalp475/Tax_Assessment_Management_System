<?php
/**
 * Original Return Process
 * 
 * This file handles all the processing for original return management operations.
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
        addReturn();
        break;
        
    case 'edit':
        editReturn();
        break;
        
    case 'delete':
        deleteReturn();
        break;
        
    default:
        // Redirect back to return list if no valid action
        $_SESSION['error'] = "Invalid action specified.";
        header("Location: original_return.php");
        exit;
}

/**
 * Add a new return
 */
function addReturn() {
    global $conn, $db;
    
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Validate required fields
        $requiredFields = ['client_id', 'assessment_year', 'return_type', 'filing_date', 'total_income', 'tax_payable', 'acknowledgement_no', 'filing_type'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field missing: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate client ID
        $clientId = intval($_POST['client_id']);
        if ($clientId <= 0) {
            throw new Exception("Invalid client selected.");
        }
        
        // Check if client exists
        $stmt = $conn->prepare("SELECT id, name FROM clients WHERE id = ?");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Selected client does not exist.");
        }
        
        $client = $result->fetch_assoc();
        
        // Check if a return already exists for this client and assessment year
        $assessmentYear = $db->escapeString(trim($_POST['assessment_year']));
        
        $stmt = $conn->prepare("SELECT id FROM returns WHERE client_id = ? AND assessment_year = ?");
        $stmt->bind_param("is", $clientId, $assessmentYear);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("A return already exists for client {$client['name']} for assessment year {$assessmentYear}.");
        }
        
        // Collect and sanitize form data
        $returnType = $db->escapeString(trim($_POST['return_type']));
        $filingDate = date('Y-m-d', strtotime($_POST['filing_date']));
        $totalIncome = floatval($_POST['total_income']);
        $taxPayable = floatval($_POST['tax_payable']);
        $acknowledgementNo = $db->escapeString(trim($_POST['acknowledgement_no']));
        $filingType = $db->escapeString(trim($_POST['filing_type']));
        $taxPaidDate = !empty($_POST['tax_paid_date']) ? date('Y-m-d', strtotime($_POST['tax_paid_date'])) : null;
        $challanNo = isset($_POST['challan_no']) ? $db->escapeString(trim($_POST['challan_no'])) : null;
        $remarks = isset($_POST['remarks']) ? $db->escapeString(trim($_POST['remarks'])) : null;
        
        // Validate numeric fields
        if ($totalIncome < 0) {
            throw new Exception("Total income cannot be negative.");
        }
        
        if ($taxPayable < 0) {
            throw new Exception("Tax payable cannot be negative.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Insert return record
        $stmt = $conn->prepare("
            INSERT INTO returns (
                client_id, assessment_year, return_type, filing_date, total_income, 
                tax_payable, acknowledgement_no, filing_type, tax_paid_date, 
                challan_no, remarks, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        
        $stmt->bind_param(
            "isssddssss",
            $clientId,
            $assessmentYear,
            $returnType,
            $filingDate,
            $totalIncome,
            $taxPayable,
            $acknowledgementNo,
            $filingType,
            $taxPaidDate,
            $challanNo,
            $remarks
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to add return: " . $conn->error);
        }
        
        $returnId = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'success',
                'message' => 'Return filed successfully.',
                'redirect' => 'original_return.php?action=view&id=' . $returnId
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Return filed successfully.";
            header("Location: original_return.php?action=view&id=" . $returnId);
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
            header("Location: original_return.php?action=add");
            exit;
        }
    }
}

/**
 * Edit an existing return
 */
function editReturn() {
    global $conn, $db;
    
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Validate required fields
        $requiredFields = ['id', 'assessment_year', 'return_type', 'filing_date', 'total_income', 'tax_payable', 'acknowledgement_no', 'filing_type'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field missing: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        $returnId = intval($_POST['id']);
        
        // Check if return exists and if it can be edited (not revised)
        $stmt = $conn->prepare("
            SELECT r.*, (SELECT COUNT(*) FROM revised_returns WHERE original_return_id = r.id) as has_revised
            FROM returns r
            WHERE r.id = ?
        ");
        $stmt->bind_param("i", $returnId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Return not found.");
        }
        
        $return = $result->fetch_assoc();
        
        if ($return['has_revised'] > 0) {
            throw new Exception("This return cannot be edited because it has been revised.");
        }
        
        // Collect and sanitize form data
        $assessmentYear = $db->escapeString(trim($_POST['assessment_year']));
        $returnType = $db->escapeString(trim($_POST['return_type']));
        $filingDate = date('Y-m-d', strtotime($_POST['filing_date']));
        $totalIncome = floatval($_POST['total_income']);
        $taxPayable = floatval($_POST['tax_payable']);
        $acknowledgementNo = $db->escapeString(trim($_POST['acknowledgement_no']));
        $filingType = $db->escapeString(trim($_POST['filing_type']));
        $taxPaidDate = !empty($_POST['tax_paid_date']) ? date('Y-m-d', strtotime($_POST['tax_paid_date'])) : null;
        $challanNo = isset($_POST['challan_no']) ? $db->escapeString(trim($_POST['challan_no'])) : null;
        $remarks = isset($_POST['remarks']) ? $db->escapeString(trim($_POST['remarks'])) : null;
        
        // Validate numeric fields
        if ($totalIncome < 0) {
            throw new Exception("Total income cannot be negative.");
        }
        
        if ($taxPayable < 0) {
            throw new Exception("Tax payable cannot be negative.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Update return record
        $stmt = $conn->prepare("
            UPDATE returns SET
                assessment_year = ?,
                return_type = ?,
                filing_date = ?,
                total_income = ?,
                tax_payable = ?,
                acknowledgement_no = ?,
                filing_type = ?,
                tax_paid_date = ?,
                challan_no = ?,
                remarks = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "sssddssssi",
            $assessmentYear,
            $returnType,
            $filingDate,
            $totalIncome,
            $taxPayable,
            $acknowledgementNo,
            $filingType,
            $taxPaidDate,
            $challanNo,
            $remarks,
            $returnId
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to update return: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'success',
                'message' => 'Return updated successfully.',
                'redirect' => 'original_return.php?action=view&id=' . $returnId
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Return updated successfully.";
            header("Location: original_return.php?action=view&id=" . $returnId);
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
            header("Location: original_return.php?action=edit&id=" . (isset($_POST['id']) ? intval($_POST['id']) : 0));
            exit;
        }
    }
}

/**
 * Delete a return
 */
function deleteReturn() {
    global $conn;
    
    try {
        // Check if return ID is provided
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("Invalid return ID.");
        }
        
        $returnId = intval($_GET['id']);
        
        // Check if return exists and if it can be deleted (not revised)
        $stmt = $conn->prepare("
            SELECT r.*, (SELECT COUNT(*) FROM revised_returns WHERE original_return_id = r.id) as has_revised
            FROM returns r
            WHERE r.id = ?
        ");
        $stmt->bind_param("i", $returnId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Return not found.");
        }
        
        $return = $result->fetch_assoc();
        
        if ($return['has_revised'] > 0) {
            throw new Exception("This return cannot be deleted because it has been revised. Please delete the revised return first.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Delete return
        $stmt = $conn->prepare("DELETE FROM returns WHERE id = ?");
        $stmt->bind_param("i", $returnId);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to delete return: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Return deleted successfully.";
        header("Location: original_return.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['error'] = $e->getMessage();
        header("Location: original_return.php");
        exit;
    }
}
