<?php
$title = "Balance Sheets - Tax Assessment System";
$currentPage = 'balance-sheets';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Balance Sheets</h1>
    <a href="/balance-sheets/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Create New Balance Sheet
    </a>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>PAN</th>
                        <th>Assessment Year</th>
                        <th>Current Assets</th>
                        <th>Fixed Assets</th>
                        <th>Current Liabilities</th>
                        <th>Long Term Liabilities</th>
                        <th>Equity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($balanceSheets && count($balanceSheets) > 0): ?>
                        <?php foreach($balanceSheets as $sheet): 
                            $assets = json_decode($sheet['ASSETS'], true) ?? [];
                            $liabilities = json_decode($sheet['LIABILITIES'], true) ?? [];
                            
                            $currentAssets = $assets['current_assets'] ?? 0;
                            $fixedAssets = $assets['fixed_assets'] ?? 0;
                            $currentLiabilities = $liabilities['current_liabilities'] ?? 0;
                            $longTermLiabilities = $liabilities['long_term_liabilities'] ?? 0;
                            
                            $equity = ($currentAssets + $fixedAssets) - ($currentLiabilities + $longTermLiabilities);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sheet['PAN']); ?></td>
                                <td><?php echo htmlspecialchars($sheet['ASSESSMENT_YEAR']); ?></td>
                                <td class="text-end"><?php echo number_format($currentAssets, 2); ?></td>
                                <td class="text-end"><?php echo number_format($fixedAssets, 2); ?></td>
                                <td class="text-end"><?php echo number_format($currentLiabilities, 2); ?></td>
                                <td class="text-end"><?php echo number_format($longTermLiabilities, 2); ?></td>
                                <td class="text-end"><?php echo number_format($equity, 2); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/balance-sheets/edit/<?php echo $sheet['ID']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="/balance-sheets/delete/<?php echo $sheet['ID']; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this balance sheet?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No balance sheets found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
