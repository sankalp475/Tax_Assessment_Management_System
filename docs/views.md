# Views Documentation

## Overview
Views in the Tax Assessment Management System are responsible for presenting data to users in a structured and user-friendly manner. The system uses PHP templates with Bootstrap for styling and responsive design.

## View Structure
Views are organized in the following structure:
```
views/
├── layouts/          # Layout templates
├── dashboard/        # Dashboard views
├── returns/          # Tax return views
├── clients/          # Client management views
├── trading-accounts/ # Trading account views
├── pl-accounts/      # P&L account views
├── balance-sheets/   # Balance sheet views
└── reports/          # Report views
```

## Dashboard View

### File Location
`views/dashboard/index.php`

### Purpose
The dashboard view provides an overview of the system's key metrics and recent activities. It serves as the main landing page for users after login.

### Components

#### 1. Key Metrics Section
```php
<div class="row mb-4">
    <!-- Metric Cards -->
</div>
```
- Total Clients
- Pending Returns
- Upcoming Deadlines
- Total Revenue

Each metric card includes:
- Icon
- Title
- Value
- Trend indicator

#### 2. Quick Actions Panel
```php
<div class="col-md-4">
    <div class="card">
        <!-- Quick Action Buttons -->
    </div>
</div>
```
Available actions:
- New Tax Return
- Add New Client
- Generate Report
- View Calendar

#### 3. Recent Activity Feed
```php
<div class="col-md-8">
    <div class="card">
        <!-- Activity Items -->
    </div>
</div>
```
Shows:
- New Tax Returns
- Client Additions
- Report Generations
- Document Uploads

#### 4. Upcoming Deadlines Table
```php
<div class="row mt-4">
    <div class="col-12">
        <!-- Deadlines Table -->
    </div>
</div>
```
Columns:
- Client Name
- Type
- Due Date
- Status
- Action

### Styling
The dashboard uses Bootstrap 5 classes and custom CSS:
```css
.metric-card {
    border-radius: 10px;
    transition: transform 0.2s;
}
.metric-card:hover {
    transform: translateY(-5px);
}
.metric-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}
.recent-activity {
    max-height: 400px;
    overflow-y: auto;
}
.activity-item {
    border-left: 3px solid #0d6efd;
    padding-left: 1rem;
    margin-bottom: 1rem;
}
```

### Required Variables
The dashboard expects the following PHP variables:
```php
$totalClients
$totalReturns
$upcomingDeadlines
$totalRevenue
$recentReturns
$recentClients
$recentReports
$recentDocuments
```

### Security Measures
- XSS prevention using `htmlspecialchars()`
- Proper date formatting
- Null coalescing for optional data

## Best Practices
1. Use semantic HTML
2. Implement responsive design
3. Follow Bootstrap grid system
4. Maintain consistent styling
5. Handle missing data gracefully
6. Use proper security measures
7. Keep views focused on presentation
8. Use meaningful variable names

## Usage Example
```php
// In controller
$data = [
    'totalClients' => 150,
    'totalReturns' => 45,
    'upcomingDeadlines' => 8,
    'totalRevenue' => 125000,
    'recentReturns' => $recentReturns,
    'recentClients' => $recentClients,
    'recentReports' => $recentReports,
    'recentDocuments' => $recentDocuments
];

$this->render('dashboard/index', $data);
``` 
