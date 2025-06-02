<?php
$title = "Create P&L Account - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Create P&L Account</h1>
    <a href="/pl" class="btn btn-secondary">
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

        <form action="/pl/store" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pan" class="form-label">PAN Number</label>
                    <input type="text" class="form-control" id="pan" name="pan" required pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}">
                    <div class="invalid-feedback">
                        Please enter a valid PAN number (e.g., ABCDE1234F)
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="assessment_year" class="form-label">Assessment Year</label>
                    <input type="text" class="form-control" id="assessment_year" name="assessment_year" required pattern="[0-9]{4}-[0-9]{2}">
                    <div class="invalid-feedback">
                        Please enter a valid assessment year (e.g., 2023-24)
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="gross_profit" class="form-label">Gross Profit</label>
                    <input type="number" class="form-control" id="gross_profit" name="gross_profit" required step="0.01">
                    <div class="invalid-feedback">
                        Please enter the gross profit amount
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="indirect_income" class="form-label">Indirect Income</label>
                    <input type="number" class="form-control" id="indirect_income" name="indirect_income" required step="0.01">
                    <div class="invalid-feedback">
                        Please enter the indirect income amount
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="indirect_expenses" class="form-label">Indirect Expenses</label>
                    <input type="number" class="form-control" id="indirect_expenses" name="indirect_expenses" required step="0.01">
                    <div class="invalid-feedback">
                        Please enter the indirect expenses amount
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="net_profit" class="form-label">Net Profit (Auto-calculated)</label>
                    <input type="number" class="form-control" id="net_profit" name="net_profit" readonly step="0.01">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create P&L Account</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

<script>
    // Calculate Net Profit automatically
    document.getElementById('gross_profit').addEventListener('input', calculateNetProfit);
    document.getElementById('indirect_income').addEventListener('input', calculateNetProfit);
    document.getElementById('indirect_expenses').addEventListener('input', calculateNetProfit);

    function calculateNetProfit() {
        const grossProfit = parseFloat(document.getElementById('gross_profit').value) || 0;
        const indirectIncome = parseFloat(document.getElementById('indirect_income').value) || 0;
        const indirectExpenses = parseFloat(document.getElementById('indirect_expenses').value) || 0;

        const netProfit = (grossProfit + indirectIncome) - indirectExpenses;
        document.getElementById('net_profit').value = netProfit.toFixed(2);
    }
</script> 
