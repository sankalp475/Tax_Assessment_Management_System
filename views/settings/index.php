<?php
$currentPage = 'settings';
$title = "Settings - Tax Assessment System";
?>

<div class="container mt-4">
    <h2 class="mb-4">Settings</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Settings</h5>
                </div>
                <div class="card-body">
                    <form action="/settings/update" method="POST">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="company_address" class="form-label">Company Address</label>
                            <textarea class="form-control" id="company_address" name="company_address" rows="3"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="company_phone" class="form-label">Company Phone</label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone" value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="company_email" class="form-label">Company Email</label>
                            <input type="email" class="form-control" id="company_email" name="company_email" value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="tax_year_start" class="form-label">Tax Year Start Date</label>
                            <input type="date" class="form-control" id="tax_year_start" name="tax_year_start" value="<?php echo htmlspecialchars($settings['tax_year_start'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="tax_year_end" class="form-label">Tax Year End Date</label>
                            <input type="date" class="form-control" id="tax_year_end" name="tax_year_end" value="<?php echo htmlspecialchars($settings['tax_year_end'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    <p><strong>Database:</strong> MySQL</p>
                    <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                    <p><strong>Last Updated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div> 
