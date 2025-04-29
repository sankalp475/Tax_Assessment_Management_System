<?php
/**
 * Reports Process
 * 
 * This file handles all the processing for reports management operations.
 */

session_start();
require_once '../../config/database.php';

// Set response header for AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
}

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Check if action is provided
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process based on action
switch ($action) {
    case 'get_dashboard_data':
        getDashboardData();
        break;
        
    case 'get_clients':
        getClients();
        break;
        
    case 'generate_report':
        generateReport();
        break;
        
    case 'export_report':
        exportReport();
        break;
        
    default:
        sendResponse('error', 'Invalid action specified.');
        break;
}

/**
 * Get dashboard data for homepage
 */
function getDashboardData() {
    // Since we don't have a real database yet, we'll return sample data
    $data = [
        'total_clients' => 45,
        'total_returns' => 32,
        'revised_returns' => 8,
        'firm_clients' => 18,
        'monthly_returns' => [
            ['month' => 'Jan', 'count' => 4],
            ['month' => 'Feb', 'count' => 6],
            ['month' => 'Mar', 'count' => 9],
            ['month' => 'Apr', 'count' => 12],
            ['month' => 'May', 'count' => 8],
            ['month' => 'Jun', 'count' => 5],
            ['month' => 'Jul', 'count' => 3],
            ['month' => 'Aug', 'count' => 7],
            ['month' => 'Sep', 'count' => 10],
            ['month' => 'Oct', 'count' => 8],
            ['month' => 'Nov', 'count' => 6],
            ['month' => 'Dec', 'count' => 4],
        ],
        'return_types' => [
            ['type' => 'ITR-1', 'count' => 15],
            ['type' => 'ITR-2', 'count' => 8],
            ['type' => 'ITR-3', 'count' => 12],
            ['type' => 'ITR-4', 'count' => 6],
            ['type' => 'ITR-5', 'count' => 5],
            ['type' => 'ITR-6', 'count' => 2],
            ['type' => 'ITR-7', 'count' => 1],
        ],
        'recent_activities' => [
            [
                'date' => date('d-m-Y', strtotime('-1 day')),
                'client' => 'Sharma Enterprises',
                'activity' => 'Return Filed',
                'details' => 'Filed ITR-3 for assessment year 2023-2024'
            ],
            [
                'date' => date('d-m-Y', strtotime('-2 day')),
                'client' => 'Rahul Patel',
                'activity' => 'Client Added',
                'details' => 'New individual client added'
            ],
            [
                'date' => date('d-m-Y', strtotime('-2 day')),
                'client' => 'Global Trading Co.',
                'activity' => 'Trading Account Updated',
                'details' => 'Updated trading account for FY 2023-2024'
            ],
            [
                'date' => date('d-m-Y', strtotime('-3 day')),
                'client' => 'Priya Sharma',
                'activity' => 'Revised Return',
                'details' => 'Filed revised return for AY 2023-2024'
            ],
            [
                'date' => date('d-m-Y', strtotime('-5 day')),
                'client' => 'Kumar Industries',
                'activity' => 'Balance Sheet',
                'details' => 'Added balance sheet for FY 2023-2024'
            ],
        ]
    ];
    
    sendResponse('success', 'Dashboard data retrieved successfully.', $data);
}

/**
 * Get list of clients
 */
function getClients() {
    // Since we don't have a real database yet, we'll return sample data
    $clients = [
        ['id' => 1, 'name' => 'Rahul Patel', 'pan' => 'ABCDE1234F', 'client_type' => 'individual'],
        ['id' => 2, 'name' => 'Priya Sharma', 'pan' => 'FGHIJ5678K', 'client_type' => 'individual'],
        ['id' => 3, 'name' => 'Sharma Enterprises', 'pan' => 'KLMNO9012P', 'client_type' => 'partnership'],
        ['id' => 4, 'name' => 'Global Trading Co.', 'pan' => 'QRSTU3456V', 'client_type' => 'company'],
        ['id' => 5, 'name' => 'Kumar Industries', 'pan' => 'WXYZ7890A', 'client_type' => 'company'],
        ['id' => 6, 'name' => 'Singh Family Trust', 'pan' => 'BCDEF1234G', 'client_type' => 'trust'],
    ];
    
    sendResponse('success', 'Clients retrieved successfully.', $clients);
}

