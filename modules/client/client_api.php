<?php
/**
 * Client API
 * 
 * This file provides AJAX endpoints for client operations
 */

session_start();
require_once '../../config/database.php';

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Set response header
header('Content-Type: application/json');

// Check for action parameter
if (!isset($_GET['action'])) {
    sendResponse('error', 'No action specified.');
    exit;
}

// Process based on action
switch ($_GET['action']) {
    case 'search':
        searchClients();
        break;
        
    case 'get_client':
        getClient();
        break;
        
    case 'get_clients_by_type':
        getClientsByType();
        break;
        
    case 'validate_pan':
        validatePAN();
        break;
        
    default:
        sendResponse('error', 'Invalid action specified.');
        break;
}

/**
 * Search clients
 */
function searchClients() {
    global $conn, $db;
    
    // Get search term
    $searchTerm = isset($_GET['term']) ? $db->escapeString($_GET['term']) : '';
    
    if (empty($searchTerm)) {
        sendResponse('error', 'Search term is required.');
        return;
    }
    
    // Search clients by name, PAN, or registration number
    $sql = "
        SELECT id, pan, name, client_type
        FROM clients
        WHERE name LIKE '%$searchTerm%' OR pan LIKE '%$searchTerm%' OR registration_number LIKE '%$searchTerm%'
        ORDER BY name
        LIMIT 10
    ";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        sendResponse('error', 'Failed to search clients: ' . $conn->error);
        return;
    }
    
    $clients = [];
    
    while ($row = $result->fetch_assoc()) {
        $clients[] = [
            'id' => $row['id'],
            'pan' => $row['pan'],
            'name' => $row['name'],
            'client_type' => $row['client_type'],
            'label' => $row['name'] . ' (' . $row['pan'] . ')',
            'value' => $row['name'] . ' (' . $row['pan'] . ')'
        ];
    }
    
    echo json_encode($clients);
}

/**
 * Get client details by ID
 */
function getClient() {
    global $conn;
    
    // Get client ID
    $clientId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($clientId <= 0) {
        sendResponse('error', 'Invalid client ID.');
        return;
    }
    
    // Get client data
    $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse('error', 'Client not found.');
        return;
    }
    
    $client = $result->fetch_assoc();
    
    // Format dates for display
    if (!empty($client['dob'])) {
        $client['dob_formatted'] = date('d-m-Y', strtotime($client['dob']));
    }
    
    if (!empty($client['incorporation_date'])) {
        $client['incorporation_date_formatted'] = date('d-m-Y', strtotime($client['incorporation_date']));
    }
    
    // Return client data
    sendResponse('success', 'Client retrieved successfully.', $client);
}

/**
 * Get clients by type
 */
function getClientsByType() {
    global $conn, $db;
    
    // Get client type
    $clientType = isset($_GET['type']) ? $db->escapeString($_GET['type']) : '';
    
    if (empty($clientType)) {
        sendResponse('error', 'Client type is required.');
        return;
    }
    
    // Get clients of the specified type
    $sql = "
        SELECT id, pan, name
        FROM clients
        WHERE client_type = '$clientType'
        ORDER BY name
    ";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        sendResponse('error', 'Failed to get clients: ' . $conn->error);
        return;
    }
    
    $clients = [];
    
    while ($row = $result->fetch_assoc()) {
        $clients[] = [
            'id' => $row['id'],
            'pan' => $row['pan'],
            'name' => $row['name']
        ];
    }
    
    sendResponse('success', 'Clients retrieved successfully.', $clients);
}

/**
 * Validate PAN (Permanent Account Number)
 */
function validatePAN() {
    global $conn, $db;
    
    // Get PAN
    $pan = isset($_GET['pan']) ? strtoupper(trim($_GET['pan'])) : '';
    
    if (empty($pan)) {
        sendResponse('error', 'PAN is required.');
        return;
    }
    
    // Validate PAN format
    if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
        sendResponse('error', 'Invalid PAN format. It should be 5 letters followed by 4 digits and 1 letter (e.g., ABCDE1234F).');
        return;
    }
    
    // Check if PAN exists in database
    $stmt = $conn->prepare("SELECT id, name FROM clients WHERE pan = ?");
    $stmt->bind_param("s", $pan);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exists = $result->num_rows > 0;
    
    if ($exists) {
        $client = $result->fetch_assoc();
        sendResponse('exists', 'PAN already exists in the database.', [
            'id' => $client['id'],
            'name' => $client['name']
        ]);
    } else {
        sendResponse('valid', 'PAN is valid and not in use.');
    }
}

/**
 * Send JSON response
 * 
 * @param string $status Status of the response ('success', 'error', etc.)
 * @param string $message Message to be sent
 * @param array $data Additional data to include in the response
 */
function sendResponse($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}
