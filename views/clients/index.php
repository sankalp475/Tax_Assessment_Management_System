<?php
$title = "Clients - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Clients</h1>
    <a href="/clients/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Client
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>PAN</th>
                        <th>Name</th>
                        <th>Father's Name</th>
                        <th>Category</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clients)): ?>
                        <?php foreach($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['PAN']); ?></td>
                            <td><?php echo htmlspecialchars($client['NAME']); ?></td>
                            <td><?php echo htmlspecialchars($client['FATHERS_NAME']); ?></td>
                            <td><?php echo htmlspecialchars($client['CATEGORY']); ?></td>
                            <td><?php echo htmlspecialchars($client['PHONE']); ?></td>
                            <td><?php echo htmlspecialchars($client['EMAIL']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($client['CREATED_AT'])); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/clients/view/<?php echo htmlspecialchars($client['PAN']); ?>" class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="/clients/edit/<?php echo htmlspecialchars($client['PAN']); ?>" class="btn btn-sm btn-outline-primary" title="Edit Client">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form action="/clients/delete/<?php echo htmlspecialchars($client['PAN']); ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Client">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No clients found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
