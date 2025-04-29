<?php
session_start();

// Check if the user is logged in, if not redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Dashboard - Tax Assessment Management System";
$activePage = "dashboard";
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
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="mb-4">Tax Assessment Management System</h1>
            <div class="alert alert-info">
                <strong>Welcome to the Tax Assessment Management System!</strong> Select a module from the navigation menu to get started.
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Clients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-clients">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Returns Filed (Current FY)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-returns">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Revised Returns</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="revised-returns">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Firm Clients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="firm-clients">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Returns Overview</h6>
                </div>
                <div class="card-body">
                    <div id="monthly-returns-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Return Types Distribution</h6>
                </div>
                <div class="card-body">
                    <div id="return-types-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recent-activities-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Activity</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="recent-activities">
                                <tr>
                                    <td colspan="4" class="text-center">Loading recent activities...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- D3.js -->
<script src="https://d3js.org/d3.v7.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/main.js"></script>
<script src="assets/js/charts.js"></script>
<script>
    $(document).ready(function() {
        // Fetch dashboard data
        $.ajax({
            url: 'modules/reports/reports_process.php',
            type: 'GET',
            data: { action: 'get_dashboard_data' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#total-clients').text(response.data.total_clients);
                    $('#total-returns').text(response.data.total_returns);
                    $('#revised-returns').text(response.data.revised_returns);
                    $('#firm-clients').text(response.data.firm_clients);
                    
                    // Initialize charts
                    initMonthlyReturnsChart(response.data.monthly_returns);
                    initReturnTypesChart(response.data.return_types);
                    
                    // Recent activities
                    let activitiesHtml = '';
                    if (response.data.recent_activities.length > 0) {
                        response.data.recent_activities.forEach(function(activity) {
                            activitiesHtml += `
                                <tr>
                                    <td>${activity.date}</td>
                                    <td>${activity.client}</td>
                                    <td>${activity.activity}</td>
                                    <td>${activity.details}</td>
                                </tr>
                            `;
                        });
                    } else {
                        activitiesHtml = '<tr><td colspan="4" class="text-center">No recent activities found</td></tr>';
                    }
                    
                    $('#recent-activities').html(activitiesHtml);
                } else {
                    console.error('Error fetching dashboard data:', response.message);
                    $('.card-body').html('<div class="alert alert-danger">Error loading dashboard data. Please try again later.</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $('.card-body').html('<div class="alert alert-danger">Error loading dashboard data. Please try again later.</div>');
            }
        });
    });
</script>

</body>
</html>
