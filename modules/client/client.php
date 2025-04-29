<?php
session_start();
require_once '../../config/database.php';

$pageTitle = "Client Management";
$activePage = "client";

// Initialize variables
$clients = [];
$clientTypes = ['individual', 'partnership', 'company', 'trust'];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$clientId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$client = null;

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Process the requested action
switch ($action) {
    case 'add':
        // Display add client form
        break;
        
    case 'edit':
        // Get client data for editing
        if ($clientId > 0) {
            $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
            $stmt->bind_param("i", $clientId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $client = $result->fetch_assoc();
            } else {
                $_SESSION['error'] = "Client not found.";
                header("Location: client.php");
                exit;
            }
        }
        break;
        
    case 'view':
        // Get client data for viewing
        if ($clientId > 0) {
            $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
            $stmt->bind_param("i", $clientId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $client = $result->fetch_assoc();
                
                // Get returns data for this client
                $returnsStmt = $conn->prepare("
                    SELECT r.*, 
                           CASE WHEN rr.id IS NOT NULL THEN 'Yes' ELSE 'No' END AS has_revised
                    FROM returns r
                    LEFT JOIN revised_returns rr ON r.id = rr.original_return_id
                    WHERE r.client_id = ?
                    ORDER BY r.assessment_year DESC
                ");
                $returnsStmt->bind_param("i", $clientId);
                $returnsStmt->execute();
                $returnsResult = $returnsStmt->get_result();
                $returns = [];
                
                while ($row = $returnsResult->fetch_assoc()) {
                    $returns[] = $row;
                }
                
                // Get financial data if client is a firm
                $financialData = [];
                if ($client['client_type'] != 'individual') {
                    // Get trading account data
                    $tradingStmt = $conn->prepare("
                        SELECT * FROM trading_accounts 
                        WHERE client_id = ? 
                        ORDER BY financial_year DESC
                    ");
                    $tradingStmt->bind_param("i", $clientId);
                    $tradingStmt->execute();
                    $tradingResult = $tradingStmt->get_result();
                    $financialData['trading'] = [];
                    
                    while ($row = $tradingResult->fetch_assoc()) {
                        $financialData['trading'][] = $row;
                    }
                    
                    // Get profit & loss data
                    $plStmt = $conn->prepare("
                        SELECT * FROM profit_loss_accounts 
                        WHERE client_id = ? 
                        ORDER BY financial_year DESC
                    ");
                    $plStmt->bind_param("i", $clientId);
                    $plStmt->execute();
                    $plResult = $plStmt->get_result();
                    $financialData['profit_loss'] = [];
                    
                    while ($row = $plResult->fetch_assoc()) {
                        $financialData['profit_loss'][] = $row;
                    }
                    
                    // Get balance sheet data
                    $bsStmt = $conn->prepare("
                        SELECT * FROM balance_sheets 
                        WHERE client_id = ? 
                        ORDER BY financial_year DESC
                    ");
                    $bsStmt->bind_param("i", $clientId);
                    $bsStmt->execute();
                    $bsResult = $bsStmt->get_result();
                    $financialData['balance_sheet'] = [];
                    
                    while ($row = $bsResult->fetch_assoc()) {
                        $financialData['balance_sheet'][] = $row;
                    }
                }
            } else {
                $_SESSION['error'] = "Client not found.";
                header("Location: client.php");
                exit;
            }
        }
        break;
        
    case 'delete':
        // Handle delete request (this is processed in client_process.php)
        break;
        
    case 'list':
    default:
        // Get all clients
        $searchTerm = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';
        $clientType = isset($_GET['type']) ? $db->escapeString($_GET['type']) : '';
        
        $sql = "SELECT * FROM clients WHERE 1=1";
        
        // Add search condition if specified
        if (!empty($searchTerm)) {
            $sql .= " AND (name LIKE '%$searchTerm%' OR pan LIKE '%$searchTerm%')";
        }
        
        // Add client type filter if specified
        if (!empty($clientType)) {
            $sql .= " AND client_type = '$clientType'";
        }
        
        $sql .= " ORDER BY name ASC";
        
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
        }
        break;
}

include '../../includes/header.php';
?>

<div class="container-fluid">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if ($action == 'list'): ?>
        <!-- List Clients -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Client List</h6>
                <a href="?action=add" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Client
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by name or PAN" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">All Client Types</option>
                                <?php foreach ($clientTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= (isset($_GET['type']) && $_GET['type'] == $type) ? 'selected' : '' ?>>
                                        <?= ucfirst($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                        <?php if (!empty($_GET['search']) || !empty($_GET['type'])): ?>
                            <div class="col-md-2">
                                <a href="?action=list" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="clientDataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>PAN</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($clients) > 0): ?>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($client['pan']) ?></td>
                                        <td><?= htmlspecialchars($client['name']) ?></td>
                                        <td><?= ucfirst(htmlspecialchars($client['client_type'])) ?></td>
                                        <td><?= htmlspecialchars($client['phone']) ?></td>
                                        <td><?= htmlspecialchars($client['email']) ?></td>
                                        <td>
                                            <a href="?action=view&id=<?= $client['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?action=edit&id=<?= $client['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?= $client['id'] ?>)" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No clients found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <!-- Add/Edit Client Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><?= ($action == 'add') ? 'Add New Client' : 'Edit Client' ?></h6>
            </div>
            <div class="card-body">
                <form action="client_process.php" method="POST" class="ajax-form">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $client['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="pan" class="form-label required-field">PAN (Permanent Account Number)</label>
                            <input type="text" class="form-control pan-input" id="pan" name="pan" maxlength="10" required value="<?= isset($client['pan']) ? htmlspecialchars($client['pan']) : '' ?>" <?= ($action == 'edit') ? 'readonly' : '' ?>>
                            <div class="invalid-feedback">
                                Please provide a valid PAN (e.g., ABCDE1234F).
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="clientType" class="form-label required-field">Client Type</label>
                            <select class="form-select" id="clientType" name="client_type" required>
                                <option value="">Select Client Type</option>
                                <?php foreach ($clientTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= (isset($client['client_type']) && $client['client_type'] == $type) ? 'selected' : '' ?>>
                                        <?= ucfirst($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label required-field">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required value="<?= isset($client['name']) ? htmlspecialchars($client['name']) : '' ?>">
                        </div>
                        <div class="col-md-6 individual-fields">
                            <label for="father_name" class="form-label">Father's Name</label>
                            <input type="text" class="form-control" id="father_name" name="father_name" value="<?= isset($client['father_name']) ? htmlspecialchars($client['father_name']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3 individual-fields">
                        <div class="col-md-6">
                            <label for="dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="dob" value="<?= isset($client['dob']) ? htmlspecialchars($client['dob']) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?= (isset($client['gender']) && $client['gender'] == 'male') ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= (isset($client['gender']) && $client['gender'] == 'female') ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= (isset($client['gender']) && $client['gender'] == 'other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3 firm-fields" style="display: none;">
                        <div class="col-md-6">
                            <label for="registration_number" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?= isset($client['registration_number']) ? htmlspecialchars($client['registration_number']) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="incorporation_date" class="form-label">Incorporation/Registration Date</label>
                            <input type="date" class="form-control" id="incorporation_date" name="incorporation_date" value="<?= isset($client['incorporation_date']) ? htmlspecialchars($client['incorporation_date']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= isset($client['email']) ? htmlspecialchars($client['email']) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= isset($client['phone']) ? htmlspecialchars($client['phone']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= isset($client['address']) ? htmlspecialchars($client['address']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= isset($client['notes']) ? htmlspecialchars($client['notes']) : '' ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="client.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <?= ($action == 'add') ? 'Add Client' : 'Update Client' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($action == 'view' && $client): ?>
        <!-- View Client Details -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Client Details</h6>
                <div>
                    <a href="?action=edit&id=<?= $client['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="javascript:void(0);" onclick="printClientDetails()" class="btn btn-info btn-sm">
                        <i class="fas fa-print"></i> Print
                    </a>
                    <a href="client.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body" id="client-details-print">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Basic Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">PAN</th>
                                <td><?= htmlspecialchars($client['pan']) ?></td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td><?= htmlspecialchars($client['name']) ?></td>
                            </tr>
                            <tr>
                                <th>Client Type</th>
                                <td><?= ucfirst(htmlspecialchars($client['client_type'])) ?></td>
                            </tr>
                            
                            <?php if ($client['client_type'] == 'individual'): ?>
                                <tr>
                                    <th>Father's Name</th>
                                    <td><?= htmlspecialchars($client['father_name'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Date of Birth</th>
                                    <td><?= $client['dob'] ? date('d-m-Y', strtotime($client['dob'])) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <th>Gender</th>
                                    <td><?= $client['gender'] ? ucfirst(htmlspecialchars($client['gender'])) : 'N/A' ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <th>Registration Number</th>
                                    <td><?= htmlspecialchars($client['registration_number'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Incorporation Date</th>
                                    <td><?= $client['incorporation_date'] ? date('d-m-Y', strtotime($client['incorporation_date'])) : 'N/A' ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Contact Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Email</th>
                                <td><?= htmlspecialchars($client['email'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td><?= htmlspecialchars($client['phone'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td><?= nl2br(htmlspecialchars($client['address'] ?? 'N/A')) ?></td>
                            </tr>
                            <tr>
                                <th>Notes</th>
                                <td><?= nl2br(htmlspecialchars($client['notes'] ?? 'N/A')) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h5>Returns History</h5>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Assessment Year</th>
                                <th>Return Type</th>
                                <th>Filing Date</th>
                                <th>Income</th>
                                <th>Tax Payable</th>
                                <th>Has Revised</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($returns) && count($returns) > 0): ?>
                                <?php foreach ($returns as $return): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($return['assessment_year']) ?></td>
                                        <td><?= htmlspecialchars($return['return_type']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($return['filing_date'])) ?></td>
                                        <td>₹<?= number_format($return['total_income'], 2) ?></td>
                                        <td>₹<?= number_format($return['tax_payable'], 2) ?></td>
                                        <td><?= $return['has_revised'] ?></td>
                                        <td>
                                            <a href="../original_return/original_return.php?action=view&id=<?= $return['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($return['has_revised'] == 'Yes'): ?>
                                                <a href="../revised_return/revised_return.php?action=view&original_id=<?= $return['id'] ?>" class="btn btn-warning btn-sm" title="View Revised">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="../revised_return/revised_return.php?action=add&original_id=<?= $return['id'] ?>" class="btn btn-secondary btn-sm" title="File Revised">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No returns found for this client.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($client['client_type'] != 'individual' && isset($financialData)): ?>
                    <div class="mt-4">
                        <h5>Financial Statements</h5>
                        <ul class="nav nav-tabs" id="financialTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="trading-tab" data-bs-toggle="tab" data-bs-target="#trading" type="button" role="tab" aria-controls="trading" aria-selected="true">Trading Accounts</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profit-loss-tab" data-bs-toggle="tab" data-bs-target="#profit-loss" type="button" role="tab" aria-controls="profit-loss" aria-selected="false">Profit & Loss Accounts</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="balance-sheet-tab" data-bs-toggle="tab" data-bs-target="#balance-sheet" type="button" role="tab" aria-controls="balance-sheet" aria-selected="false">Balance Sheets</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="financialTabsContent">
                            <div class="tab-pane fade show active" id="trading" role="tabpanel" aria-labelledby="trading-tab">
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Financial Year</th>
                                                <th>Opening Stock</th>
                                                <th>Purchases</th>
                                                <th>Direct Expenses</th>
                                                <th>Closing Stock</th>
                                                <th>Gross Profit/Loss</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($financialData['trading']) > 0): ?>
                                                <?php foreach ($financialData['trading'] as $trading): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($trading['financial_year']) ?></td>
                                                        <td>₹<?= number_format($trading['opening_stock'], 2) ?></td>
                                                        <td>₹<?= number_format($trading['purchases'], 2) ?></td>
                                                        <td>₹<?= number_format($trading['direct_expenses'], 2) ?></td>
                                                        <td>₹<?= number_format($trading['closing_stock'], 2) ?></td>
                                                        <td>₹<?= number_format($trading['gross_profit_loss'], 2) ?></td>
                                                        <td>
                                                            <a href="../trading_account/trading_account.php?action=view&id=<?= $trading['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../trading_account/trading_account.php?action=edit&id=<?= $trading['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No trading accounts found for this client.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="profit-loss" role="tabpanel" aria-labelledby="profit-loss-tab">
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Financial Year</th>
                                                <th>Gross Profit/Loss</th>
                                                <th>Indirect Incomes</th>
                                                <th>Indirect Expenses</th>
                                                <th>Net Profit/Loss</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($financialData['profit_loss']) > 0): ?>
                                                <?php foreach ($financialData['profit_loss'] as $pl): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($pl['financial_year']) ?></td>
                                                        <td>₹<?= number_format($pl['gross_profit_loss'], 2) ?></td>
                                                        <td>₹<?= number_format($pl['indirect_incomes'], 2) ?></td>
                                                        <td>₹<?= number_format($pl['indirect_expenses'], 2) ?></td>
                                                        <td>₹<?= number_format($pl['net_profit_loss'], 2) ?></td>
                                                        <td>
                                                            <a href="../profit_loss/profit_loss.php?action=view&id=<?= $pl['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../profit_loss/profit_loss.php?action=edit&id=<?= $pl['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No profit & loss accounts found for this client.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="balance-sheet" role="tabpanel" aria-labelledby="balance-sheet-tab">
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Financial Year</th>
                                                <th>Total Assets</th>
                                                <th>Total Liabilities</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($financialData['balance_sheet']) > 0): ?>
                                                <?php foreach ($financialData['balance_sheet'] as $bs): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($bs['financial_year']) ?></td>
                                                        <td>₹<?= number_format($bs['total_assets'], 2) ?></td>
                                                        <td>₹<?= number_format($bs['total_liabilities'], 2) ?></td>
                                                        <td>
                                                            <a href="../balance_sheet/balance_sheet.php?action=view&id=<?= $bs['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../balance_sheet/balance_sheet.php?action=edit&id=<?= $bs['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No balance sheets found for this client.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Toggle client type specific fields
    $(document).ready(function() {
        // Initial setup based on selected value
        const clientType = $('#clientType').val();
        toggleClientTypeFields(clientType);
        
        // Handle changes
        $('#clientType').on('change', function() {
            toggleClientTypeFields($(this).val());
        });
    });
    
    function toggleClientTypeFields(clientType) {
        if (clientType === 'individual') {
            $('.individual-fields').show();
            $('.firm-fields').hide();
        } else {
            $('.individual-fields').hide();
            $('.firm-fields').show();
        }
    }
    
    // Confirm client deletion
    function confirmDelete(clientId) {
        if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
            window.location.href = 'client_process.php?action=delete&id=' + clientId;
        }
    }
    
    // Print client details
    function printClientDetails() {
        const printContents = document.getElementById('client-details-print').innerHTML;
        const originalContents = document.body.innerHTML;
        
        document.body.innerHTML = `
            <div class="container mt-4">
                <h2 class="text-center mb-4">Client Details</h2>
                ${printContents}
            </div>
            <style>
                @media print {
                    body {
                        padding: 20px;
                        font-size: 14px;
                    }
                    .btn, .nav-tabs, .tab-content {
                        display: none !important;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                }
            </style>
        `;
        
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>

<?php include '../../includes/footer.php'; ?>
