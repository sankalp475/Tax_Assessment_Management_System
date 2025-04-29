<?php
session_start();
require_once '../../config/database.php';

$pageTitle = "Revised Return Management";
$activePage = "revised_return";

// Initialize variables
$revisedReturns = [];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$revisedId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$originalId = isset($_GET['original_id']) ? intval($_GET['original_id']) : 0;
$returnTypes = ['ITR-1', 'ITR-2', 'ITR-3', 'ITR-4', 'ITR-5', 'ITR-6', 'ITR-7'];
$revisedReturn = null;
$originalReturn = null;
$client = null;
$reasons = [
    'income_omission' => 'Omission of Income',
    'deduction_error' => 'Error in Deduction/Exemption',
    'incorrect_details' => 'Incorrect Personal Details',
    'tax_calculation_error' => 'Error in Tax Calculation',
    'document_update' => 'Updated Documents',
    'other' => 'Other'
];

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Process the requested action
switch ($action) {
    case 'add':
        // Get original return data for filing a revised return
        if ($originalId > 0) {
            $stmt = $conn->prepare("
                SELECT r.*, c.name as client_name, c.pan as client_pan, c.client_type,
                       (SELECT COUNT(*) FROM revised_returns WHERE original_return_id = r.id) as has_revised
                FROM returns r
                JOIN clients c ON r.client_id = c.id
                WHERE r.id = ?
            ");
            $stmt->bind_param("i", $originalId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $originalReturn = $result->fetch_assoc();
                
                if ($originalReturn['has_revised'] > 0) {
                    $_SESSION['error'] = "A revised return already exists for this original return.";
                    header("Location: revised_return.php");
                    exit;
                }
                
                $client = [
                    'id' => $originalReturn['client_id'],
                    'name' => $originalReturn['client_name'],
                    'pan' => $originalReturn['client_pan'],
                    'client_type' => $originalReturn['client_type']
                ];
            } else {
                $_SESSION['error'] = "Original return not found.";
                header("Location: revised_return.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Original return ID is required.";
            header("Location: revised_return.php");
            exit;
        }
        break;
        
    case 'edit':
        // Get revised return data for editing
        if ($revisedId > 0) {
            $stmt = $conn->prepare("
                SELECT rr.*, r.client_id, r.assessment_year, r.return_type as original_return_type,
                       c.name as client_name, c.pan as client_pan, c.client_type
                FROM revised_returns rr
                JOIN returns r ON rr.original_return_id = r.id
                JOIN clients c ON r.client_id = c.id
                WHERE rr.id = ?
            ");
            $stmt->bind_param("i", $revisedId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $revisedReturn = $result->fetch_assoc();
                $originalId = $revisedReturn['original_return_id'];
                
                // Get original return data
                $stmt = $conn->prepare("SELECT * FROM returns WHERE id = ?");
                $stmt->bind_param("i", $originalId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $originalReturn = $result->fetch_assoc();
                }
                
                $client = [
                    'id' => $revisedReturn['client_id'],
                    'name' => $revisedReturn['client_name'],
                    'pan' => $revisedReturn['client_pan'],
                    'client_type' => $revisedReturn['client_type']
                ];
            } else {
                $_SESSION['error'] = "Revised return not found.";
                header("Location: revised_return.php");
                exit;
            }
        }
        break;
        
    case 'view':
        // Get revised return data for viewing
        if ($revisedId > 0) {
            $stmt = $conn->prepare("
                SELECT rr.*, r.client_id, r.assessment_year, r.return_type as original_return_type,
                       r.filing_date as original_filing_date, r.total_income as original_income,
                       r.tax_payable as original_tax, r.acknowledgement_no as original_ack,
                       c.name as client_name, c.pan as client_pan, c.client_type
                FROM revised_returns rr
                JOIN returns r ON rr.original_return_id = r.id
                JOIN clients c ON r.client_id = c.id
                WHERE rr.id = ?
            ");
            $stmt->bind_param("i", $revisedId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $revisedReturn = $result->fetch_assoc();
            } else {
                $_SESSION['error'] = "Revised return not found.";
                header("Location: revised_return.php");
                exit;
            }
        } elseif ($originalId > 0) {
            // Get revised return for a specific original return
            $stmt = $conn->prepare("
                SELECT rr.*, r.client_id, r.assessment_year, r.return_type as original_return_type,
                       r.filing_date as original_filing_date, r.total_income as original_income,
                       r.tax_payable as original_tax, r.acknowledgement_no as original_ack,
                       c.name as client_name, c.pan as client_pan, c.client_type
                FROM revised_returns rr
                JOIN returns r ON rr.original_return_id = r.id
                JOIN clients c ON r.client_id = c.id
                WHERE rr.original_return_id = ?
                ORDER BY rr.filing_date DESC
                LIMIT 1
            ");
            $stmt->bind_param("i", $originalId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $revisedReturn = $result->fetch_assoc();
            } else {
                $_SESSION['error'] = "Revised return not found for this original return.";
                header("Location: revised_return.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Revised return ID or original return ID is required.";
            header("Location: revised_return.php");
            exit;
        }
        break;
        
    case 'delete':
        // Handle delete request (this is processed in revised_return_process.php)
        break;
        
    case 'list':
    default:
        // Get all revised returns
        $searchTerm = isset($_GET['search']) ? $db->escapeString($_GET['search']) : '';
        $assessmentYear = isset($_GET['assessment_year']) ? $db->escapeString($_GET['assessment_year']) : '';
        
        $sql = "
            SELECT rr.*, r.assessment_year, r.return_type as original_return_type,
                   c.name as client_name, c.pan as client_pan
            FROM revised_returns rr
            JOIN returns r ON rr.original_return_id = r.id
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
        
        $sql .= " ORDER BY rr.filing_date DESC";
        
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $revisedReturns[] = $row;
            }
        }
        
        // Get current assessment years for filter
        $yearResult = $conn->query("SELECT DISTINCT assessment_year FROM returns ORDER BY assessment_year DESC");
        $assessmentYears = [];
        
        if ($yearResult) {
            while ($row = $yearResult->fetch_assoc()) {
                $assessmentYears[] = $row['assessment_year'];
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
        <!-- List Revised Returns -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Revised Returns List</h6>
                <div>
                    <a href="../original_return/original_return.php" class="btn btn-info btn-sm">
                        <i class="fas fa-file-invoice"></i> Original Returns
                    </a>
                </div>
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
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                        <?php if (!empty($_GET['search']) || !empty($_GET['assessment_year'])): ?>
                            <div class="col-md-2">
                                <a href="?action=list" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="revisedReturnsDataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>PAN</th>
                                <th>Assessment Year</th>
                                <th>Original Type</th>
                                <th>Revised Type</th>
                                <th>Filing Date</th>
                                <th>Total Income</th>
                                <th>Tax Payable</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($revisedReturns) > 0): ?>
                                <?php foreach ($revisedReturns as $return): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($return['client_name']) ?></td>
                                        <td><?= htmlspecialchars($return['client_pan']) ?></td>
                                        <td><?= htmlspecialchars($return['assessment_year']) ?></td>
                                        <td><?= htmlspecialchars($return['original_return_type']) ?></td>
                                        <td><?= htmlspecialchars($return['return_type']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($return['filing_date'])) ?></td>
                                        <td>₹<?= number_format($return['total_income'], 2) ?></td>
                                        <td>₹<?= number_format($return['tax_payable'], 2) ?></td>
                                        <td>
                                            <a href="?action=view&id=<?= $return['id'] ?>" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?action=edit&id=<?= $return['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?= $return['id'] ?>)" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="../original_return/original_return.php?action=view&id=<?= $return['original_return_id'] ?>" class="btn btn-secondary btn-sm" title="View Original">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No revised returns found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <!-- Add/Edit Revised Return Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><?= ($action == 'add') ? 'File Revised Return' : 'Edit Revised Return' ?></h6>
            </div>
            <div class="card-body">
                <form action="revised_return_process.php" method="POST" class="ajax-form">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $revisedReturn['id'] ?>">
                    <?php endif; ?>
                    <input type="hidden" name="original_return_id" value="<?= $originalId ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">Original Return Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Client:</strong> <?= htmlspecialchars($client['name']) ?> (<?= htmlspecialchars($client['pan']) ?>)</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Assessment Year:</strong> <?= htmlspecialchars($originalReturn['assessment_year']) ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Return Type:</strong> <?= htmlspecialchars($originalReturn['return_type']) ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Total Income:</strong> ₹<?= number_format($originalReturn['total_income'], 2) ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Tax Payable:</strong> ₹<?= number_format($originalReturn['tax_payable'], 2) ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Filing Date:</strong> <?= date('d-m-Y', strtotime($originalReturn['filing_date'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="return_type" class="form-label required-field">Return Type</label>
                            <select class="form-select" id="return_type" name="return_type" required>
                                <option value="">Select Return Type</option>
                                <?php foreach ($returnTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= (isset($revisedReturn['return_type']) && $revisedReturn['return_type'] == $type) ? 'selected' : '' ?>>
                                        <?= $type ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="filing_date" class="form-label required-field">Filing Date</label>
                            <input type="date" class="form-control" id="filing_date" name="filing_date" required value="<?= isset($revisedReturn['filing_date']) ? substr($revisedReturn['filing_date'], 0, 10) : date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="total_income" class="form-label required-field">Total Income (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="total_income" name="total_income" required value="<?= isset($revisedReturn['total_income']) ? $revisedReturn['total_income'] : $originalReturn['total_income'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="tax_payable" class="form-label required-field">Tax Payable (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="tax_payable" name="tax_payable" required value="<?= isset($revisedReturn['tax_payable']) ? $revisedReturn['tax_payable'] : $originalReturn['tax_payable'] ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="acknowledgement_no" class="form-label required-field">Acknowledgement Number</label>
                            <input type="text" class="form-control" id="acknowledgement_no" name="acknowledgement_no" required value="<?= isset($revisedReturn['acknowledgement_no']) ? htmlspecialchars($revisedReturn['acknowledgement_no']) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="filing_type" class="form-label required-field">Filing Type</label>
                            <select class="form-select" id="filing_type" name="filing_type" required>
                                <option value="">Select Filing Type</option>
                                <option value="online" <?= (isset($revisedReturn['filing_type']) && $revisedReturn['filing_type'] == 'online') ? 'selected' : '' ?>>Online</option>
                                <option value="offline" <?= (isset($revisedReturn['filing_type']) && $revisedReturn['filing_type'] == 'offline') ? 'selected' : '' ?>>Offline</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="revision_reason" class="form-label required-field">Reason for Revision</label>
                            <select class="form-select" id="revision_reason" name="revision_reason" required>
                                <option value="">Select Reason</option>
                                <?php foreach ($reasons as $key => $reason): ?>
                                    <option value="<?= $key ?>" <?= (isset($revisedReturn['revision_reason']) && $revisedReturn['revision_reason'] == $key) ? 'selected' : '' ?>>
                                        <?= $reason ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tax_paid_date" class="form-label">Tax Paid Date</label>
                            <input type="date" class="form-control" id="tax_paid_date" name="tax_paid_date" value="<?= isset($revisedReturn['tax_paid_date']) ? substr($revisedReturn['tax_paid_date'], 0, 10) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="challan_no" class="form-label">Challan Number</label>
                            <input type="text" class="form-control" id="challan_no" name="challan_no" value="<?= isset($revisedReturn['challan_no']) ? htmlspecialchars($revisedReturn['challan_no']) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="additional_tax" class="form-label">Additional Tax Paid (if any)</label>
                            <input type="number" step="0.01" class="form-control" id="additional_tax" name="additional_tax" value="<?= isset($revisedReturn['additional_tax']) ? $revisedReturn['additional_tax'] : '0.00' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"><?= isset($revisedReturn['remarks']) ? htmlspecialchars($revisedReturn['remarks']) : '' ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= isset($originalId) ? '../original_return/original_return.php?action=view&id=' . $originalId : 'revised_return.php' ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <?= ($action == 'add') ? 'File Revised Return' : 'Update Revised Return' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($action == 'view' && $revisedReturn): ?>
        <!-- View Revised Return Details -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Revised Return Details</h6>
                <div>
                    <a href="?action=edit&id=<?= $revisedReturn['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="../original_return/original_return.php?action=view&id=<?= $revisedReturn['original_return_id'] ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-file-invoice"></i> View Original
                    </a>
                    <a href="javascript:void(0);" onclick="printReturnDetails()" class="btn btn-info btn-sm">
                        <i class="fas fa-print"></i> Print
                    </a>
                    <a href="revised_return.php" class="btn btn-secondary btn-sm">
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
                                <td><?= htmlspecialchars($revisedReturn['client_name']) ?></td>
                            </tr>
                            <tr>
                                <th>PAN</th>
                                <td><?= htmlspecialchars($revisedReturn['client_pan']) ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Return Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Assessment Year</th>
                                <td><?= htmlspecialchars($revisedReturn['assessment_year']) ?></td>
                            </tr>
                            <tr>
                                <th>Original Return Type</th>
                                <td><?= htmlspecialchars($revisedReturn['original_return_type']) ?></td>
                            </tr>
                            <tr>
                                <th>Revised Return Type</th>
                                <td><?= htmlspecialchars($revisedReturn['return_type']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Revision Details</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Original Return</th>
                                        <th>Revised Return</th>
                                        <th>Difference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>Filing Date</th>
                                        <td><?= date('d-m-Y', strtotime($revisedReturn['original_filing_date'])) ?></td>
                                        <td><?= date('d-m-Y', strtotime($revisedReturn['filing_date'])) ?></td>
                                        <td><?= round((strtotime($revisedReturn['filing_date']) - strtotime($revisedReturn['original_filing_date'])) / 86400) ?> days</td>
                                    </tr>
                                    <tr>
                                        <th>Total Income</th>
                                        <td>₹<?= number_format($revisedReturn['original_income'], 2) ?></td>
                                        <td>₹<?= number_format($revisedReturn['total_income'], 2) ?></td>
                                        <td class="<?= ($revisedReturn['total_income'] > $revisedReturn['original_income']) ? 'text-success' : 'text-danger' ?>">
                                            <?= ($revisedReturn['total_income'] >= $revisedReturn['original_income']) ? '+' : '' ?>₹<?= number_format($revisedReturn['total_income'] - $revisedReturn['original_income'], 2) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tax Payable</th>
                                        <td>₹<?= number_format($revisedReturn['original_tax'], 2) ?></td>
                                        <td>₹<?= number_format($revisedReturn['tax_payable'], 2) ?></td>
                                        <td class="<?= ($revisedReturn['tax_payable'] > $revisedReturn['original_tax']) ? 'text-danger' : 'text-success' ?>">
                                            <?= ($revisedReturn['tax_payable'] >= $revisedReturn['original_tax']) ? '+' : '' ?>₹<?= number_format($revisedReturn['tax_payable'] - $revisedReturn['original_tax'], 2) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Acknowledgement Number</th>
                                        <td><?= htmlspecialchars($revisedReturn['original_ack']) ?></td>
                                        <td><?= htmlspecialchars($revisedReturn['acknowledgement_no']) ?></td>
                                        <td>-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Additional Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Reason for Revision</th>
                                <td><?= isset($reasons[$revisedReturn['revision_reason']]) ? $reasons[$revisedReturn['revision_reason']] : $revisedReturn['revision_reason'] ?></td>
                            </tr>
                            <tr>
                                <th>Filing Type</th>
                                <td><?= ucfirst(htmlspecialchars($revisedReturn['filing_type'])) ?></td>
                            </tr>
                            <tr>
                                <th>Additional Tax Paid</th>
                                <td>₹<?= number_format($revisedReturn['additional_tax'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Tax Paid Date</th>
                                <td><?= $revisedReturn['tax_paid_date'] ? date('d-m-Y', strtotime($revisedReturn['tax_paid_date'])) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Challan Number</th>
                                <td><?= htmlspecialchars($revisedReturn['challan_no'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php if (!empty($revisedReturn['remarks'])): ?>
                    <div class="col-md-6">
                        <h5>Remarks</h5>
                        <div class="card">
                            <div class="card-body">
                                <?= nl2br(htmlspecialchars($revisedReturn['remarks'])) ?>
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
    // Confirm return deletion
    function confirmDelete(returnId) {
        if (confirm('Are you sure you want to delete this revised return? This action cannot be undone.')) {
            window.location.href = 'revised_return_process.php?action=delete&id=' + returnId;
        }
    }
    
    // Print return details
    function printReturnDetails() {
        const printContents = document.getElementById('return-details-print').innerHTML;
        const originalContents = document.body.innerHTML;
        
        document.body.innerHTML = `
            <div class="container mt-4">
                <h2 class="text-center mb-4">Revised Return Details</h2>
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
</script>

<?php include '../../includes/footer.php'; ?>
