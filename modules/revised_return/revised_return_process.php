<?php
/**
 * Revised Return Process
 * 
 * This file handles all the processing for revised return management operations.
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
        addRevisedReturn();
        break;
        
    case 'edit':
        editRevisedReturn();
        break;
        
    case 'delete':
        deleteRevisedReturn();
        break;
        
    default:
        // Redirect back to revised return list if no valid action
        $_SESSION['error'] = "Invalid action specified.";
        header("Location: revised_return.php");
        exit;
}

/**
 * Add a new revised return
 */
function addRevisedReturn() {
    global $conn, $db;
    
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Validate required fields
        $requiredFields = ['original_return_id', 'return_type', 'filing_date', 'total_income', 'tax_payable', 'acknowledgement_no', 'filing_type', 'revision_reason'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field missing: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate original return ID
        $originalReturnId = intval($_POST['original_return_id']);
        if ($originalReturnId <= 0) {
            throw new Exception("Invalid original return selected.");
        }
        
        // Check if original return exists
        $stmt = $conn->prepare("
            SELECT r.*, c.name as client_name 
            FROM returns r
            JOIN clients c ON r.client_id = c.id
            WHERE r.id = ?
        ");
        $stmt->bind_param("i", $originalReturnId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Original return not found.");
        }
        
        $originalReturn = $result->fetch_assoc();
        
        // Check if a revised return already exists for this original return
        $stmt = $conn->prepare("SELECT id FROM revised_returns WHERE original_return_id = ?");
        $stmt->bind_param("i", $originalReturnId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("A revised return already exists for this original return.");
        }
        
        // Collect and sanitize form data
        $returnType = $db->escapeString(trim($_POST['return_type']));
        $filingDate = date('Y-m-d', strtotime($_POST['filing_date']));
        $totalIncome = floatval($_POST['total_income']);
        $taxPayable = floatval($_POST['tax_payable']);
        $acknowledgementNo = $db->escapeString(trim($_POST['acknowledgement_no']));
        $filingType = $db->escapeString(trim($_POST['filing_type']));
        $revisionReason = $db->escapeString(trim($_POST['revision_reason']));
        $taxPaidDate = !empty($_POST['tax_paid_date']) ? date('Y-m-d', strtotime($_POST['tax_paid_date'])) : null;
        $challanNo = isset($_POST['challan_no']) ? $db->escapeString(trim($_POST['challan_no'])) : null;
        $additionalTax = isset($_POST['additional_tax']) ? floatval($_POST['additional_tax']) : 0;
        $remarks = isset($_POST['remarks']) ? $db->escapeString(trim($_POST['remarks'])) : null;
        
        // Validate numeric fields
        if ($totalIncome < 0) {
            throw new Exception("Total income cannot be negative.");
        }
        
        if ($taxPayable < 0) {
            throw new Exception("Tax payable cannot be negative.");
        }
        
        if ($additionalTax < 0) {
            throw new Exception("Additional tax cannot be negative.");
        }
        
        // Validate filing date is after original return's filing date
        if (strtotime($filingDate) <= strtotime($originalReturn['filing_date'])) {
            throw new Exception("Revised return filing date must be after the original return filing date.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Insert revised return record
        $stmt = $conn->prepare("
            INSERT INTO revised_returns (
                original_return_id, return_type, filing_date, total_income, 
                tax_payable, acknowledgement_no, filing_type, revision_reason,
                tax_paid_date, challan_no, additional_tax, remarks, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        
        $stmt->bind_param(
            "issddsssssds",
            $originalReturnId,
            $returnType,
            $filingDate,
            $totalIncome,
            $taxPayable,
            $acknowledgementNo,
            $filingType,
            $revisionReason,
            $taxPaidDate,
            $challanNo,
            $additionalTax,
            $remarks
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to add revised return: " . $conn->error);
        }
        
        $revisedReturnId = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'success',
                'message' => 'Revised return filed successfully.',
                'redirect' => 'revised_return.php?action=view&id=' . $revisedReturnId
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Revised return filed successfully.";
            header("Location: revised_return.php?action=view&id=" . $revisedReturnId);
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
            header("Location: revised_return.php?action=add&original_id=" . (isset($_POST['original_return_id']) ? intval($_POST['original_return_id']) : 0));
            exit;
        }
    }
}

/**
 * Edit an existing revised return
 */
function editRevisedReturn() {
    global $conn, $db;
    
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Validate required fields
        $requiredFields = ['id', 'original_return_id', 'return_type', 'filing_date', 'total_income', 'tax_payable', 'acknowledgement_no', 'filing_type', 'revision_reason'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field missing: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        $revisedReturnId = intval($_POST['id']);
        $originalReturnId = intval($_POST['original_return_id']);
        
        // Check if revised return exists
        $stmt = $conn->prepare("SELECT * FROM revised_returns WHERE id = ?");
        $stmt->bind_param("i", $revisedReturnId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Revised return not found.");
        }
        
        // Check if original return exists
        $stmt = $conn->prepare("SELECT * FROM returns WHERE id = ?");
        $stmt->bind_param("i", $originalReturnId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Original return not found.");
        }
        
        $originalReturn = $result->fetch_assoc();
        
        // Collect and sanitize form data
        $returnType = $db->escapeString(trim($_POST['return_type']));
        $filingDate = date('Y-m-d', strtotime($_POST['filing_date']));
        $totalIncome = floatval($_POST['total_income']);
        $taxPayable = floatval($_POST['tax_payable']);
        $acknowledgementNo = $db->escapeString(trim($_POST['acknowledgement_no']));
        $filingType = $db->escapeString(trim($_POST['filing_type']));
        $revisionReason = $db->escapeString(trim($_POST['revision_reason']));
        $taxPaidDate = !empty($_POST['tax_paid_date']) ? date('Y-m-d', strtotime($_POST['tax_paid_date'])) : null;
        $challanNo = isset($_POST['challan_no']) ? $db->escapeString(trim($_POST['challan_no'])) : null;
        $additionalTax = isset($_POST['additional_tax']) ? floatval($_POST['additional_tax']) : 0;
        $remarks = isset($_POST['remarks']) ? $db->escapeString(trim($_POST['remarks'])) : null;
        
        // Validate numeric fields
        if ($totalIncome < 0) {
            throw new Exception("Total income cannot be negative.");
        }
        
        if ($taxPayable < 0) {
            throw new Exception("Tax payable cannot be negative.");
        }
        
        if ($additionalTax < 0) {
            throw new Exception("Additional tax cannot be negative.");
        }
        
        // Validate filing date is after original return's filing date
        if (strtotime($filingDate) <= strtotime($originalReturn['filing_date'])) {
            throw new Exception("Revised return filing date must be after the original return filing date.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Update revised return record
        $stmt = $conn->prepare("
            UPDATE revised_returns SET
                return_type = ?,
                filing_date = ?,
                total_income = ?,
                tax_payable = ?,
                acknowledgement_no = ?,
                filing_type = ?,
                revision_reason = ?,
                tax_paid_date = ?,
                challan_no = ?,
                additional_tax = ?,
                remarks = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "ssddsssssds i",
            $returnType,
            $filingDate,
            $totalIncome,
            $taxPayable,
            $acknowledgementNo,
            $filingType,
            $revisionReason,
            $taxPaidDate,
            $challanNo,
            $additionalTax,
            $remarks,
            $revisedReturnId
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to update revised return: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'success',
                'message' => 'Revised return updated successfully.',
                'redirect' => 'revised_return.php?action=view&id=' . $revisedReturnId
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Revised return updated successfully.";
            header("Location: revised_return.php?action=view&id=" . $revisedReturnId);
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
            header("Location: revised_return.php?action=edit&id=" . (isset($_POST['id']) ? intval($_POST['id']) : 0));
            exit;
        }
    }
}

/**
 * Delete a revised return
 */
function deleteRevisedReturn() {
    global $conn;
    
    try {
        // Check if revised return ID is provided
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("Invalid revised return ID.");
        }
        
        $revisedReturnId = intval($_GET['id']);
        
        // Check if revised return exists
        $stmt = $conn->prepare("
            SELECT rr.*, r.id as original_id
            FROM revised_returns rr
            JOIN returns r ON rr.original_return_id = r.id
            WHERE rr.id = ?
        ");
        $stmt->bind_param("i", $revisedReturnId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Revised return not found.");
        }
        
        $revisedReturn = $result->fetch_assoc();
        $originalReturnId = $revisedReturn['original_id'];
        
        // Start transaction
        $conn->begin_transaction();
        
        // Delete revised return
        $stmt = $conn->prepare("DELETE FROM revised_returns WHERE id = ?");
        $stmt->bind_param("i", $revisedReturnId);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to delete revised return: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Revised return deleted successfully.";
        header("Location: ../original_return/original_return.php?action=view&id=" . $originalReturnId);
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['error'] = $e->getMessage();
        header("Location: revised_return.php");
        exit;
    }
}
