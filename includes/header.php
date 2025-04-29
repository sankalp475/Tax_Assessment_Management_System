<?php
/**
 * Header Template
 * 
 * Includes the HTML header and navigation elements
 */

// Initialize session
session_start();

// Check if user is not logged in, redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login.php");
    exit;
}

// Default page title if not set
if (!isset($pageTitle)) {
    $pageTitle = "Tax Assessment Management System";
}

// Default active page if not set
if (!isset($activePage)) {
    $activePage = "";
}

// Handle logout
if(isset($_GET["logout"])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("location: /login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="top-navigation">
        <button class="btn text-white" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <a href="/" class="navbar-brand ms-3">
            <i class="fas fa-file-invoice-dollar me-2"></i>
            Tax Assessment Management System
        </a>
        
        <div class="nav-links d-flex">
            <a href="/index.php" class="nav-link <?= ($activePage == 'dashboard') ? 'active' : '' ?>">
                <i class="fas fa-home me-1"></i> Home
            </a>
            <a href="/modules/client/client.php" class="nav-link <?= ($activePage == 'client') ? 'active' : '' ?>">
                <i class="fas fa-users me-1"></i> Client Information
            </a>
            <a href="/modules/original_return/original_return.php" class="nav-link <?= (in_array($activePage, ['original_return', 'revised_return'])) ? 'active' : '' ?>">
                <i class="fas fa-file-invoice me-1"></i> Returns
            </a>
            <a href="/modules/trading_account/trading_account.php" class="nav-link <?= (in_array($activePage, ['trading_account', 'profit_loss', 'balance_sheet'])) ? 'active' : '' ?>">
                <i class="fas fa-chart-line me-1"></i> Financial Accounts
            </a>
            <a href="/modules/reports/reports.php" class="nav-link <?= ($activePage == 'reports') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar me-1"></i> Reports
            </a>
        </div>
        
        <div class="d-flex align-items-center ms-auto">
            <div class="search-container me-3">
                <form class="d-flex">
                    <input class="search-input" type="search" placeholder="Search clients..." aria-label="Search">
                    <button class="search-btn" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-none d-lg-inline me-2">Welcome, <?= htmlspecialchars($_SESSION["username"] ?? 'User'); ?></span>
                    <i class="fas fa-user-circle"></i>
                </a>
                <!-- Dropdown - User Information -->
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?logout=1"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content-wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-brand d-flex align-items-center justify-content-center">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Tax Assessment System</div>
                <button class="btn close-btn" id="sidebarClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="sidebar-search px-3 py-2">
                <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search clients..." aria-label="Search" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <hr class="sidebar-divider my-0">
            
            <!-- Nav Items -->
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= ($activePage == 'dashboard') ? 'active' : '' ?>" href="/index.php">
                        <i class="fas fa-fw fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($activePage == 'client') ? 'active' : '' ?>" href="/modules/client/client.php">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Client Information</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (in_array($activePage, ['original_return', 'revised_return'])) ? 'active' : '' ?>" href="/modules/original_return/original_return.php">
                        <i class="fas fa-fw fa-file-invoice"></i>
                        <span>Returns</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (in_array($activePage, ['trading_account', 'profit_loss', 'balance_sheet'])) ? 'active' : '' ?>" href="/modules/trading_account/trading_account.php">
                        <i class="fas fa-fw fa-chart-line"></i>
                        <span>Financial Accounts</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($activePage == 'reports') ? 'active' : '' ?>" href="/modules/reports/reports.php">
                        <i class="fas fa-fw fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="content">
            <!-- Page content header -->
            <div class="mb-4">
                <h5 class="mb-0 py-3"><?= $pageTitle ?></h5>
            </div>
            
            <!-- Page Content -->
            <div id="alert-container"></div>
