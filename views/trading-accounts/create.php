<?php
$currentPage = 'trading-accounts';
$title = "Create Trading Account - Tax Assessment System";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Create New Trading Account</h2>
    <a href="/trading-accounts" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="/trading-accounts/create" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="PAN" class="form-label">PAN Number</label>
                    <input type="text" class="form-control" id="PAN" name="PAN" required>
                </div>
                <div class="col-md-3">
                    <label for="ASSES_YEAR_1" class="form-label">Assessment Year From</label>
                    <input type="text" class="form-control" id="ASSES_YEAR_1" name="ASSES_YEAR_1" required>
                </div>
                <div class="col-md-3">
                    <label for="ASSES_YEAR_2" class="form-label">Assessment Year To</label>
                    <input type="text" class="form-control" id="ASSES_YEAR_2" name="ASSES_YEAR_2" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="OPENING_STOCK" class="form-label">Opening Stock</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control" id="OPENING_STOCK" name="OPENING_STOCK" value="0.00" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="PURCHASES" class="form-label">Purchases</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control" id="PURCHASES" name="PURCHASES" value="0.00" required>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="DIRECT_EXPENSES" class="form-label">Direct Expenses</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control" id="DIRECT_EXPENSES" name="DIRECT_EXPENSES" value="0.00" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="CLOSING_STOCK" class="form-label">Closing Stock</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control" id="CLOSING_STOCK" name="CLOSING_STOCK" value="0.00" required>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="GROSS_PROFIT" class="form-label">Gross Profit</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control" id="GROSS_PROFIT" name="GROSS_PROFIT" value="0.00" required>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Trading Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate gross profit
    const calculateGrossProfit = () => {
        const openingStock = parseFloat(document.getElementById('OPENING_STOCK').value) || 0;
        const purchases = parseFloat(document.getElementById('PURCHASES').value) || 0;
        const directExpenses = parseFloat(document.getElementById('DIRECT_EXPENSES').value) || 0;
        const closingStock = parseFloat(document.getElementById('CLOSING_STOCK').value) || 0;

        const grossProfit = (openingStock + purchases + directExpenses) - closingStock;
        document.getElementById('GROSS_PROFIT').value = grossProfit.toFixed(2);
    };

    // Add event listeners to relevant fields
    ['OPENING_STOCK', 'PURCHASES', 'DIRECT_EXPENSES', 'CLOSING_STOCK'].forEach(field => {
        document.getElementById(field).addEventListener('input', calculateGrossProfit);
    });
});
</script> 
