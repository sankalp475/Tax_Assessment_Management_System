<?php
/**
 * Navigation Bar Template
 */

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-file-invoice-dollar me-2"></i>
            Tax Assessment Management System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'index.php') ? 'active' : '' ?>" href="index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= (strpos($currentPage, 'client') !== false) ? 'active' : '' ?>" href="#" id="clientDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-1"></i> Client Information
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="clientDropdown">
                        <li><a class="dropdown-item" href="modules/client/client.php">View All Clients</a></li>
                        <li><a class="dropdown-item" href="modules/client/client.php?action=add">Add New Client</a></li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= (strpos($currentPage, 'return') !== false) ? 'active' : '' ?>" href="#" id="returnDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-invoice me-1"></i> Returns
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="returnDropdown">
                        <li><a class="dropdown-item" href="modules/original_return/original_return.php">Original Returns</a></li>
                        <li><a class="dropdown-item" href="modules/revised_return/revised_return.php">Revised Returns</a></li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= (strpos($currentPage, 'trading') !== false || strpos($currentPage, 'profit') !== false || strpos($currentPage, 'balance') !== false) ? 'active' : '' ?>" href="#" id="financialDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-line me-1"></i> Financial Accounts
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="financialDropdown">
                        <li><a class="dropdown-item" href="modules/trading_account/trading_account.php">Trading Account</a></li>
                        <li><a class="dropdown-item" href="modules/profit_loss/profit_loss.php">Profit & Loss Account</a></li>
                        <li><a class="dropdown-item" href="modules/balance_sheet/balance_sheet.php">Balance Sheet</a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($currentPage, 'reports') !== false) ? 'active' : '' ?>" href="modules/reports/reports.php">
                        <i class="fas fa-chart-bar me-1"></i> Reports
                    </a>
                </li>
            </ul>
            
            <div class="d-flex">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search clients..." aria-label="Search clients">
                    <button class="btn btn-light" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>
    </div>
</nav>
