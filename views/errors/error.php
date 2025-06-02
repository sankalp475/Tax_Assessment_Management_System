<?php
$title = "Error - Tax Assessment System";
$currentPage = 'error';
ob_start();
?>

<div class="container-fluid">
    <div class="text-center mt-5">
        <h1 class="display-1 text-danger">Error</h1>
        <p class="lead">An error occurred while processing your request.</p>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        <a href="/" class="btn btn-primary">Return to Dashboard</a>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?> 
