<?php
$currentPage = 'pl-accounts';
$title = "Edit P&L Account - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit P&L Account</h1>
        <a href="/pl-accounts/view/<?php echo htmlspecialchars($plAccount['id']); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Details
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="/pl-accounts/update" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($plAccount['id']); ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="pan" class="form-label">PAN Number</label>
                        <select class="form-select" id="pan" name="pan" required>
                            <option value="">Select PAN Number</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo htmlspecialchars($client['PAN']); ?>" 
                                        <?php echo $client['PAN'] === $plAccount['pan'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($client['PAN'] . ' - ' . $client['NAME']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="assessment_year" class="form-label">Assessment Year</label>
                        <input type="text" class="form-control" id="assessment_year" name="assessment_year" 
                               value="<?php echo htmlspecialchars($plAccount['assessment_year']); ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="gross_profit" class="form-label">Gross Profit</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="gross_profit" name="gross_profit" 
                                   value="<?php echo htmlspecialchars($plAccount['gross_profit']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="indirect_income" class="form-label">Indirect Income</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="indirect_income" name="indirect_income" 
                                   value="<?php echo htmlspecialchars($plAccount['indirect_income']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="indirect_expenses" class="form-label">Indirect Expenses</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="indirect_expenses" name="indirect_expenses" 
                                   value="<?php echo htmlspecialchars($plAccount['indirect_expenses']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="net_profit" class="form-label">Net Profit</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="net_profit" name="net_profit" 
                                   value="<?php echo htmlspecialchars($plAccount['net_profit']); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update P&L Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grossProfit = document.getElementById('gross_profit');
    const indirectIncome = document.getElementById('indirect_income');
    const indirectExpenses = document.getElementById('indirect_expenses');
    const netProfit = document.getElementById('net_profit');

    function calculateNetProfit() {
        const gp = parseFloat(grossProfit.value) || 0;
        const ii = parseFloat(indirectIncome.value) || 0;
        const ie = parseFloat(indirectExpenses.value) || 0;
        const np = gp + ii - ie;
        netProfit.value = np.toFixed(2);
    }

    grossProfit.addEventListener('input', calculateNetProfit);
    indirectIncome.addEventListener('input', calculateNetProfit);
    indirectExpenses.addEventListener('input', calculateNetProfit);
});
</script> 
