<?php
$currentPage = 'trading-accounts';
$title = "Trading Accounts - Tax Assessment System";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Trading Accounts</h2>
    <a href="/trading-accounts/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New Trading Account
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
                        <th>Opening Stock</th>
                        <th>Purchases</th>
                        <th>Direct Expenses</th>
                        <th>Closing Stock</th>
                        <th>Gross Profit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tradingAccounts)): ?>
                        <?php foreach ($tradingAccounts as $account): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($account['ID']); ?></td>
                                <td><?php echo htmlspecialchars($account['PAN']); ?></td>
                                <td><?php echo htmlspecialchars($account['ASSES_YEAR_1'] . '-' . $account['ASSES_YEAR_2']); ?></td>
                                <td>₹<?php echo number_format($account['OPENING_STOCK'], 2); ?></td>
                                <td>₹<?php echo number_format($account['PURCHASES'], 2); ?></td>
                                <td>₹<?php echo number_format($account['DIRECT_EXPENSES'], 2); ?></td>
                                <td>₹<?php echo number_format($account['CLOSING_STOCK'], 2); ?></td>
                                <td>₹<?php echo number_format($account['GROSS_PROFIT'], 2); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/trading-accounts/view/<?php echo $account['ID']; ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/trading-accounts/edit/<?php echo $account['ID']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="/trading-accounts/delete/<?php echo $account['ID']; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this trading account?');">
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
                            <td colspan="9" class="text-center">No trading accounts found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 
