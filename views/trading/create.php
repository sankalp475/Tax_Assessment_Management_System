<?php
$title = "Create Trading Account - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Create Trading Account</h1>
    <a href="/trading" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="/trading/store" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pan" class="form-label">PAN Number</label>
                    <input type="text" class="form-control" id="pan" name="pan" required pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}">
                    <div class="invalid-feedback">
                        Please enter a valid PAN number (e.g., ABCDE1234F)
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="assessment_years" class="form-label">Assessment Years</label>
                    <input type="text" class="form-control" id="assessment_years" name="assessment_years" required pattern="[0-9]{4}-[0-9]{2}">
                    <div class="invalid-feedback">
                        Please enter valid assessment years (e.g., 2023-24)
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="opening_stock" class="form-label">Opening Stock</label>
                    <input type="number" class="form-control" id="opening_stock" name="opening_stock" required step="0.01">
                    <div class="invalid-feedback">
                        Please enter the opening stock amount
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="purchases" class="form-label">Purchases</label>
                    <input type="number" class="form-control" id="purchases" name="purchases" required step="0.01">
                    <div class="invalid-feedback">
                        Please enter the purchases amount
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="direct_expenses" class="form-label">Direct Expenses</label>
                    <input type="number" class="form-control" id="direct_expenses" name="direct_expenses" required step="0.01">
                    <div class="invalid-feedback">
                        Please enter the direct expenses amount
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="closing_stock" class="form-label">Closing Stock</label>
                    <input type="number" class="form-control" id="closing_stock" name="closing_stock" required step="0.01">
                    <div class="invalid-feedback">
                        Please enter the closing stock amount
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="gross_profit" class="form-label">Gross Profit (Auto-calculated)</label>
                <input type="number" class="form-control" id="gross_profit" name="gross_profit" readonly step="0.01">
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create Trading Account</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

<script>
    // Calculate Gross Profit automatically
    document.getElementById('opening_stock').addEventListener('input', calculateGrossProfit);
    document.getElementById('purchases').addEventListener('input', calculateGrossProfit);
    document.getElementById('direct_expenses').addEventListener('input', calculateGrossProfit);
    document.getElementById('closing_stock').addEventListener('input', calculateGrossProfit);

    function calculateGrossProfit() {
        const openingStock = parseFloat(document.getElementById('opening_stock').value) || 0;
        const purchases = parseFloat(document.getElementById('purchases').value) || 0;
        const directExpenses = parseFloat(document.getElementById('direct_expenses').value) || 0;
        const closingStock = parseFloat(document.getElementById('closing_stock').value) || 0;

        const grossProfit = (openingStock + purchases + directExpenses) - closingStock;
        document.getElementById('gross_profit').value = grossProfit.toFixed(2);
    }
</script> 
