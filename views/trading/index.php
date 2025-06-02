<?php
$title = "Trading Accounts - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Trading Accounts</h1>
    <a href="/trading/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New Trading Account
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>PAN</th>
                        <th>Assessment Years</th>
                        <th>Opening Stock</th>
                        <th>Purchases</th>
                        <th>Direct Expenses</th>
                        <th>Closing Stock</th>
                        <th>Gross Profit</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tradingAccounts as $account): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($account['pan']); ?></td>
                        <td><?php echo htmlspecialchars($account['assessment_years']); ?></td>
                        <td><?php echo number_format($account['opening_stock'], 2); ?></td>
                        <td><?php echo number_format($account['purchases'], 2); ?></td>
                        <td><?php echo number_format($account['direct_expenses'], 2); ?></td>
                        <td><?php echo number_format($account['closing_stock'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $account['gross_profit'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo number_format($account['gross_profit'], 2); ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($account['created_at'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/trading/edit/<?php echo $account['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="/trading/delete/<?php echo $account['id']; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this trading account?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
