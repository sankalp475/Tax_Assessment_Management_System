<?php
/**
 * Client Process
 * 
 * This file handles all the processing for client management operations.
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
        addClient();
        break;
        
    case 'edit':
        editClient();
        break;
        
    case 'delete':
        deleteClient();
        break;
        
    default:
        // Redirect back to client list if no valid action
        $_SESSION['error'] = "Invalid action specified.";
        header("Location: client.php");
        exit;
}

/**
 * Add a new client
 */
function addClient() {
    global $conn, $db;
    
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Validate required fields
        $requiredFields = ['pan', 'name', 'client_type'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field missing: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        // Validate PAN format (5 uppercase letters followed by 4 digits and 1 uppercase letter)
        $pan = strtoupper(trim($_POST['pan']));
        if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
            throw new Exception("Invalid PAN format. It should be 5 letters followed by 4 digits and 1 letter (e.g., ABCDE1234F).");
        }
        
        // Check if PAN already exists
        $stmt = $conn->prepare("SELECT id FROM clients WHERE pan = ?");
        $stmt->bind_param("s", $pan);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("A client with this PAN already exists.");
        }
        
        // Collect and sanitize form data
        $name = $db->escapeString(trim($_POST['name']));
        $clientType = $db->escapeString(trim($_POST['client_type']));
        $fatherName = isset($_POST['father_name']) ? $db->escapeString(trim($_POST['father_name'])) : null;
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $gender = isset($_POST['gender']) ? $db->escapeString(trim($_POST['gender'])) : null;
        $registrationNumber = isset($_POST['registration_number']) ? $db->escapeString(trim($_POST['registration_number'])) : null;
        $incorporationDate = !empty($_POST['incorporation_date']) ? $_POST['incorporation_date'] : null;
        $email = isset($_POST['email']) ? $db->escapeString(trim($_POST['email'])) : null;
        $phone = isset($_POST['phone']) ? $db->escapeString(trim($_POST['phone'])) : null;
        $address = isset($_POST['address']) ? $db->escapeString(trim($_POST['address'])) : null;
        $notes = isset($_POST['notes']) ? $db->escapeString(trim($_POST['notes'])) : null;
        
        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Insert client record
        $stmt = $conn->prepare("
            INSERT INTO clients (
                pan, name, client_type, father_name, dob, gender,
                registration_number, incorporation_date, email, phone,
                address, notes, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        
        $stmt->bind_param(
            "ssssssssssss",
            $pan,
            $name,
            $clientType,
            $fatherName,
            $dob,
            $gender,
            $registrationNumber,
            $incorporationDate,
            $email,
            $phone,
            $address,
            $notes
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to add client: " . $conn->error);
        }
        
        $clientId = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'success',
                'message' => 'Client added successfully.',
                'redirect' => 'client.php?action=view&id=' . $clientId
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Client added successfully.";
            header("Location: client.php?action=view&id=" . $clientId);
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
            header("Location: client.php?action=add");
            exit;
        }
    }
}

/**
 * Edit an existing client
 */
function editClient() {
    global $conn, $db;
    
    // Check if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Validate required fields
        $requiredFields = ['id', 'name', 'client_type'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field missing: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        $clientId = intval($_POST['id']);
        
        // Check if client exists
        $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Client not found.");
        }
        
        // Get existing client data
        $existingClient = $result->fetch_assoc();
        
        // Collect and sanitize form data
        $name = $db->escapeString(trim($_POST['name']));
        $clientType = $db->escapeString(trim($_POST['client_type']));
        $fatherName = isset($_POST['father_name']) ? $db->escapeString(trim($_POST['father_name'])) : null;
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $gender = isset($_POST['gender']) ? $db->escapeString(trim($_POST['gender'])) : null;
        $registrationNumber = isset($_POST['registration_number']) ? $db->escapeString(trim($_POST['registration_number'])) : null;
        $incorporationDate = !empty($_POST['incorporation_date']) ? $_POST['incorporation_date'] : null;
        $email = isset($_POST['email']) ? $db->escapeString(trim($_POST['email'])) : null;
        $phone = isset($_POST['phone']) ? $db->escapeString(trim($_POST['phone'])) : null;
        $address = isset($_POST['address']) ? $db->escapeString(trim($_POST['address'])) : null;
        $notes = isset($_POST['notes']) ? $db->escapeString(trim($_POST['notes'])) : null;
        
        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Update client record
        $stmt = $conn->prepare("
            UPDATE clients SET
                name = ?,
                client_type = ?,
                father_name = ?,
                dob = ?,
                gender = ?,
                registration_number = ?,
                incorporation_date = ?,
                email = ?,
                phone = ?,
                address = ?,
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "sssssssssssi",
            $name,
            $clientType,
            $fatherName,
            $dob,
            $gender,
            $registrationNumber,
            $incorporationDate,
            $email,
            $phone,
            $address,
            $notes,
            $clientId
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to update client: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return response based on request type
        if ($isAjax) {
            $response = [
                'status' => 'success',
                'message' => 'Client updated successfully.',
                'redirect' => 'client.php?action=view&id=' . $clientId
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Client updated successfully.";
            header("Location: client.php?action=view&id=" . $clientId);
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
            header("Location: client.php?action=edit&id=" . (isset($_POST['id']) ? intval($_POST['id']) : 0));
            exit;
        }
    }
}

/**
 * Delete a client
 */
function deleteClient() {
    global $conn;
    
    try {
        // Check if client ID is provided
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("Invalid client ID.");
        }
        
        $clientId = intval($_GET['id']);
        
        // Check if client exists
        $stmt = $conn->prepare("SELECT id FROM clients WHERE id = ?");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Client not found.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Check for related records
        $tables = [
            'returns' => 'returns',
            'revised_returns' => 'revised returns',
            'trading_accounts' => 'trading accounts',
            'profit_loss_accounts' => 'profit & loss accounts',
            'balance_sheets' => 'balance sheets'
        ];
        
        foreach ($tables as $table => $displayName) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table WHERE client_id = ?");
            $stmt->bind_param("i", $clientId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                throw new Exception("Cannot delete client because there are related $displayName. Please delete those records first.");
            }
        }
        
        // Delete client
        $stmt = $conn->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->bind_param("i", $clientId);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to delete client: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Client deleted successfully.";
        header("Location: client.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['error'] = $e->getMessage();
        header("Location: client.php");
        exit;
    }
}
