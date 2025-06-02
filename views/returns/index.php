<?php
$currentPage = 'returns';
$title = "Tax Returns - Tax Assessment System";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tax Returns</h2>
    <a href="/returns/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New Return
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>PAN</th>
                        <th>Assessment Year</th>
                        <th>Return Type</th>
                        <th>Gross Income</th>
                        <th>Deductions</th>
                        <th>Taxable Income</th>
                        <th>Tax Paid</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($returns)): ?>
                        <?php foreach ($returns as $return): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($return['ID']); ?></td>
                                <td><?php echo htmlspecialchars($return['PAN']); ?></td>
                                <td><?php echo htmlspecialchars($return['ASSESSMENT_YEAR']); ?></td>
                                <td><?php echo $return['RETURN_ORIGINAL_REVISED'] == 1 ? 'Original' : 'Revised'; ?></td>
                                <td>₹<?php echo number_format($return['GROSS_INCOME'], 2); ?></td>
                                <td>₹<?php echo number_format($return['DEDUCTIONS'], 2); ?></td>
                                <td>₹<?php echo number_format($return['TAXABLE_INCOME'], 2); ?></td>
                                <td>₹<?php echo number_format($return['TAX_PAID'], 2); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/returns/view/<?php echo $return['ID']; ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/returns/edit/<?php echo $return['ID']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="/returns/delete/<?php echo $return['ID']; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this tax return?');">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No tax returns found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 
