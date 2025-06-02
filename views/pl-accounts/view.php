<?php
$currentPage = 'pl-accounts';
$title = "P&L Account Details - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">P&L Account Details</h1>
        <div>
            <a href="/pl-accounts/edit/<?php echo htmlspecialchars($plAccount['id']); ?>" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="/pl-accounts" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <?php if (isset($client)): ?>
    <!-- Client Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-person"></i> Client Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 200px;">Name:</th>
                            <td><?php echo htmlspecialchars($client['NAME'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>PAN Number:</th>
                            <td><?php echo htmlspecialchars($client['PAN'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($client['EMAIL'] ?? ''); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 200px;">Phone:</th>
                            <td><?php echo htmlspecialchars($client['PHONE'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td><?php echo htmlspecialchars($client['ADDRESS'] ?? ''); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- P&L Account Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-calculator"></i> P&L Account Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 200px;">Assessment Year:</th>
                            <td><?php echo htmlspecialchars($plAccount['assessment_year']); ?></td>
                        </tr>
                        <tr>
                            <th>Gross Profit:</th>
                            <td>₹<?php echo number_format($plAccount['gross_profit'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Indirect Income:</th>
                            <td>₹<?php echo number_format($plAccount['indirect_income'], 2); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 200px;">Indirect Expenses:</th>
                            <td>₹<?php echo number_format($plAccount['indirect_expenses'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Net Profit:</th>
                            <td>₹<?php echo number_format($plAccount['net_profit'], 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-info-circle"></i> Additional Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 200px;">Created At:</th>
                            <td><?php echo date('d M Y H:i', strtotime($plAccount['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td><?php echo date('d M Y H:i', strtotime($plAccount['updated_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 
