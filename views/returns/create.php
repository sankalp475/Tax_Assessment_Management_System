<?php
// Start output buffering to capture content
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create Tax Return</h1>
        <a href="/returns" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="/returns/store" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pan" class="form-label">PAN Number</label>
                        <select class="form-select" id="pan" name="pan" required>
                            <option value="">Select PAN</option>
                            <?php if (isset($clients) && is_array($clients)): ?>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo htmlspecialchars($client['PAN']); ?>">
                                        <?php echo htmlspecialchars($client['PAN'] . ' - ' . $client['NAME']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="assessment_year" class="form-label">Assessment Year</label>
                        <input type="text" class="form-control" id="assessment_year" name="assessment_year" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="return_original_revised" name="return_original_revised">
                            <label class="form-check-label" for="return_original_revised">
                                Revised Return
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="gross_income" class="form-label">Gross Income</label>
                        <input type="number" step="0.01" class="form-control" id="gross_income" name="gross_income" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="deductions" class="form-label">Deductions</label>
                        <input type="number" step="0.01" class="form-control" id="deductions" name="deductions" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="taxable_income" class="form-label">Taxable Income</label>
                        <input type="number" step="0.01" class="form-control" id="taxable_income" name="taxable_income" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tax_paid" class="form-label">Tax Paid</label>
                        <input type="number" step="0.01" class="form-control" id="tax_paid" name="tax_paid" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Tax Return</button>
                    <a href="/returns" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grossIncome = document.getElementById('gross_income');
    const deductions = document.getElementById('deductions');
    const taxableIncome = document.getElementById('taxable_income');
    const taxPaid = document.getElementById('tax_paid');

    function calculateTax() {
        const gross = parseFloat(grossIncome.value) || 0;
        const deduct = parseFloat(deductions.value) || 0;
        const taxable = gross - deduct;
        taxableIncome.value = taxable.toFixed(2);

        // Simple tax calculation (you may want to implement a more complex tax calculation logic)
        let tax = 0;
        if (taxable <= 250000) {
            tax = 0;
        } else if (taxable <= 500000) {
            tax = (taxable - 250000) * 0.05;
        } else if (taxable <= 1000000) {
            tax = 12500 + (taxable - 500000) * 0.2;
        } else {
            tax = 112500 + (taxable - 1000000) * 0.3;
        }
        taxPaid.value = tax.toFixed(2);
    }

    if (grossIncome && deductions) {
        grossIncome.addEventListener('input', calculateTax);
        deductions.addEventListener('input', calculateTax);
    }
});
</script>

<?php
// Capture the content and clean the buffer
$content = ob_get_clean();
?> 
