<?php
$currentPage = 'error';
$title = "404 - Page Not Found";
ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1">404</h1>
            <h2>Page Not Found</h2>
            <p class="lead">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
            <a href="/" class="btn btn-primary">Go to Homepage</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
