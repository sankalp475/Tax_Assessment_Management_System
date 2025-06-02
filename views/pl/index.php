<?php
$title = "Profit & Loss Accounts - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Profit & Loss Accounts</h1>
    <a href="/pl/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New P&L Account
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>PAN</th>
                        <th>Assessment Year</th>
                        <th>Gross Profit</th>
                        <th>Indirect Income</th>
                        <th>Indirect Expenses</th>
                        <th>Net Profit</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($plAccounts)): ?>
                        <?php foreach($plAccounts as $account): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['PAN']); ?></td>
                            <td><?php echo htmlspecialchars($account['ASSESSMENT_YEAR']); ?></td>
                            <td><?php echo number_format($account['GROSS_PROFIT'], 2); ?></td>
                            <td><?php echo number_format($account['INDIRECT_INCOME'], 2); ?></td>
                            <td><?php echo number_format($account['INDIRECT_EXPENSES'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo $account['NET_PROFIT'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo number_format($account['NET_PROFIT'], 2); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($account['CREATED_AT'])); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/pl/edit/<?php echo $account['ID']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="/pl/delete/<?php echo $account['ID']; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this P&L account?');">
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
                            <td colspan="8" class="text-center">No P&L accounts found.</td>
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
