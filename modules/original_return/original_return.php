<?php
session_start();
require_once '../../config/database.php';

$pageTitle = "Original Return Management";
$activePage = "original_return";

// Initialize variables
$returns = [];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$returnId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$returnTypes = ['ITR-1', 'ITR-2', 'ITR-3', 'ITR-4', 'ITR-5', 'ITR-6', 'ITR-7'];
$return = null;
$client = null;

// Get current assessment year (Financial year + 1)
$currentYear = date('Y');
$month = date('n');
if ($month >= 4) { // April onwards is new financial year in India
    $assessmentYears = [
        ($currentYear) . '-' . ($currentYear + 1),
        ($currentYear - 1) . '-' . $currentYear,
        ($currentYear - 2) . '-' . ($currentYear - 1),
        ($currentYear - 3) . '-' . ($currentYear - 2),
        ($currentYear - 4) . '-' . ($currentYear - 3),
    ];
} else {
    $assessmentYears = [
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
            $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
            $stmt->bind_param("i", $clientId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $client = $result->fetch_assoc();
            } else {
                $_SESSION['error'] = "Client not found.";
                header("Location: original_return.php");
                exit;
            }
        }
        break;
        
    case 'edit':
        // Get return data for editing
        if ($returnId > 0) {
            $stmt = $conn->prepare("
                SELECT r.*, c.name as client_name, c.pan as client_pan, c.client_type
                FROM returns r
                JOIN clients c ON r.client_id = c.id
                WHERE r.id = ?
            ");
            $stmt->bind_param("i", $returnId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $return = $result->fetch_assoc();
                $client = [
                    'id' => $return['client_id'],
                    'name' => $return['client_name'],
                    'pan' => $return['client_pan'],
                    'client_type' => $return['client_type']
                ];
            } else {
                $_SESSION['error'] = "Return not found.";
                header("Location: original_return.php");
                exit;
            }
        }
        break;
        
    case 'view':
        // Get return data for viewing
        if ($returnId > 0) {
            $stmt = $conn->prepare("
                SELECT r.*, c.name as client_name, c.pan as client_pan, c.client_type,
                       (SELECT COUNT(*) FROM revised_returns WHERE original_return_id = r.id) as has_revised
                FROM returns r
                JOIN clients c ON r.client_id = c.id
                WHERE r.id = ?
            ");
            $stmt->bind_param("i", $returnId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $return = $result->fetch_assoc();
                
                // Check if there's a revised return for this original return
                if ($return['has_revised'] > 0) {
                    $revStmt = $conn->prepare("
                        SELECT * FROM revised_returns
                        WHERE original_return_id = ?
                        ORDER BY filing_date DESC
                        LIMIT 1
                    ");
                    $revStmt->bind_param("i", $returnId);
                    $revStmt->execute();
                    $revResult = $revStmt->get_result();
                    
                    if ($revResult->num_rows > 0) {
                        $return['revised'] = $revResult->fetch_assoc();
                    }
                }
            } else {
                $_SESSION['error'] = "Return not found.";
                header("Location: original_return.php");
                exit;
            }
        }
        break;
        
    case 'delete':
        // Handle delete request (this is processed in original_return_process.php)
        break;
        
    case 'list':
    default:
        // Get all returns
        $searchTerm = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';
        $assessmentYear = isset($_GET['assessment_year']) ? $db->escapeString($_GET['assessment_year']) : '';
        $returnType = isset($_GET['return_type']) ? $db->escapeString($_GET['return_type']) : '';
        
        $sql = "
            SELECT r.*, c.name as client_name, c.pan as client_pan,
                   (SELECT COUNT(*) FROM revised_returns WHERE original_return_id = r.id) as has_revised
            FROM returns r
            JOIN clients c ON r.client_id = c.id
            WHERE 1=1
        ";
        
        // Add search condition if specified
        if (!empty($searchTerm)) {
            $sql .= " AND (c.name LIKE '%$searchTerm%' OR c.pan LIKE '%$searchTerm%')";
        }
        
        // Add assessment year filter if specified
        if (!empty($assessmentYear)) {
            $sql .= " AND r.assessment_year = '$assessmentYear'";
        }
        
        // Add return type filter if specified
        if (!empty($returnType)) {
            $sql .= " AND r.return_type = '$returnType'";
        }
        
        $sql .= " ORDER BY r.filing_date DESC";
        
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $returns[] = $row;
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
        <!-- List Returns -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Original Returns List</h6>
                <a href="?action=add" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> File New Return
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by client name or PAN" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="assessment_year" class="form-select">
                                <option value="">All Assessment Years</option>
                                <?php foreach ($assessmentYears as $year): ?>
                                    <option value="<?= $year ?>" <?= (isset($_GET['assessment_year']) && $_GET['assessment_year'] == $year) ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="return_type" class="form-select">
                                <option value="">All Return Types</option>
                                <?php foreach ($returnTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= (isset($_GET['return_type']) && $_GET['return_type'] == $type) ? 'selected' : '' ?>>
                                        <?= $type ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                        <?php if (!empty($_GET['search']) || !empty($_GET['assessment_year']) || !empty($_GET['return_type'])): ?>
                            <div class="col-md-2">
                                <a href="?action=list" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="returnsDataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>PAN</th>
                                <th>Assessment Year</th>
                                <th>Return Type</th>
                                <th>Filing Date</th>
                                <th>Total Income</th>
                                <th>Tax Payable</th>
                                <th>Revised</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($returns) > 0): ?>
                                <?php foreach ($returns as $return): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($return['client_name']) ?></td>
                                        <td><?= htmlspecialchars($return['client_pan']) ?></td>
                                        <td><?= htmlspecialchars($return['assessment_year']) ?></td>
                                        <td><?= htmlspecialchars($return['return_type']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($return['filing_date'])) ?></td>
                                        <td>₹<?= number_format($return['total_income'], 2) ?></td>
                                        <td>₹<?= number_format($return['tax_payable'], 2) ?></td>
                                        <td><?= ($return['has_revised'] > 0) ? 'Yes' : 'No' ?></td>
                                        <td>
                                            <a href="?action=view&id=<?= $return['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($return['has_revised'] == 0): ?>
                                                <a href="?action=edit&id=<?= $return['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0);" onclick="confirmDelete(<?= $return['id'] ?>)" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($return['has_revised'] == 0): ?>
                                                <a href="../revised_return/revised_return.php?action=add&original_id=<?= $return['id'] ?>" class="btn btn-warning btn-sm" title="File Revised Return">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="../revised_return/revised_return.php?action=view&original_id=<?= $return['id'] ?>" class="btn btn-secondary btn-sm" title="View Revised Return">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No returns found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <!-- Add/Edit Return Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><?= ($action == 'add') ? 'File New Original Return' : 'Edit Original Return' ?></h6>
            </div>
            <div class="card-body">
                <form action="original_return_process.php" method="POST" class="ajax-form">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $return['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php if ($action == 'add' && !$clientId): ?>
                                <label for="client_search" class="form-label required-field">Select Client</label>
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
                            <label for="assessment_year" class="form-label required-field">Assessment Year</label>
                            <select class="form-select" id="assessment_year" name="assessment_year" required>
                                <option value="">Select Assessment Year</option>
                                <?php foreach ($assessmentYears as $year): ?>
                                    <option value="<?= $year ?>" <?= (isset($return['assessment_year']) && $return['assessment_year'] == $year) ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="return_type" class="form-label required-field">Return Type</label>
                            <select class="form-select" id="return_type" name="return_type" required>
                                <option value="">Select Return Type</option>
                                <?php foreach ($returnTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= (isset($return['return_type']) && $return['return_type'] == $type) ? 'selected' : '' ?>>
                                        <?= $type ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="filing_date" class="form-label required-field">Filing Date</label>
                            <input type="date" class="form-control" id="filing_date" name="filing_date" required value="<?= isset($return['filing_date']) ? substr($return['filing_date'], 0, 10) : date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="total_income" class="form-label required-field">Total Income (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="total_income" name="total_income" required value="<?= isset($return['total_income']) ? $return['total_income'] : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="tax_payable" class="form-label required-field">Tax Payable (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="tax_payable" name="tax_payable" required value="<?= isset($return['tax_payable']) ? $return['tax_payable'] : '' ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="acknowledgement_no" class="form-label required-field">Acknowledgement Number</label>
                            <input type="text" class="form-control" id="acknowledgement_no" name="acknowledgement_no" required value="<?= isset($return['acknowledgement_no']) ? htmlspecialchars($return['acknowledgement_no']) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="filing_type" class="form-label required-field">Filing Type</label>
                            <select class="form-select" id="filing_type" name="filing_type" required>
                                <option value="">Select Filing Type</option>
                                <option value="online" <?= (isset($return['filing_type']) && $return['filing_type'] == 'online') ? 'selected' : '' ?>>Online</option>
                                <option value="offline" <?= (isset($return['filing_type']) && $return['filing_type'] == 'offline') ? 'selected' : '' ?>>Offline</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tax_paid_date" class="form-label">Tax Paid Date</label>
                            <input type="date" class="form-control" id="tax_paid_date" name="tax_paid_date" value="<?= isset($return['tax_paid_date']) ? substr($return['tax_paid_date'], 0, 10) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="challan_no" class="form-label">Challan Number</label>
                            <input type="text" class="form-control" id="challan_no" name="challan_no" value="<?= isset($return['challan_no']) ? htmlspecialchars($return['challan_no']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"><?= isset($return['remarks']) ? htmlspecialchars($return['remarks']) : '' ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="original_return.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <?= ($action == 'add') ? 'File Return' : 'Update Return' ?>
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
                        <h5 class="modal-title" id="clientSearchModalLabel">Search Client</h5>
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
                                        <td colspan="4" class="text-center">Search for clients to see results</td>
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
    <?php elseif ($action == 'view' && $return): ?>
        <!-- View Return Details -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Original Return Details</h6>
                <div>
                    <?php if (!isset($return['revised'])): ?>
                        <a href="?action=edit&id=<?= $return['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="../revised_return/revised_return.php?action=add&original_id=<?= $return['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-file-alt"></i> File Revised Return
                        </a>
                    <?php else: ?>
                        <a href="../revised_return/revised_return.php?action=view&original_id=<?= $return['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-file-alt"></i> View Revised Return
                        </a>
                    <?php endif; ?>
                    <a href="javascript:void(0);" onclick="printReturnDetails()" class="btn btn-info btn-sm">
                        <i class="fas fa-print"></i> Print
                    </a>
                    <a href="original_return.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body" id="return-details-print">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Client Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Name</th>
                                <td><?= htmlspecialchars($return['client_name']) ?></td>
                            </tr>
                            <tr>
                                <th>PAN</th>
                                <td><?= htmlspecialchars($return['client_pan']) ?></td>
                            </tr>
                            <tr>
                                <th>Client Type</th>
                                <td><?= ucfirst(htmlspecialchars($return['client_type'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Return Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Assessment Year</th>
                                <td><?= htmlspecialchars($return['assessment_year']) ?></td>
                            </tr>
                            <tr>
                                <th>Return Type</th>
                                <td><?= htmlspecialchars($return['return_type']) ?></td>
                            </tr>
                            <tr>
                                <th>Filing Date</th>
                                <td><?= date('d-m-Y', strtotime($return['filing_date'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Financial Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Total Income</th>
                                <td>₹<?= number_format($return['total_income'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Tax Payable</th>
                                <td>₹<?= number_format($return['tax_payable'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Tax Paid Date</th>
                                <td><?= $return['tax_paid_date'] ? date('d-m-Y', strtotime($return['tax_paid_date'])) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Challan Number</th>
                                <td><?= htmlspecialchars($return['challan_no'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Filing Details</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Acknowledgement Number</th>
                                <td><?= htmlspecialchars($return['acknowledgement_no']) ?></td>
                            </tr>
                            <tr>
                                <th>Filing Type</th>
                                <td><?= ucfirst(htmlspecialchars($return['filing_type'])) ?></td>
                            </tr>
                            <tr>
                                <th>Filing Status</th>
                                <td>Original Return</td>
                            </tr>
                            <tr>
                                <th>Has Revised</th>
                                <td><?= ($return['has_revised'] > 0) ? 'Yes' : 'No' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($return['remarks'])): ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Remarks</h5>
                        <div class="card">
                            <div class="card-body">
                                <?= nl2br(htmlspecialchars($return['remarks'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($return['revised'])): ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> This return has been revised on <?= date('d-m-Y', strtotime($return['revised']['filing_date'])) ?>. 
                            <a href="../revised_return/revised_return.php?action=view&original_id=<?= $return['id'] ?>" class="alert-link">View Revised Return</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Confirm return deletion
    function confirmDelete(returnId) {
        if (confirm('Are you sure you want to delete this return? This action cannot be undone.')) {
            window.location.href = 'original_return_process.php?action=delete&id=' + returnId;
        }
    }
    
    // Print return details
    function printReturnDetails() {
        const printContents = document.getElementById('return-details-print').innerHTML;
        const originalContents = document.body.innerHTML;
        
        document.body.innerHTML = `
            <div class="container mt-4">
                <h2 class="text-center mb-4">Original Return Details</h2>
                ${printContents}
            </div>
            <style>
                @media print {
                    body {
                        padding: 20px;
                        font-size: 14px;
                    }
                    .btn {
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
    
    // Client search functionality
    $(document).ready(function() {
        // Search clients when button is clicked
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
                    action: 'search',
                    term: searchTerm
                },
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    
                    if (data.length > 0) {
                        data.forEach(function(client) {
                            html += `
                                <tr>
                                    <td>${client.pan}</td>
                                    <td>${client.name}</td>
                                    <td>${client.client_type}</td>
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
                        html = '<tr><td colspan="4" class="text-center">No clients found. <a href="../client/client.php?action=add" target="_blank">Add a new client</a></td></tr>';
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
