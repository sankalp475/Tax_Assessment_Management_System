<?php
$title = "Tax Assessment System";
ob_start();
?>

<div class="container mt-5">
    <div class="jumbotron">
        <h1 class="display-4">Welcome to Tax Assessment System</h1>
        <p class="lead">Manage your tax returns and client information efficiently.</p>
        <hr class="my-4">
        <p>Get started by managing your clients or tax returns.</p>
        <a class="btn btn-primary btn-lg" href="/clients" role="button">Manage Clients</a>
        <a class="btn btn-success btn-lg" href="/returns" role="button">Manage Returns</a>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layouts/main.php';
?> 
