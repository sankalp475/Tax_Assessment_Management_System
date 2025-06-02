<?php
$title = "Financial Reports - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Financial Reports</h1>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Trading Account Reports</h5>
                <p class="card-text">View and analyze trading account reports for all clients.</p>
                <a href="/reports/trading" class="btn btn-primary">View Reports</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Profit & Loss Reports</h5>
                <p class="card-text">View and analyze P&L reports for all clients.</p>
                <a href="/reports/pl" class="btn btn-primary">View Reports</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Balance Sheet Reports</h5>
                <p class="card-text">View and analyze balance sheet reports for all clients.</p>
                <a href="/reports/balance" class="btn btn-primary">View Reports</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Tax Return Reports</h5>
                <p class="card-text">View and analyze tax return reports for all clients.</p>
                <a href="/reports/returns" class="btn btn-primary">View Reports</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
