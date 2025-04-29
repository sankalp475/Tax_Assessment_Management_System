<?php
session_start();
require_once '../../config/database.php';

$pageTitle = "Reports";
$activePage = "reports";

include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Report Generator</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Select the report type and parameters below to generate a report.
                            </div>
                        </div>
                    </div>
                    
                    <form id="report-form">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="report-type" class="form-label">Report Type</label>
                                <select id="report-type" class="form-select">
                                    <option value="">Select Report Type</option>
                                    <option value="client_return_history">Client Return History</option>
                                    <option value="client_returns_by_year">Client Returns by Fiscal Year</option>
                                    <option value="total_returns_by_year">Total Returns by Fiscal Year</option>
                                    <option value="total_revised_returns_by_client">Total Revised Returns by Client</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4" id="client-select-container" style="display: none;">
                                <label for="client-select" class="form-label">Select Client</label>
                                <select id="client-select" class="form-select">
                                    <option value="">Select Client</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4" id="year-select-container" style="display: none;">
                                <label for="year-select" class="form-label">Select Fiscal Year</label>
                                <select id="year-select" class="form-select">
                                    <option value="">Select Fiscal Year</option>
                                    <option value="2024-2025">2024-2025</option>
                                    <option value="2023-2024">2023-2024</option>
                                    <option value="2022-2023">2022-2023</option>
                                    <option value="2021-2022">2021-2022</option>
                                    <option value="2020-2021">2020-2021</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" id="generate-report-btn" class="btn btn-primary">
                                    <i class="fas fa-file-alt me-2"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Report Results</h6>
                    <div class="dropdown no-arrow">
                        <button id="export-btn" class="btn btn-sm btn-primary" disabled>
                            <i class="fas fa-download fa-sm"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body" id="report-container">
                    <div class="alert alert-secondary text-center">
                        <i class="fas fa-chart-bar me-2"></i> Select a report type and click Generate Report to view data.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Show/hide relevant form fields based on report type
    $('#report-type').change(function() {
        const reportType = $(this).val();
        
        // Reset and hide all containers first
        $('#client-select-container, #year-select-container').hide();
        
        if (reportType === 'client_return_history' || reportType === 'client_returns_by_year' || reportType === 'total_revised_returns_by_client') {
            $('#client-select-container').show();
        }
        
        if (reportType === 'client_returns_by_year' || reportType === 'total_returns_by_year') {
            $('#year-select-container').show();
        }
    });
    
    // Load client list from API
    $.ajax({
        url: 'reports_process.php',
        type: 'GET',
        data: { action: 'get_clients' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let clientOptions = '<option value="">Select Client</option>';
                response.data.forEach(function(client) {
                    clientOptions += `<option value="${client.id}">${client.name} (${client.pan})</option>`;
                });
                $('#client-select').html(clientOptions);
            } else {
                console.error('Error fetching clients:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
    
    // Generate report button click event
    $('#generate-report-btn').click(function() {
        const reportType = $('#report-type').val();
        
        if (!reportType) {
            alert('Please select a report type');
            return;
        }
        
        // Validate required fields based on report type
        if ((reportType === 'client_return_history' || reportType === 'client_returns_by_year' || reportType === 'total_revised_returns_by_client') 
            && !$('#client-select').val()) {
            alert('Please select a client');
            return;
        }
        
        if ((reportType === 'client_returns_by_year' || reportType === 'total_returns_by_year') 
            && !$('#year-select').val()) {
            alert('Please select a fiscal year');
            return;
        }
        
        // Show loading state
        $('#report-container').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-3">Generating report...</p></div>');
        
        // Generate the report
        $.ajax({
            url: 'reports_process.php',
            type: 'GET',
            data: {
                action: 'generate_report',
                report_type: reportType,
                client_id: $('#client-select').val(),
                fiscal_year: $('#year-select').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#report-container').html(response.data.html);
                    $('#export-btn').prop('disabled', false);
                } else {
                    $('#report-container').html(`<div class="alert alert-danger">${response.message}</div>`);
                    $('#export-btn').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $('#report-container').html('<div class="alert alert-danger">Error generating report. Please try again later.</div>');
                $('#export-btn').prop('disabled', true);
            }
        });
    });
    
    // Export button click event
    $('#export-btn').click(function() {
        const reportType = $('#report-type').val();
        
        if (!reportType) {
            return;
        }
        
        const clientId = $('#client-select').val() || '';
        const fiscalYear = $('#year-select').val() || '';
        
        window.location.href = `reports_process.php?action=export_report&report_type=${reportType}&client_id=${clientId}&fiscal_year=${fiscalYear}`;
    });
});
</script>