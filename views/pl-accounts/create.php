<?php
$currentPage = 'pl-accounts';
$title = "Create P&L Account - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create New P&L Account</h1>
        <a href="/pl-accounts" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="/pl-accounts/store" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="pan" class="form-label">PAN Number</label>
                        <select class="form-select" id="pan" name="pan" required>
                            <option value="">Select PAN Number</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo htmlspecialchars($client['PAN']); ?>">
                                    <?php echo htmlspecialchars($client['PAN'] . ' - ' . $client['NAME']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="assessment_year" class="form-label">Assessment Year</label>
                        <input type="text" class="form-control" id="assessment_year" name="assessment_year" 
                               placeholder="e.g., 2023-24" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="gross_profit" class="form-label">Gross Profit</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="gross_profit" name="gross_profit" 
                                   value="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="indirect_income" class="form-label">Indirect Income</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="indirect_income" name="indirect_income" 
                                   value="0.00" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="indirect_expenses" class="form-label">Indirect Expenses</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="indirect_expenses" name="indirect_expenses" 
                                   value="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="net_profit" class="form-label">Net Profit</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" id="net_profit" name="net_profit" 
                                   value="0.00" readonly>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create P&L Account
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
        const gross = parseFloat(grossProfit.value) || 0;
        const income = parseFloat(indirectIncome.value) || 0;
        const expenses = parseFloat(indirectExpenses.value) || 0;
        const net = gross + income - expenses;
        netProfit.value = net.toFixed(2);
    }

    grossProfit.addEventListener('input', calculateNetProfit);
    indirectIncome.addEventListener('input', calculateNetProfit);
    indirectExpenses.addEventListener('input', calculateNetProfit);
});
</script> 
