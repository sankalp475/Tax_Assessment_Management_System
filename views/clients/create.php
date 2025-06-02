<?php
$title = "Create Client - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Create Client</h1>
    <a href="/clients" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="/clients/store" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pan" class="form-label">PAN Number</label>
                    <input type="text" class="form-control" id="pan" name="pan" value="<?php echo htmlspecialchars($_POST['pan'] ?? ''); ?>" required pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}">
                    <div class="invalid-feedback">
                        Please enter a valid PAN number (e.g., ABCDE1234F)
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    <div class="invalid-feedback">
                        Please enter the client's name
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    <div class="invalid-feedback">
                        Please enter a valid email address
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required pattern="[0-9]{10}">
                    <div class="invalid-feedback">
                        Please enter a valid 10-digit phone number
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Individual" <?php echo ($_POST['category'] ?? '') === 'Individual' ? 'selected' : ''; ?>>Individual</option>
                    <option value="HUF" <?php echo ($_POST['category'] ?? '') === 'HUF' ? 'selected' : ''; ?>>HUF</option>
                    <option value="Partnership Firm" <?php echo ($_POST['category'] ?? '') === 'Partnership Firm' ? 'selected' : ''; ?>>Partnership Firm</option>
                    <option value="LLP" <?php echo ($_POST['category'] ?? '') === 'LLP' ? 'selected' : ''; ?>>LLP</option>
                    <option value="Company" <?php echo ($_POST['category'] ?? '') === 'Company' ? 'selected' : ''; ?>>Company</option>
                    <option value="AOP/BOI" <?php echo ($_POST['category'] ?? '') === 'AOP/BOI' ? 'selected' : ''; ?>>AOP/BOI</option>
                    <option value="Trust" <?php echo ($_POST['category'] ?? '') === 'Trust' ? 'selected' : ''; ?>>Trust</option>
                </select>
                <div class="invalid-feedback">
                    Please select a client category
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create Client</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
