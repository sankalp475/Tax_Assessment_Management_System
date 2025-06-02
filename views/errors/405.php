<?php
$title = "Method Not Allowed - Tax Assessment System";
$currentPage = 'error';
ob_start();
?>

<div class="container-fluid">
    <div class="text-center mt-5">
        <h1 class="display-1 text-danger">405</h1>
        <p class="lead">Method Not Allowed</p>
        <p>The requested method is not allowed for this URL.</p>
        <a href="/" class="btn btn-primary">Return to Dashboard</a>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
