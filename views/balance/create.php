<?php
$title = "Create Balance Sheet - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Create Balance Sheet</h1>
    <a href="/balance-sheets" class="btn btn-secondary">
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

        <form action="/balance-sheets/store" method="POST" class="needs-validation" novalidate>
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
                <div class="col-md-6">
                    <h4 class="mb-3">Assets</h4>
                    <div class="mb-3">
                        <label for="current_assets" class="form-label">Current Assets</label>
                        <input type="number" class="form-control" id="current_assets" name="current_assets" required min="0" step="0.01">
                        <div class="invalid-feedback">
                            Please enter current assets amount
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fixed_assets" class="form-label">Fixed Assets</label>
                        <input type="number" class="form-control" id="fixed_assets" name="fixed_assets" required min="0" step="0.01">
                        <div class="invalid-feedback">
                            Please enter fixed assets amount
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4 class="mb-3">Liabilities</h4>
                    <div class="mb-3">
                        <label for="current_liabilities" class="form-label">Current Liabilities</label>
                        <input type="number" class="form-control" id="current_liabilities" name="current_liabilities" required min="0" step="0.01">
                        <div class="invalid-feedback">
                            Please enter current liabilities amount
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="long_term_liabilities" class="form-label">Long Term Liabilities</label>
                        <input type="number" class="form-control" id="long_term_liabilities" name="long_term_liabilities" required min="0" step="0.01">
                        <div class="invalid-feedback">
                            Please enter long term liabilities amount
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="equity" class="form-label">Equity (Auto-calculated)</label>
                <input type="number" class="form-control" id="equity" name="equity" readonly step="0.01">
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create Balance Sheet</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

<script>
    // Calculate Equity automatically
    const inputs = ['current_assets', 'fixed_assets', 'current_liabilities', 'long_term_liabilities'];
    inputs.forEach(input => {
        document.getElementById(input).addEventListener('input', calculateEquity);
    });

    function calculateEquity() {
        const currentAssets = parseFloat(document.getElementById('current_assets').value) || 0;
        const fixedAssets = parseFloat(document.getElementById('fixed_assets').value) || 0;
        const currentLiabilities = parseFloat(document.getElementById('current_liabilities').value) || 0;
        const longTermLiabilities = parseFloat(document.getElementById('long_term_liabilities').value) || 0;

        const totalAssets = currentAssets + fixedAssets;
        const totalLiabilities = currentLiabilities + longTermLiabilities;
        const equity = totalAssets - totalLiabilities;

        document.getElementById('equity').value = equity.toFixed(2);
    }
</script> 
