<?php
$currentPage = 'reports';
$title = "Generate Report - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Generate Report</h1>
        <a href="/reports" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="/reports/generate" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="pan" class="form-label">Client</label>
                        <select class="form-select" id="pan" name="pan" required>
                            <option value="">Select Client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo htmlspecialchars($client['PAN']); ?>">
                                    <?php echo htmlspecialchars($client['PAN'] . ' - ' . $client['NAME']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="assessment_year" class="form-label">Assessment Year</label>
                        <input type="text" class="form-control" id="assessment_year" name="assessment_year" 
                               placeholder="e.g., 2023-24" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Report Type</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" 
                                           id="tax_return" value="tax_return" required>
                                    <label class="form-check-label" for="tax_return">
                                        Tax Return Report
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" 
                                           id="trading_account" value="trading_account">
                                    <label class="form-check-label" for="trading_account">
                                        Trading Account Report
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" 
                                           id="pl_account" value="pl_account">
                                    <label class="form-check-label" for="pl_account">
                                        P&L Account Report
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" 
                                           id="balance_sheet" value="balance_sheet">
                                    <label class="form-check-label" for="balance_sheet">
                                        Balance Sheet Report
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" 
                                           id="comprehensive" value="comprehensive">
                                    <label class="form-check-label" for="comprehensive">
                                        Comprehensive Report
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 
