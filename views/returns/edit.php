<?php
$currentPage = 'returns';
$title = "Edit Tax Return - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Tax Return</h1>
        <a href="/returns/view/<?php echo htmlspecialchars($taxReturn['id']); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Details
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="/returns/update" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($taxReturn['id']); ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="pan" class="form-label">PAN Number</label>
                        <select class="form-select" id="pan" name="pan" required>
                            <option value="">Select PAN Number</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo htmlspecialchars($client['PAN']); ?>" 
                                        <?php echo $client['PAN'] === $taxReturn['pan'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($client['PAN'] . ' - ' . $client['NAME']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="assessment_year" class="form-label">Assessment Year</label>
                        <input type="text" class="form-control" id="assessment_year" name="assessment_year" 
                               value="<?php echo htmlspecialchars($taxReturn['assessment_year']); ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="return_original_revised" class="form-label">Return Type</label>
                        <select class="form-select" id="return_original_revised" name="return_original_revised" required>
                            <option value="0" <?php echo $taxReturn['return_original_revised'] == 0 ? 'selected' : ''; ?>>Original</option>
                            <option value="1" <?php echo $taxReturn['return_original_revised'] == 1 ? 'selected' : ''; ?>>Revised</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="gross_income" class="form-label">Gross Income</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="gross_income" name="gross_income" 
                                   value="<?php echo htmlspecialchars($taxReturn['gross_income']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="deductions" class="form-label">Deductions</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="deductions" name="deductions" 
                                   value="<?php echo htmlspecialchars($taxReturn['deductions']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="taxable_income" class="form-label">Taxable Income</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="taxable_income" name="taxable_income" 
                                   value="<?php echo htmlspecialchars($taxReturn['taxable_income']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tax_paid" class="form-label">Tax Paid</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="tax_paid" name="tax_paid" 
                                   value="<?php echo htmlspecialchars($taxReturn['tax_paid']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Tax Return
                    </button>
                </div>
            </form>
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

    // Calculate taxable income automatically
    document.addEventListener('DOMContentLoaded', function() {
        const grossIncome = document.getElementById('gross_income');
        const deductions = document.getElementById('deductions');
        const taxableIncome = document.getElementById('taxable_income');

        function calculateTaxableIncome() {
            const gross = parseFloat(grossIncome.value) || 0;
            const deduct = parseFloat(deductions.value) || 0;
            const taxable = gross - deduct;
            taxableIncome.value = taxable.toFixed(2);
        }

        grossIncome.addEventListener('input', calculateTaxableIncome);
        deductions.addEventListener('input', calculateTaxableIncome);
    });
</script> 
