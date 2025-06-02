<?php
$currentPage = 'pl-accounts';
$title = "P&L Accounts - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">P&L Accounts</h1>
        <a href="/pl-accounts/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New P&L Account
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>PAN</th>
                            <th>Assessment Year</th>
                            <th>Gross Profit</th>
                            <th>Indirect Income</th>
                            <th>Indirect Expenses</th>
                            <th>Net Profit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($accounts)): ?>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($account['ID'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($account['PAN'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($account['ASSESSMENT_YEAR'] ?? ''); ?></td>
                                    <td>₹<?php echo number_format($account['GROSS_PROFIT'] ?? 0, 2); ?></td>
                                    <td>₹<?php echo number_format($account['INDIRECT_INCOME'] ?? 0, 2); ?></td>
                                    <td>₹<?php echo number_format($account['INDIRECT_EXPENSES'] ?? 0, 2); ?></td>
                                    <td>₹<?php echo number_format($account['NET_PROFIT'] ?? 0, 2); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/pl-accounts/view/<?php echo htmlspecialchars($account['ID'] ?? ''); ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="/pl-accounts/edit/<?php echo htmlspecialchars($account['ID'] ?? ''); ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="/pl-accounts/delete/<?php echo htmlspecialchars($account['ID'] ?? ''); ?>" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this P&L account?');">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
</div> 
