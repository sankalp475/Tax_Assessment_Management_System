<?php
session_start();
require_once '../../config/database.php';

$pageTitle = "Trading Account Management";
$activePage = "trading_account";

// Initialize variables
$tradingAccounts = [];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$accountId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$tradingAccount = null;
$client = null;

// Get current financial year
$currentYear = date('Y');
$month = date('n');
if ($month >= 4) { // April onwards is new financial year in India
    $financialYears = [
        ($currentYear) . '-' . ($currentYear + 1),
        ($currentYear - 1) . '-' . $currentYear,
        ($currentYear - 2) . '-' . ($currentYear - 1),
        ($currentYear - 3) . '-' . ($currentYear - 2),
        ($currentYear - 4) . '-' . ($currentYear - 3),
    ];
} else {
    $financialYears = [
        ($currentYear - 1) . '-' . $currentYear,
        ($currentYear - 2) . '-' . ($currentYear - 1),
        ($currentYear - 3) . '-' . ($currentYear - 2),
        ($currentYear - 4) . '-' . ($currentYear - 3),
        ($currentYear - 5) . '-' . ($currentYear - 4),
    ];
}

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Process the requested action
switch ($action) {
    case 'add':
        // If client_id is provided, get client details
        if ($clientId > 0) {
            $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ? AND client_type != 'individual'");
            $stmt->bind_param("i", $clientId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $client = $result->fetch_assoc();
            } else {
                $_SESSION['error'] = "Client not found or is an individual (Trading accounts are only for firm clients).";
                header("Location: trading_account.php");
                exit;
            }
        }
        break;
        
    case 'edit':
        // Get trading account data for editing
        if ($accountId > 0) {
            $stmt = $conn->prepare("
                SELECT ta.*, c.name as client_name, c.pan as client_pan
                FROM trading_accounts ta
                JOIN clients c ON ta.client_id = c.id
                WHERE ta.id = ?
            ");
            $stmt->bind_param("i", $accountId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $tradingAccount = $result->fetch_assoc();
                
                // Parse JSON data
                if (!empty($tradingAccount['sales_details'])) {
                    $tradingAccount['sales_details'] = json_decode($tradingAccount['sales_details'], true);
                } else {
                    $tradingAccount['sales_details'] = [];
                }
                
                if (!empty($tradingAccount['purchases_details'])) {
                    $tradingAccount['purchases_details'] = json_decode($tradingAccount['purchases_details'], true);
                } else {
                    $tradingAccount['purchases_details'] = [];
                }
                
                if (!empty($tradingAccount['direct_expenses_details'])) {
                    $tradingAccount['direct_expenses_details'] = json_decode($tradingAccount['direct_expenses_details'], true);
                } else {
                    $tradingAccount['direct_expenses_details'] = [];
                }
                
                $client = [
                    'id' => $tradingAccount['client_id'],
                    'name' => $tradingAccount['client_name'],
                    'pan' => $tradingAccount['client_pan']
                ];
            } else {
                $_SESSION['error'] = "Trading account not found.";
                header("Location: trading_account.php");
                exit;
            }
        }
        break;
        
    case 'view':
        // Get trading account data for viewing
        if ($accountId > 0) {
            $stmt = $conn->prepare("
                SELECT ta.*, c.name as client_name, c.pan as client_pan, c.client_type
                FROM trading_accounts ta
                JOIN clients c ON ta.client_id = c.id
                WHERE ta.id = ?
            ");
            $stmt->bind_param("i", $accountId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $tradingAccount = $result->fetch_assoc();
                
                // Parse JSON data
                if (!empty($tradingAccount['sales_details'])) {
                    $tradingAccount['sales_details'] = json_decode($tradingAccount['sales_details'], true);
                } else {
                    $tradingAccount['sales_details'] = [];
                }
                
                if (!empty($tradingAccount['purchases_details'])) {
                    $tradingAccount['purchases_details'] = json_decode($tradingAccount['purchases_details'], true);
                } else {
                    $tradingAccount['purchases_details'] = [];
                }
                
                if (!empty($tradingAccount['direct_expenses_details'])) {
                    $tradingAccount['direct_expenses_details'] = json_decode($tradingAccount['direct_expenses_details'], true);
                } else {
                    $tradingAccount['direct_expenses_details'] = [];
                }
                
                // Get profit and loss account for this trading account
                $stmt = $conn->prepare("
                    SELECT pl.id 
                    FROM profit_loss_accounts pl 
                    WHERE pl.client_id = ? AND pl.financial_year = ?
                ");
                $stmt->bind_param("is", $tradingAccount['client_id'], $tradingAccount['financial_year']);
                $stmt->execute();
                $plResult = $stmt->get_result();
                
                if ($plResult->num_rows > 0) {
                    $plRow = $plResult->fetch_assoc();
                    $tradingAccount['has_profit_loss'] = true;
                    $tradingAccount['profit_loss_id'] = $plRow['id'];
                } else {
                    $tradingAccount['has_profit_loss'] = false;
                }
            } else {
                $_SESSION['error'] = "Trading account not found.";
                header("Location: trading_account.php");
                exit;
            }
        }
        break;
        
    case 'delete':
        // Handle delete request (this is processed in trading_account_process.php)
        break;
        
    case 'list':
    default:
        // Get all trading accounts
        $searchTerm = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';
        $financialYear = isset($_GET['financial_year']) ? $db->escapeString($_GET['financial_year']) : '';
        
        $sql = "
            SELECT ta.*, c.name as client_name, c.pan as client_pan
            FROM trading_accounts ta
            JOIN clients c ON ta.client_id = c.id
            WHERE 1=1
        ";
        
        // Add search condition if specified
        if (!empty($searchTerm)) {
            $sql .= " AND (c.name LIKE '%$searchTerm%' OR c.pan LIKE '%$searchTerm%')";
        }
        
        // Add financial year filter if specified
        if (!empty($financialYear)) {
            $sql .= " AND ta.financial_year = '$financialYear'";
        }
        
        $sql .= " ORDER BY ta.financial_year DESC, c.name ASC";
        
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $tradingAccounts[] = $row;
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
        <!-- List Trading Accounts -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Trading Accounts List</h6>
                <a href="?action=add" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Trading Account
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by client name or PAN" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="financial_year" class="form-select">
                                <option value="">All Financial Years</option>
                                <?php foreach ($financialYears as $year): ?>
                                    <option value="<?= $year ?>" <?= (isset($_GET['financial_year']) && $_GET['financial_year'] == $year) ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                        <?php if (!empty($_GET['search']) || !empty($_GET['financial_year'])): ?>
                            <div class="col-md-2">
                                <a href="?action=list" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tradingAccountsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>PAN</th>
                                <th>Financial Year</th>
                                <th>Opening Stock</th>
                                <th>Purchases</th>
                                <th>Sales</th>
                                <th>Closing Stock</th>
                                <th>Gross Profit/Loss</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($tradingAccounts) > 0): ?>
                                <?php foreach ($tradingAccounts as $account): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($account['client_name']) ?></td>
                                        <td><?= htmlspecialchars($account['client_pan']) ?></td>
                                        <td><?= htmlspecialchars($account['financial_year']) ?></td>
                                        <td>₹<?= number_format($account['opening_stock'], 2) ?></td>
                                        <td>₹<?= number_format($account['purchases'], 2) ?></td>
                                        <td>₹<?= number_format($account['sales'], 2) ?></td>
                                        <td>₹<?= number_format($account['closing_stock'], 2) ?></td>
                                        <td class="<?= ($account['gross_profit_loss'] >= 0) ? 'text-success' : 'text-danger' ?>">
                                            ₹<?= number_format(abs($account['gross_profit_loss']), 2) ?>
                                            <?= ($account['gross_profit_loss'] >= 0) ? '(Profit)' : '(Loss)' ?>
                                        </td>
                                        <td>
                                            <a href="?action=view&id=<?= $account['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?action=edit&id=<?= $account['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?= $account['id'] ?>)" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No trading accounts found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <!-- Add/Edit Trading Account Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><?= ($action == 'add') ? 'Add New Trading Account' : 'Edit Trading Account' ?></h6>
            </div>
            <div class="card-body">
                <form action="trading_account_process.php" method="POST" class="ajax-form">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $tradingAccount['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php if ($action == 'add' && !$clientId): ?>
                                <label for="client_search" class="form-label required-field">Select Client (Firm)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="client_search" placeholder="Search by client name or PAN" required>
                                    <input type="hidden" name="client_id" id="client_id" value="">
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#clientSearchModal">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div id="client_info" class="mt-2"></div>
                            <?php else: ?>
                                <label class="form-label">Client</label>
                                <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                                <div class="form-control bg-light"><?= htmlspecialchars($client['name']) ?> (<?= htmlspecialchars($client['pan']) ?>)</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="financial_year" class="form-label required-field">Financial Year</label>
                            <select class="form-select" id="financial_year" name="financial_year" required>
                                <option value="">Select Financial Year</option>
                                <?php foreach ($financialYears as $year): ?>
                                    <option value="<?= $year ?>" <?= (isset($tradingAccount['financial_year']) && $tradingAccount['financial_year'] == $year) ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="opening_stock" class="form-label required-field">Opening Stock (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="opening_stock" name="opening_stock" required value="<?= isset($tradingAccount['opening_stock']) ? $tradingAccount['opening_stock'] : '0.00' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="closing_stock" class="form-label required-field">Closing Stock (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="closing_stock" name="closing_stock" required value="<?= isset($tradingAccount['closing_stock']) ? $tradingAccount['closing_stock'] : '0.00' ?>">
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Sales</h5>
                    <div id="sales_container">
                        <?php if (isset($tradingAccount['sales_details']) && count($tradingAccount['sales_details']) > 0): ?>
                            <?php foreach ($tradingAccount['sales_details'] as $index => $item): ?>
                                <div class="row mb-3 sales-item">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="sales_description[]" placeholder="Description" value="<?= htmlspecialchars($item['description']) ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" step="0.01" min="0" class="form-control sales-amount" name="sales_amount[]" placeholder="Amount" value="<?= $item['amount'] ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger remove-item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="row mb-3 sales-item">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="sales_description[]" placeholder="Description">
                                </div>
                                <div class="col-md-5">
                                    <input type="number" step="0.01" min="0" class="form-control sales-amount" name="sales_amount[]" placeholder="Amount" value="0.00">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger remove-item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success btn-sm" id="add_sales">
                                <i class="fas fa-plus"></i> Add Sales Item
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="sales_total" class="form-label">Total Sales (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="sales_total" name="sales" readonly value="<?= isset($tradingAccount['sales']) ? $tradingAccount['sales'] : '0.00' ?>">
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Purchases</h5>
                    <div id="purchases_container">
                        <?php if (isset($tradingAccount['purchases_details']) && count($tradingAccount['purchases_details']) > 0): ?>
                            <?php foreach ($tradingAccount['purchases_details'] as $index => $item): ?>
                                <div class="row mb-3 purchases-item">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="purchases_description[]" placeholder="Description" value="<?= htmlspecialchars($item['description']) ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" step="0.01" min="0" class="form-control purchases-amount" name="purchases_amount[]" placeholder="Amount" value="<?= $item['amount'] ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger remove-item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="row mb-3 purchases-item">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="purchases_description[]" placeholder="Description">
                                </div>
                                <div class="col-md-5">
                                    <input type="number" step="0.01" min="0" class="form-control purchases-amount" name="purchases_amount[]" placeholder="Amount" value="0.00">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger remove-item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success btn-sm" id="add_purchases">
                                <i class="fas fa-plus"></i> Add Purchase Item
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="purchases_total" class="form-label">Total Purchases (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="purchases_total" name="purchases" readonly value="<?= isset($tradingAccount['purchases']) ? $tradingAccount['purchases'] : '0.00' ?>">
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Direct Expenses</h5>
                    <div id="direct_expenses_container">
                        <?php if (isset($tradingAccount['direct_expenses_details']) && count($tradingAccount['direct_expenses_details']) > 0): ?>
                            <?php foreach ($tradingAccount['direct_expenses_details'] as $index => $item): ?>
                                <div class="row mb-3 direct-expenses-item">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="direct_expenses_description[]" placeholder="Description" value="<?= htmlspecialchars($item['description']) ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" step="0.01" min="0" class="form-control direct-expenses-amount" name="direct_expenses_amount[]" placeholder="Amount" value="<?= $item['amount'] ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger remove-item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="row mb-3 direct-expenses-item">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="direct_expenses_description[]" placeholder="Description">
                                </div>
                                <div class="col-md-5">
                                    <input type="number" step="0.01" min="0" class="form-control direct-expenses-amount" name="direct_expenses_amount[]" placeholder="Amount" value="0.00">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger remove-item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success btn-sm" id="add_direct_expenses">
                                <i class="fas fa-plus"></i> Add Direct Expense
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="direct_expenses_total" class="form-label">Total Direct Expenses (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="direct_expenses_total" name="direct_expenses" readonly value="<?= isset($tradingAccount['direct_expenses']) ? $tradingAccount['direct_expenses'] : '0.00' ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="gross_profit_loss" class="form-label">Gross Profit/Loss (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="gross_profit_loss" name="gross_profit_loss" readonly value="<?= isset($tradingAccount['gross_profit_loss']) ? $tradingAccount['gross_profit_loss'] : '0.00' ?>">
                            <div class="form-text" id="profit_loss_text"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= isset($tradingAccount['notes']) ? htmlspecialchars($tradingAccount['notes']) : '' ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="trading_account.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <?= ($action == 'add') ? 'Add Trading Account' : 'Update Trading Account' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Client Search Modal -->
        <div class="modal fade" id="clientSearchModal" tabindex="-1" aria-labelledby="clientSearchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clientSearchModalLabel">Search Client (Firms Only)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="modal_client_search" placeholder="Search by name or PAN">
                                <button class="btn btn-primary" type="button" id="search_clients_btn">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="client_search_results">
                                <thead>
                                    <tr>
                                        <th>PAN</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center">Search for firm clients to see results</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($action == 'view' && $tradingAccount): ?>
        <!-- View Trading Account Details -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Trading Account Details</h6>
                <div>
                    <a href="?action=edit&id=<?= $tradingAccount['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php if ($tradingAccount['has_profit_loss']): ?>
                        <a href="../profit_loss/profit_loss.php?action=view&id=<?= $tradingAccount['profit_loss_id'] ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-file-alt"></i> View P&L Account
                        </a>
                    <?php else: ?>
                        <a href="../profit_loss/profit_loss.php?action=add&client_id=<?= $tradingAccount['client_id'] ?>&financial_year=<?= $tradingAccount['financial_year'] ?>&gross_profit_loss=<?= $tradingAccount['gross_profit_loss'] ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Create P&L Account
                        </a>
                    <?php endif; ?>
                    <a href="javascript:void(0);" onclick="printTradingAccount()" class="btn btn-info btn-sm">
                        <i class="fas fa-print"></i> Print
                    </a>
                    <a href="trading_account.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body" id="trading-account-print">
                <div class="financial-statement">
                    <div class="statement-header">
                        <h2 class="statement-title">Trading Account</h2>
                        <div class="statement-subtitle"><?= htmlspecialchars($tradingAccount['client_name']) ?> (<?= htmlspecialchars($tradingAccount['client_pan']) ?>)</div>
                        <div class="statement-period">For the Financial Year: <?= htmlspecialchars($tradingAccount['financial_year']) ?></div>
                    </div>
                    
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered statement-table">
                                    <thead>
                                        <tr>
                                            <th width="50%">Debit</th>
                                            <th width="50%">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex justify-content-between">
                                                    <span>To Opening Stock</span>
                                                    <span class="amount-column">₹<?= number_format($tradingAccount['opening_stock'], 2) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-between">
                                                    <span>By Sales</span>
                                                    <span class="amount-column">₹<?= number_format($tradingAccount['sales'], 2) ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex justify-content-between">
                                                    <span>To Purchases</span>
                                                    <span class="amount-column">₹<?= number_format($tradingAccount['purchases'], 2) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-between">
                                                    <span>By Closing Stock</span>
                                                    <span class="amount-column">₹<?= number_format($tradingAccount['closing_stock'], 2) ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex justify-content-between">
                                                    <span>To Direct Expenses</span>
                                                    <span class="amount-column">₹<?= number_format($tradingAccount['direct_expenses'], 2) ?></span>
                                                </div>
                                            </td>
                                            <td></td>
                                        </tr>
                                        
                                        <?php 
                                        $debitTotal = $tradingAccount['opening_stock'] + $tradingAccount['purchases'] + $tradingAccount['direct_expenses'];
                                        $creditTotal = $tradingAccount['sales'] + $tradingAccount['closing_stock'];
                                        $diff = $creditTotal - $debitTotal;
                                        
                                        if ($diff > 0): // Gross Profit ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex justify-content-between font-weight-bold">
                                                        <span>To Gross Profit c/d</span>
                                                        <span class="amount-column">₹<?= number_format($diff, 2) ?></span>
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                        <?php elseif ($diff < 0): // Gross Loss ?>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <div class="d-flex justify-content-between font-weight-bold">
                                                        <span>By Gross Loss c/d</span>
                                                        <span class="amount-column">₹<?= number_format(abs($diff), 2) ?></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        
                                        <tr class="statement-total-row">
                                            <td>
                                                <div class="d-flex justify-content-between">
                                                    <span>Total</span>
                                                    <span class="amount-column">₹<?= number_format(($diff > 0) ? ($debitTotal + $diff) : $debitTotal, 2) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-between">
                                                    <span>Total</span>
                                                    <span class="amount-column">₹<?= number_format(($diff < 0) ? ($creditTotal + abs($diff)) : $creditTotal, 2) ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($tradingAccount['sales_details']) || !empty($tradingAccount['purchases_details']) || !empty($tradingAccount['direct_expenses_details'])): ?>
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <h4>Detailed Breakdown</h4>
                            
                            <?php if (!empty($tradingAccount['sales_details'])): ?>
                            <h5 class="mt-3">Sales</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tradingAccount['sales_details'] as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['description']) ?></td>
                                                <td class="text-end"><?= number_format($item['amount'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-secondary">
                                            <th>Total Sales</th>
                                            <th class="text-end"><?= number_format($tradingAccount['sales'], 2) ?></th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($tradingAccount['purchases_details'])): ?>
                            <h5 class="mt-3">Purchases</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tradingAccount['purchases_details'] as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['description']) ?></td>
                                                <td class="text-end"><?= number_format($item['amount'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-secondary">
                                            <th>Total Purchases</th>
                                            <th class="text-end"><?= number_format($tradingAccount['purchases'], 2) ?></th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($tradingAccount['direct_expenses_details'])): ?>
                            <h5 class="mt-3">Direct Expenses</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tradingAccount['direct_expenses_details'] as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['description']) ?></td>
                                                <td class="text-end"><?= number_format($item['amount'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-secondary">
                                            <th>Total Direct Expenses</th>
                                            <th class="text-end"><?= number_format($tradingAccount['direct_expenses'], 2) ?></th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($tradingAccount['notes'])): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Notes</h5>
                            <div class="card">
                                <div class="card-body">
                                    <?= nl2br(htmlspecialchars($tradingAccount['notes'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Calculate totals and profit/loss
    function calculateTotals() {
        // Sales total
        let salesTotal = 0;
        $('.sales-amount').each(function() {
            salesTotal += parseFloat($(this).val()) || 0;
        });
        $('#sales_total').val(salesTotal.toFixed(2));
        
        // Purchases total
        let purchasesTotal = 0;
        $('.purchases-amount').each(function() {
            purchasesTotal += parseFloat($(this).val()) || 0;
        });
        $('#purchases_total').val(purchasesTotal.toFixed(2));
        
        // Direct expenses total
        let directExpensesTotal = 0;
        $('.direct-expenses-amount').each(function() {
            directExpensesTotal += parseFloat($(this).val()) || 0;
        });
        $('#direct_expenses_total').val(directExpensesTotal.toFixed(2));
        
        // Calculate gross profit/loss
        const openingStock = parseFloat($('#opening_stock').val()) || 0;
        const closingStock = parseFloat($('#closing_stock').val()) || 0;
        
        const grossProfitLoss = salesTotal + closingStock - openingStock - purchasesTotal - directExpensesTotal;
        $('#gross_profit_loss').val(grossProfitLoss.toFixed(2));
        
        // Update profit/loss text
        if (grossProfitLoss > 0) {
            $('#profit_loss_text').html('Gross Profit').removeClass('text-danger').addClass('text-success');
        } else if (grossProfitLoss < 0) {
            $('#profit_loss_text').html('Gross Loss').removeClass('text-success').addClass('text-danger');
        } else {
            $('#profit_loss_text').html('No Profit/Loss').removeClass('text-success text-danger');
        }
    }
    
    $(document).ready(function() {
        // Calculate initial totals
        calculateTotals();
        
        // Recalculate when values change
        $(document).on('input', '.sales-amount, .purchases-amount, .direct-expenses-amount, #opening_stock, #closing_stock', function() {
            calculateTotals();
        });
        
        // Add sales item
        $('#add_sales').on('click', function() {
            const newItem = `
                <div class="row mb-3 sales-item">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="sales_description[]" placeholder="Description">
                    </div>
                    <div class="col-md-5">
                        <input type="number" step="0.01" min="0" class="form-control sales-amount" name="sales_amount[]" placeholder="Amount" value="0.00">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#sales_container').append(newItem);
        });
        
        // Add purchases item
        $('#add_purchases').on('click', function() {
            const newItem = `
                <div class="row mb-3 purchases-item">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="purchases_description[]" placeholder="Description">
                    </div>
                    <div class="col-md-5">
                        <input type="number" step="0.01" min="0" class="form-control purchases-amount" name="purchases_amount[]" placeholder="Amount" value="0.00">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#purchases_container').append(newItem);
        });
        
        // Add direct expenses item
        $('#add_direct_expenses').on('click', function() {
            const newItem = `
                <div class="row mb-3 direct-expenses-item">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="direct_expenses_description[]" placeholder="Description">
                    </div>
                    <div class="col-md-5">
                        <input type="number" step="0.01" min="0" class="form-control direct-expenses-amount" name="direct_expenses_amount[]" placeholder="Amount" value="0.00">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#direct_expenses_container').append(newItem);
        });
        
        // Remove item
        $(document).on('click', '.remove-item', function() {
            $(this).closest('.row').remove();
            calculateTotals();
        });
        
        // Confirm account deletion
        window.confirmDelete = function(accountId) {
            if (confirm('Are you sure you want to delete this trading account? This action cannot be undone.')) {
                window.location.href = 'trading_account_process.php?action=delete&id=' + accountId;
            }
        };
        
        // Print trading account
        window.printTradingAccount = function() {
            const printContents = document.getElementById('trading-account-print').innerHTML;
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div class="container mt-4">
                    ${printContents}
                </div>
                <style>
                    @media print {
                        body {
                            padding: 20px;
                            font-size: 14px;
                        }
                        .btn, .no-print {
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
                        .statement-header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .statement-title {
                            font-size: 24px;
                            font-weight: bold;
                        }
                        .statement-subtitle {
                            font-size: 18px;
                        }
                        .statement-period {
                            font-size: 16px;
                            margin-top: 5px;
                        }
                        .d-flex {
                            display: flex !important;
                        }
                        .justify-content-between {
                            justify-content: space-between !important;
                        }
                        .amount-column {
                            text-align: right;
                        }
                    }
                </style>
            `;
            
            window.print();
            document.body.innerHTML = originalContents;
        };
        
        // Client search functionality
        $('#search_clients_btn').on('click', function() {
            const searchTerm = $('#modal_client_search').val();
            if (searchTerm.length < 2) {
                alert('Please enter at least 2 characters to search.');
                return;
            }
            
            $.ajax({
                url: '../client/client_api.php',
                type: 'GET',
                data: {
                    action: 'get_clients_by_type',
                    type: 'partnership,company,trust' // Only non-individual clients
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let html = '';
                        const clients = response.data.filter(client => 
                            client.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
                            client.pan.toLowerCase().includes(searchTerm.toLowerCase())
                        );
                        
                        if (clients.length > 0) {
                            clients.forEach(function(client) {
                                html += `
                                    <tr>
                                        <td>${client.pan}</td>
                                        <td>${client.name}</td>
                                        <td>${client.client_type || 'Firm'}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm select-client" 
                                                    data-id="${client.id}" 
                                                    data-name="${client.name}" 
                                                    data-pan="${client.pan}">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = '<tr><td colspan="4" class="text-center">No matching clients found. <a href="../client/client.php?action=add" target="_blank">Add a new client</a></td></tr>';
                        }
                        
                        $('#client_search_results tbody').html(html);
                        
                        // Handle client selection
                        $('.select-client').on('click', function() {
                            const id = $(this).data('id');
                            const name = $(this).data('name');
                            const pan = $(this).data('pan');
                            
                            $('#client_id').val(id);
                            $('#client_search').val(name + ' (' + pan + ')');
                            $('#client_info').html(`
                                <div class="alert alert-success">
                                    <strong>Selected Client:</strong> ${name} (${pan})
                                </div>
                            `);
                            
                            $('#clientSearchModal').modal('hide');
                        });
                    } else {
                        $('#client_search_results tbody').html('<tr><td colspan="4" class="text-center">Error: ' + response.message + '</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error searching clients:', error);
                    $('#client_search_results tbody').html('<tr><td colspan="4" class="text-center">Error searching clients. Please try again.</td></tr>');
                }
            });
        });
        
        // Allow pressing Enter in search field
        $('#modal_client_search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#search_clients_btn').click();
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>
