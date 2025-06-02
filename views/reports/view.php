<?php
$currentPage = 'reports';
$title = "View Report - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Report Details</h1>
        <div>
            <a href="/reports" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-center mb-4">
                <h2 class="h4"><?php echo htmlspecialchars($client['NAME']); ?></h2>
                <p class="text-muted">PAN: <?php echo htmlspecialchars($client['PAN']); ?></p>
                <p class="text-muted">Assessment Year: <?php echo htmlspecialchars($assessment_year); ?></p>
            </div>

            <?php if ($report_type === 'tax_return' && isset($tax_return)): ?>
                <div class="mb-4">
                    <h3 class="h5 mb-3">Tax Return Details</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Return Type</th>
                                <td><?php echo $tax_return['RETURN_ORIGINAL_REVISED'] ? 'Revised' : 'Original'; ?></td>
                            </tr>
                            <tr>
                                <th>Taxable Income</th>
                                <td>₹<?php echo number_format($tax_return['TAXABLE_INCOME'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Tax Paid</th>
                                <td>₹<?php echo number_format($tax_return['TAX_PAID'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($report_type === 'trading_account' && isset($trading_account)): ?>
                <div class="mb-4">
                    <h3 class="h5 mb-3">Trading Account Details</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Opening Stock</th>
                                <td>₹<?php echo number_format($trading_account['OPENING_STOCK'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Purchases</th>
                                <td>₹<?php echo number_format($trading_account['PURCHASES'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Direct Expenses</th>
                                <td>₹<?php echo number_format($trading_account['DIRECT_EXPENSES'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Closing Stock</th>
                                <td>₹<?php echo number_format($trading_account['CLOSING_STOCK'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Gross Profit</th>
                                <td>₹<?php echo number_format($trading_account['GROSS_PROFIT'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($report_type === 'pl_account' && isset($pl_account)): ?>
                <div class="mb-4">
                    <h3 class="h5 mb-3">Profit & Loss Account Details</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Gross Profit</th>
                                <td>₹<?php echo number_format($pl_account['GROSS_PROFIT'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Indirect Income</th>
                                <td>₹<?php echo number_format($pl_account['INDIRECT_INCOME'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Indirect Expenses</th>
                                <td>₹<?php echo number_format($pl_account['INDIRECT_EXPENSES'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Net Profit</th>
                                <td>₹<?php echo number_format($pl_account['NET_PROFIT'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($report_type === 'balance_sheet' && isset($balance_sheet)): ?>
                <div class="mb-4">
                    <h3 class="h5 mb-3">Balance Sheet Details</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="h6">Assets</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><?php echo nl2br(htmlspecialchars($balance_sheet['ASSETS'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4 class="h6">Liabilities</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><?php echo nl2br(htmlspecialchars($balance_sheet['LIABILITIES'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($report_type === 'comprehensive'): ?>
                <div class="mb-4">
                    <h3 class="h5 mb-3">Comprehensive Report</h3>
                    <?php if (isset($tax_return)): ?>
                        <div class="mb-4">
                            <h4 class="h6">Tax Return Details</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 200px;">Return Type</th>
                                        <td><?php echo $tax_return['RETURN_ORIGINAL_REVISED'] ? 'Revised' : 'Original'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Taxable Income</th>
                                        <td>₹<?php echo number_format($tax_return['TAXABLE_INCOME'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tax Paid</th>
                                        <td>₹<?php echo number_format($tax_return['TAX_PAID'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($trading_account)): ?>
                        <div class="mb-4">
                            <h4 class="h6">Trading Account Details</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 200px;">Opening Stock</th>
                                        <td>₹<?php echo number_format($trading_account['OPENING_STOCK'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Purchases</th>
                                        <td>₹<?php echo number_format($trading_account['PURCHASES'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Direct Expenses</th>
                                        <td>₹<?php echo number_format($trading_account['DIRECT_EXPENSES'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Closing Stock</th>
                                        <td>₹<?php echo number_format($trading_account['CLOSING_STOCK'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Gross Profit</th>
                                        <td>₹<?php echo number_format($trading_account['GROSS_PROFIT'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($pl_account)): ?>
                        <div class="mb-4">
                            <h4 class="h6">Profit & Loss Account Details</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 200px;">Gross Profit</th>
                                        <td>₹<?php echo number_format($pl_account['GROSS_PROFIT'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Indirect Income</th>
                                        <td>₹<?php echo number_format($pl_account['INDIRECT_INCOME'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Indirect Expenses</th>
                                        <td>₹<?php echo number_format($pl_account['INDIRECT_EXPENSES'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Net Profit</th>
                                        <td>₹<?php echo number_format($pl_account['NET_PROFIT'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($balance_sheet)): ?>
                        <div class="mb-4">
                            <h4 class="h6">Balance Sheet Details</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="h6">Assets</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><?php echo nl2br(htmlspecialchars($balance_sheet['ASSETS'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="h6">Liabilities</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><?php echo nl2br(htmlspecialchars($balance_sheet['LIABILITIES'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, .sidebar {
        display: none !important;
    }
    .container-fluid {
        width: 100%;
        padding: 0;
        margin: 0;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style> 
