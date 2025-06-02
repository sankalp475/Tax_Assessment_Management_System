<?php
$title = "Client Reports - Tax Assessment System";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Client Reports</h1>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Client List</h5>
                <p class="card-text">View a list of all clients with their basic information.</p>
                <a href="/reports/clients/list" class="btn btn-primary">View List</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Client Categories</h5>
                <p class="card-text">View clients grouped by their categories.</p>
                <a href="/reports/clients/categories" class="btn btn-primary">View Categories</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Client Search</h5>
                <p class="card-text">Search for specific clients by PAN, name, or other criteria.</p>
                <a href="/reports/clients/search" class="btn btn-primary">Search Clients</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Client Statistics</h5>
                <p class="card-text">View statistics and analytics about clients.</p>
                <a href="/reports/clients/statistics" class="btn btn-primary">View Statistics</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
