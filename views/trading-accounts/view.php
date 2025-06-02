<?php
$currentPage = 'trading-accounts';
$title = "View Trading Account - Tax Assessment System";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Trading Account Details</h2>
    <div>
        <a href="/trading-accounts/edit/<?php echo $account['ID']; ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="/trading-accounts" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="card-title mb-4">Basic Information</h5>
                <table class="table">
                    <tr>
                        <th style="width: 200px;">PAN Number</th>
                        <td><?php echo htmlspecialchars($account['PAN']); ?></td>
                    </tr>
                    <tr>
                        <th>Assessment Year</th>
                        <td><?php echo htmlspecialchars($account['ASSES_YEAR_1'] . '-' . $account['ASSES_YEAR_2']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="card-title mb-4">Trading Account Details</h5>
                <table class="table">
                    <tr>
                        <th style="width: 200px;">Opening Stock</th>
                        <td>₹<?php echo number_format($account['OPENING_STOCK'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Purchases</th>
                        <td>₹<?php echo number_format($account['PURCHASES'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Direct Expenses</th>
                        <td>₹<?php echo number_format($account['DIRECT_EXPENSES'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Closing Stock</th>
                        <td>₹<?php echo number_format($account['CLOSING_STOCK'], 2); ?></td>
                    </tr>
                    <tr class="table-primary">
                        <th>Gross Profit</th>
                        <td>₹<?php echo number_format($account['GROSS_PROFIT'], 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h5 class="card-title mb-4">Additional Information</h5>
                <table class="table">
                    <tr>
                        <th style="width: 200px;">Created At</th>
                        <td><?php echo date('F j, Y, g:i a', strtotime($account['CREATED_AT'])); ?></td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td><?php echo date('F j, Y, g:i a', strtotime($account['UPDATED_AT'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div> 
