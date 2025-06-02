<?php
$currentPage = 'returns';
$title = "View Tax Return - Tax Assessment System";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Tax Return Details</h1>
        <div>
            <a href="/returns/edit/<?php echo htmlspecialchars($taxReturn['id']); ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="/returns" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Client Information</h5>
                </div>
                <div class="card-body">
                    <?php if ($client): ?>
                        <table class="table">
                            <tr>
                                <th>Name</th>
                                <td><?php echo htmlspecialchars($client['name']); ?></td>
                            </tr>
                            <tr>
                                <th>PAN</th>
                                <td><?php echo htmlspecialchars($client['pan']); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td><?php echo htmlspecialchars($client['phone']); ?></td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td><?php echo htmlspecialchars($client['address']); ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">Client information not found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tax Return Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Assessment Year</th>
                            <td><?php echo htmlspecialchars($taxReturn['assessment_year']); ?></td>
                        </tr>
                        <tr>
                            <th>Return Type</th>
                            <td><?php echo $taxReturn['return_original_revised'] ? 'Revised' : 'Original'; ?></td>
                        </tr>
                        <tr>
                            <th>Gross Income</th>
                            <td>₹<?php echo number_format($taxReturn['gross_income'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Deductions</th>
                            <td>₹<?php echo number_format($taxReturn['deductions'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Taxable Income</th>
                            <td>₹<?php echo number_format($taxReturn['taxable_income'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Tax Paid</th>
                            <td>₹<?php echo number_format($taxReturn['tax_paid'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?php echo date('d M Y H:i', strtotime($taxReturn['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td><?php echo date('d M Y H:i', strtotime($taxReturn['updated_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 
