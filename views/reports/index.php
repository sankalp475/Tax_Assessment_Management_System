<?php
$currentPage = 'reports';
$title = "Reports - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Reports</h1>
        <a href="/reports/generate" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Generate New Report
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-file-earmark-text"></i> Tax Returns
                    </h5>
                    <p class="card-text">Generate reports for tax returns, including taxable income and tax paid details.</p>
                    <a href="/reports/generate?type=tax_return" class="btn btn-outline-primary">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-calculator"></i> Trading Accounts
                    </h5>
                    <p class="card-text">View trading account reports with opening stock, purchases, and gross profit details.</p>
                    <a href="/reports/generate?type=trading_account" class="btn btn-outline-primary">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-graph-up"></i> P&L Accounts
                    </h5>
                    <p class="card-text">Generate profit and loss reports showing gross profit, indirect income, and expenses.</p>
                    <a href="/reports/generate?type=pl_account" class="btn btn-outline-primary">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-journal-text"></i> Balance Sheets
                    </h5>
                    <p class="card-text">View balance sheet reports with assets and liabilities information.</p>
                    <a href="/reports/generate?type=balance_sheet" class="btn btn-outline-primary">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-file-earmark-bar-graph"></i> Comprehensive Reports
                    </h5>
                    <p class="card-text">Generate comprehensive reports including all financial statements for a client.</p>
                    <a href="/reports/generate?type=comprehensive" class="btn btn-outline-primary">
                        Generate Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>
 