/**
 * Generate reports based on selected criteria
 */
function generateReport() {
    $reportType = isset($_GET['report_type']) ? $_GET['report_type'] : '';
    $clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
    $fiscalYear = isset($_GET['fiscal_year']) ? $_GET['fiscal_year'] : '';
    
    if (empty($reportType)) {
        sendResponse('error', 'Report type is required.');
        return;
    }
    
    // Get client info if client ID is provided
    $client = null;
    if ($clientId > 0) {
        // In a real implementation, we would fetch this from database
        $clients = [
            1 => ['id' => 1, 'name' => 'Rahul Patel', 'pan' => 'ABCDE1234F', 'client_type' => 'individual'],
            2 => ['id' => 2, 'name' => 'Priya Sharma', 'pan' => 'FGHIJ5678K', 'client_type' => 'individual'],
            3 => ['id' => 3, 'name' => 'Sharma Enterprises', 'pan' => 'KLMNO9012P', 'client_type' => 'partnership'],
            4 => ['id' => 4, 'name' => 'Global Trading Co.', 'pan' => 'QRSTU3456V', 'client_type' => 'company'],
            5 => ['id' => 5, 'name' => 'Kumar Industries', 'pan' => 'WXYZ7890A', 'client_type' => 'company'],
            6 => ['id' => 6, 'name' => 'Singh Family Trust', 'pan' => 'BCDEF1234G', 'client_type' => 'trust'],
        ];
        
        if (isset($clients[$clientId])) {
            $client = $clients[$clientId];
        } else {
            sendResponse('error', 'Client not found.');
            return;
        }
    }
    
    $html = '';
    $reportData = [];
    
    // Generate report based on type
    switch ($reportType) {
        case 'client_return_history':
            if (!$client) {
                sendResponse('error', 'Client selection is required for this report.');
                return;
            }
            
            // Sample data for client return history
            $returnHistory = [
                [
                    'assessment_year' => '2023-2024',
                    'return_type' => 'ITR-1',
                    'filing_date' => '31-07-2023',
                    'total_income' => 850000,
                    'tax_payable' => 45000,
                    'revised' => 'No'
                ],
                [
                    'assessment_year' => '2022-2023',
                    'return_type' => 'ITR-1',
                    'filing_date' => '29-07-2022',
                    'total_income' => 780000,
                    'tax_payable' => 38000,
                    'revised' => 'Yes'
                ],
                [
                    'assessment_year' => '2021-2022',
                    'return_type' => 'ITR-1',
                    'filing_date' => '31-07-2021',
                    'total_income' => 720000,
                    'tax_payable' => 32000,
                    'revised' => 'No'
                ],
            ];
            
            // Build report HTML
            $html = '
                <div class="report-header text-center mb-4">
                    <h4>Client Return History Report</h4>
                    <p>Client: ' . htmlspecialchars($client['name']) . ' (PAN: ' . htmlspecialchars($client['pan']) . ')</p>
                    <p>Generated on: ' . date('d-m-Y') . '</p>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Assessment Year</th>
                                <th>Return Type</th>
                                <th>Filing Date</th>
                                <th>Total Income</th>
                                <th>Tax Payable</th>
                                <th>Revised</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($returnHistory as $return) {
                $html .= '
                    <tr>
                        <td>' . $return['assessment_year'] . '</td>
                        <td>' . $return['return_type'] . '</td>
                        <td>' . $return['filing_date'] . '</td>
                        <td>₹' . number_format($return['total_income'], 2) . '</td>
                        <td>₹' . number_format($return['tax_payable'], 2) . '</td>
                        <td>' . $return['revised'] . '</td>
                    </tr>';
            }
            
            $html .= '
                        </tbody>
                    </table>
                </div>';
            
            $reportData = [
                'client' => $client,
                'returns' => $returnHistory
            ];
            break;
            
        case 'client_returns_by_year':
            if (!$client || empty($fiscalYear)) {
                sendResponse('error', 'Client and fiscal year selection are required for this report.');
                return;
            }
            
            // Sample data for client returns by year
            $returnsByYear = [
                [
                    'filing_date' => '31-07-2023',
                    'return_type' => 'ITR-1',
                    'acknowledgement_no' => '123456789012',
                    'total_income' => 850000,
                    'tax_payable' => 45000,
                    'filing_type' => 'E-filing'
                ]
            ];
            
            // Build report HTML
            $html = '
                <div class="report-header text-center mb-4">
                    <h4>Client Returns by Fiscal Year Report</h4>
                    <p>Client: ' . htmlspecialchars($client['name']) . ' (PAN: ' . htmlspecialchars($client['pan']) . ')</p>
                    <p>Fiscal Year: ' . $fiscalYear . '</p>
                    <p>Generated on: ' . date('d-m-Y') . '</p>
                </div>';
            
            if (count($returnsByYear) > 0) {
                $html .= '
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Filing Date</th>
                                    <th>Return Type</th>
                                    <th>Acknowledgement No.</th>
                                    <th>Total Income</th>
                                    <th>Tax Payable</th>
                                    <th>Filing Type</th>
                                </tr>
                            </thead>
                            <tbody>';
                
                foreach ($returnsByYear as $return) {
                    $html .= '
                        <tr>
                            <td>' . $return['filing_date'] . '</td>
                            <td>' . $return['return_type'] . '</td>
                            <td>' . $return['acknowledgement_no'] . '</td>
                            <td>₹' . number_format($return['total_income'], 2) . '</td>
                            <td>₹' . number_format($return['tax_payable'], 2) . '</td>
                            <td>' . $return['filing_type'] . '</td>
                        </tr>';
                }
                
                $html .= '
                            </tbody>
                        </table>
                    </div>';
            } else {
                $html .= '<div class="alert alert-info">No returns found for this client in the selected fiscal year.</div>';
            }
            
            $reportData = [
                'client' => $client,
                'fiscal_year' => $fiscalYear,
                'returns' => $returnsByYear
            ];
            break;
            
        case 'total_returns_by_year':
            if (empty($fiscalYear)) {
                sendResponse('error', 'Fiscal year selection is required for this report.');
                return;
            }
            
            // Sample data for total returns by year
            $totalReturnsByYear = [
                ['return_type' => 'ITR-1', 'count' => 15, 'total_income' => 12500000, 'total_tax' => 750000],
                ['return_type' => 'ITR-2', 'count' => 8, 'total_income' => 9200000, 'total_tax' => 650000],
                ['return_type' => 'ITR-3', 'count' => 12, 'total_income' => 18500000, 'total_tax' => 1250000],
                ['return_type' => 'ITR-4', 'count' => 6, 'total_income' => 4500000, 'total_tax' => 300000],
                ['return_type' => 'ITR-5', 'count' => 5, 'total_income' => 22000000, 'total_tax' => 1750000],
                ['return_type' => 'ITR-6', 'count' => 2, 'total_income' => 30000000, 'total_tax' => 2500000],
                ['return_type' => 'ITR-7', 'count' => 1, 'total_income' => 5000000, 'total_tax' => 400000],
            ];
            
            // Calculate totals
            $totalCount = array_sum(array_column($totalReturnsByYear, 'count'));
            $totalIncome = array_sum(array_column($totalReturnsByYear, 'total_income'));
            $totalTax = array_sum(array_column($totalReturnsByYear, 'total_tax'));
            
            // Build report HTML
            $html = '
                <div class="report-header text-center mb-4">
                    <h4>Total Returns by Fiscal Year Report</h4>
                    <p>Fiscal Year: ' . $fiscalYear . '</p>
                    <p>Generated on: ' . date('d-m-Y') . '</p>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Return Type</th>
                                <th>Count</th>
                                <th>Total Income</th>
                                <th>Total Tax</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($totalReturnsByYear as $return) {
                $percentage = ($return['count'] / $totalCount) * 100;
                $html .= '
                    <tr>
                        <td>' . $return['return_type'] . '</td>
                        <td>' . $return['count'] . '</td>
                        <td>₹' . number_format($return['total_income'], 2) . '</td>
                        <td>₹' . number_format($return['total_tax'], 2) . '</td>
                        <td>' . number_format($percentage, 2) . '%</td>
                    </tr>';
            }
            
            $html .= '
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold bg-light">
                                <td>Total</td>
                                <td>' . $totalCount . '</td>
                                <td>₹' . number_format($totalIncome, 2) . '</td>
                                <td>₹' . number_format($totalTax, 2) . '</td>
                                <td>100.00%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6 offset-md-3">
                        <div id="returns-distribution-chart" style="height: 300px;"></div>
                    </div>
                </div>
                
                <script>
                    // Create pie chart for return type distribution
                    $(document).ready(function() {
                        const data = [
                            ' . implode(',', array_map(function($item) {
                                return '{ type: "' . $item['return_type'] . '", count: ' . $item['count'] . ' }';
                            }, $totalReturnsByYear)) . '
                        ];
                        
                        initReturnTypesChart(data, "returns-distribution-chart");
                    });
                </script>';
            
            $reportData = [
                'fiscal_year' => $fiscalYear,
                'returns' => $totalReturnsByYear,
                'totals' => [
                    'count' => $totalCount,
                    'income' => $totalIncome,
                    'tax' => $totalTax
                ]
            ];
            break;
            
        case 'total_revised_returns_by_client':
            if (!$client) {
                sendResponse('error', 'Client selection is required for this report.');
                return;
            }
            
            // Sample data for total revised returns by client
            $revisedReturns = [
                [
                    'assessment_year' => '2022-2023',
                    'original_filing_date' => '29-07-2022',
                    'revised_filing_date' => '15-09-2022',
                    'original_income' => 780000,
                    'revised_income' => 820000,
                    'original_tax' => 38000,
                    'revised_tax' => 42000,
                    'additional_tax' => 4000,
                    'reason' => 'Income Omission'
                ]
            ];
            
            // Build report HTML
            $html = '
                <div class="report-header text-center mb-4">
                    <h4>Total Revised Returns by Client Report</h4>
                    <p>Client: ' . htmlspecialchars($client['name']) . ' (PAN: ' . htmlspecialchars($client['pan']) . ')</p>
                    <p>Generated on: ' . date('d-m-Y') . '</p>
                </div>';
            
            if (count($revisedReturns) > 0) {
                $html .= '
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Assessment Year</th>
                                    <th>Original Filing Date</th>
                                    <th>Revised Filing Date</th>
                                    <th>Original Income</th>
                                    <th>Revised Income</th>
                                    <th>Original Tax</th>
                                    <th>Revised Tax</th>
                                    <th>Additional Tax</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>';
                
                foreach ($revisedReturns as $return) {
                    $html .= '
                        <tr>
                            <td>' . $return['assessment_year'] . '</td>
                            <td>' . $return['original_filing_date'] . '</td>
                            <td>' . $return['revised_filing_date'] . '</td>
                            <td>₹' . number_format($return['original_income'], 2) . '</td>
                            <td>₹' . number_format($return['revised_income'], 2) . '</td>
                            <td>₹' . number_format($return['original_tax'], 2) . '</td>
                            <td>₹' . number_format($return['revised_tax'], 2) . '</td>
                            <td>₹' . number_format($return['additional_tax'], 2) . '</td>
                            <td>' . $return['reason'] . '</td>
                        </tr>';
                }
                
                $html .= '
                            </tbody>
                        </table>
                    </div>';
            } else {
                $html .= '<div class="alert alert-info">No revised returns found for this client.</div>';
            }
            
            $reportData = [
                'client' => $client,
                'revised_returns' => $revisedReturns
            ];
            break;
            
        default:
            sendResponse('error', 'Invalid report type.');
            return;
    }
    
    // Return the report HTML
    $response = [
        'html' => $html,
        'data' => $reportData
    ];
    
    sendResponse('success', 'Report generated successfully.', $response);
}

/**
 * Export report data
 */
function exportReport() {
    $reportType = isset($_GET['report_type']) ? $_GET['report_type'] : '';
    $clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
    $fiscalYear = isset($_GET['fiscal_year']) ? $_GET['fiscal_year'] : '';
    
    // In a real implementation, we would generate a CSV or PDF file here
    // For now, we'll just return a message
    echo "This feature is not yet implemented. It will export the selected report as a CSV or PDF file.";
    exit;
}

/**
 * Send JSON response
 * 
 * @param string $status Status of the response ('success', 'error', etc.)
 * @param string $message Message to be sent
 * @param array $data Additional data to include in the response
 */
function sendResponse($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}