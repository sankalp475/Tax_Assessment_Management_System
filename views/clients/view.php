<?php
$title = "View Client - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Client Details</h1>
    <div>
        <a href="/clients/edit/<?php echo htmlspecialchars($client['pan']); ?>" class="btn btn-primary">
            <i class="bi bi-pencil-fill"></i> Edit Client
        </a>
        <a href="/clients" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <dl class="row">
                    <dt class="col-sm-4">PAN Number</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($client['pan']); ?></dd>

                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($client['name']); ?></dd>

                    <dt class="col-sm-4">Father's Name</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($client['fathers_name']); ?></dd>

                    <dt class="col-sm-4">Date of Birth</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($client['dob']); ?></dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row">
                    <dt class="col-sm-4">Category</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($client['category']); ?></dd>

                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($client['phone']); ?></dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($client['email']); ?></dd>

                    <dt class="col-sm-4">Created At</dt>
                    <dd class="col-sm-8"><?php echo date('Y-m-d H:i', strtotime($client['created_at'])); ?></dd>
                </dl>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <dt>Address</dt>
                <dd><?php echo nl2br(htmlspecialchars($client['address'])); ?></dd>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
