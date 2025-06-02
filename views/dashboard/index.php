<?php
$currentPage = 'dashboard';
$title = "Dashboard - Tax Assessment System";
?>

<div class="row mb-4">
    <div class="col">
        <h2>Dashboard</h2>
        <p class="text-muted">Welcome back! Here's an overview of your tax assessment system.</p>
    </div>
</div>

<!-- Key Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card metric-card bg-primary text-white">
            <div class="card-body">
                <div class="metric-icon">
                    <i class="bi bi-people"></i>
                </div>
                <h5 class="card-title">Total Clients</h5>
                <h2 class="card-text"><?php echo $totalClients; ?></h2>
                <p class="card-text"><small>↑ 12% from last month</small></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card bg-success text-white">
            <div class="card-body">
                <div class="metric-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <h5 class="card-title">Pending Returns</h5>
                <h2 class="card-text"><?php echo $totalReturns; ?></h2>
                <p class="card-text"><small>Due this month</small></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card bg-warning text-white">
            <div class="card-body">
                <div class="metric-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h5 class="card-title">Upcoming Deadlines</h5>
                <h2 class="card-text"><?php echo $upcomingDeadlines; ?></h2>
                <p class="card-text"><small>Next 7 days</small></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card metric-card bg-info text-white">
            <div class="card-body">
                <div class="metric-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h5 class="card-title">Total Revenue</h5>
                <h2 class="card-text">£<?php echo number_format($totalRevenue ?? 0, 2); ?></h2>
                <p class="card-text"><small>This quarter</small></p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and Recent Activity -->
<div class="row">
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/returns/create" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> New Tax Return
                    </a>
                    <a href="/clients/create" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus"></i> Add New Client
                    </a>
                    <a href="/reports/generate" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-bar-graph"></i> Generate Report
                    </a>
                    <a href="/calendar" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-week"></i> View Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body recent-activity">
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <h6>New Tax Return Submitted</h6>
                        <small class="text-muted"><?php echo date('h:i A', strtotime($recentReturns[0]['CREATED_AT'] ?? '1970-01-01')); ?></small>
                    </div>
                    <p class="mb-0">PAN: <?php echo htmlspecialchars($recentReturns[0]['PAN'] ?? ''); ?></p>
                </div>
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <h6>Client Added</h6>
                        <small class="text-muted"><?php echo date('h:i A', strtotime($recentClients[0]['CREATED_AT'] ?? '1970-01-01')); ?></small>
                    </div>
                    <p class="mb-0"><?php echo htmlspecialchars($recentClients[0]['NAME'] ?? ''); ?></p>
                </div>
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <h6>Report Generated</h6>
                        <small class="text-muted"><?php echo date('h:i A', strtotime($recentReports[0]['CREATED_AT'] ?? '1970-01-01')); ?></small>
                    </div>
                    <p class="mb-0"><?php echo htmlspecialchars($recentReports[0]['TITLE'] ?? ''); ?></p>
                </div>
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <h6>Document Uploaded</h6>
                        <small class="text-muted"><?php echo date('h:i A', strtotime($recentDocuments[0]['UPLOADED_AT'] ?? '1970-01-01')); ?></small>
                    </div>
                    <p class="mb-0"><?php echo htmlspecialchars($recentDocuments[0]['TITLE'] ?? ''); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Deadlines -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Upcoming Deadlines</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Type</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingDeadlines as $deadline): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($deadline['NAME'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($deadline['TYPE'] ?? ''); ?></td>
                                <td><?php echo date('d M Y', strtotime($deadline['DUE_DATE'] ?? '1970-01-01')); ?></td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 
