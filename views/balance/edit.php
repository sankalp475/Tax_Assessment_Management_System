<?php
$title = "Edit Balance Sheet - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Balance Sheet</h1>
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

        <form action="/balance-sheets/update" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($balanceSheet['ID']); ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pan" class="form-label">PAN Number</label>
                    <input type="text" class="form-control" id="pan" name="pan" value="<?php echo htmlspecialchars($balanceSheet['PAN']); ?>" required pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}">
                    <div class="invalid-feedback">
                        Please enter a valid PAN number (e.g., ABCDE1234F)
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="assessment_year" class="form-label">Assessment Year</label>
                    <input type="text" class="form-control" id="assessment_year" name="assessment_year" value="<?php echo htmlspecialchars($balanceSheet['ASSESSMENT_YEAR']); ?>" required pattern="[0-9]{4}-[0-9]{2}">
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
                        <input type="number" class="form-control" id="current_assets" name="current_assets" value="<?php echo htmlspecialchars(json_decode($balanceSheet['ASSETS'], true)['current_assets'] ?? 0); ?>" required min="0" step="0.01">
                        <div class="invalid-feedback">
                            Please enter current assets amount
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fixed_assets" class="form-label">Fixed Assets</label>
                        <input type="number" class="form-control" id="fixed_assets" name="fixed_assets" value="<?php echo htmlspecialchars(json_decode($balanceSheet['ASSETS'], true)['fixed_assets'] ?? 0); ?>" required min="0" step="0.01">
                        <div class="invalid-feedback">
                            Please enter fixed assets amount
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4 class="mb-3">Liabilities</h4>
                    <div class="mb-3">
                        <label for="current_liabilities" class="form-label">Current Liabilities</label>
                        <input type="number" class="form-control" id="current_liabilities" name="current_liabilities" value="<?php echo htmlspecialchars(json_decode($balanceSheet['LIABILITIES'], true)['current_liabilities'] ?? 0); ?>" required min="0" step="0.01">
                        <div class="invalid-feedback">
                            Please enter current liabilities amount
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="long_term_liabilities" class="form-label">Long Term Liabilities</label>
                        <input type="number" class="form-control" id="long_term_liabilities" name="long_term_liabilities" value="<?php echo htmlspecialchars(json_decode($balanceSheet['LIABILITIES'], true)['long_term_liabilities'] ?? 0); ?>" required min="0" step="0.01">
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

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Balance Sheet</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const currentAssets = document.getElementById('current_assets');
    const fixedAssets = document.getElementById('fixed_assets');
    const currentLiabilities = document.getElementById('current_liabilities');
    const longTermLiabilities = document.getElementById('long_term_liabilities');
    const equity = document.getElementById('equity');

    function calculateEquity() {
        const totalAssets = (parseFloat(currentAssets.value) || 0) + (parseFloat(fixedAssets.value) || 0);
        const totalLiabilities = (parseFloat(currentLiabilities.value) || 0) + (parseFloat(longTermLiabilities.value) || 0);
        equity.value = (totalAssets - totalLiabilities).toFixed(2);
    }

    [currentAssets, fixedAssets, currentLiabilities, longTermLiabilities].forEach(input => {
        input.addEventListener('input', calculateEquity);
    });

    // Calculate initial equity
    calculateEquity();

    // Form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
