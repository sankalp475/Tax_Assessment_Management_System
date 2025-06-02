<?php
$title = "Edit Client - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Client</h1>
    <a href="/clients" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="/clients/update" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="pan" value="<?php echo htmlspecialchars($client['pan']); ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pan_display" class="form-label">PAN Number</label>
                    <input type="text" class="form-control" id="pan_display" value="<?php echo htmlspecialchars($client['pan']); ?>" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
                    <div class="invalid-feedback">
                        Please enter the client's name
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fathers_name" class="form-label">Father's Name</label>
                    <input type="text" class="form-control" id="fathers_name" name="fathers_name" value="<?php echo htmlspecialchars($client['fathers_name']); ?>" required>
                    <div class="invalid-feedback">
                        Please enter father's name
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($client['dob']); ?>" required>
                    <div class="invalid-feedback">
                        Please enter date of birth
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
                    <div class="invalid-feedback">
                        Please enter a valid email address
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>" required pattern="[0-9]{10}">
                    <div class="invalid-feedback">
                        Please enter a valid 10-digit phone number
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Individual" <?php echo $client['category'] === 'Individual' ? 'selected' : ''; ?>>Individual</option>
                    <option value="HUF" <?php echo $client['category'] === 'HUF' ? 'selected' : ''; ?>>HUF</option>
                    <option value="Partnership Firm" <?php echo $client['category'] === 'Partnership Firm' ? 'selected' : ''; ?>>Partnership Firm</option>
                    <option value="LLP" <?php echo $client['category'] === 'LLP' ? 'selected' : ''; ?>>LLP</option>
                    <option value="Company" <?php echo $client['category'] === 'Company' ? 'selected' : ''; ?>>Company</option>
                    <option value="AOP/BOI" <?php echo $client['category'] === 'AOP/BOI' ? 'selected' : ''; ?>>AOP/BOI</option>
                    <option value="Trust" <?php echo $client['category'] === 'Trust' ? 'selected' : ''; ?>>Trust</option>
                </select>
                <div class="invalid-feedback">
                    Please select a client category
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($client['address']); ?></textarea>
                <div class="invalid-feedback">
                    Please enter the client's address
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Client</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
