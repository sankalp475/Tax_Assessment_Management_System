<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/TaxReturn.php';
require_once __DIR__ . '/../models/TradingAccount.php';
require_once __DIR__ . '/../models/PLAccount.php';
require_once __DIR__ . '/../models/BalanceSheet.php';

class ReturnController {
    private $db;
    private $client;
    private $taxReturn;
    private $tradingAccount;
    private $plAccount;
    private $balanceSheet;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            if (!$this->db) {
                throw new Exception("Database connection failed");
            }
            $this->client = new Client($this->db);
            $this->taxReturn = new TaxReturn($this->db);
            $this->tradingAccount = new TradingAccount($this->db);
            $this->plAccount = new PLAccount($this->db);
            $this->balanceSheet = new BalanceSheet($this->db);
        } catch (Exception $e) {
            error_log("Error in ReturnController constructor: " . $e->getMessage());
            throw $e;
        }
    }

    private function render($view, $data = []) {
        try {
            // Clear any existing output
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Extract data to make variables available in view
            extract($data);
            
            // Start output buffering
            ob_start();
            
            // Include the view file
            $viewPath = __DIR__ . '/../views/' . $view . '.php';
            if (!file_exists($viewPath)) {
                throw new Exception("View file not found: " . $viewPath);
            }
            
            // Include the view file and capture its output
            require $viewPath;
            $content = ob_get_clean();
            
            // Start a new buffer for the layout
            ob_start();
            
            // Include the layout
            $layoutPath = __DIR__ . '/../views/layouts/main.php';
            if (!file_exists($layoutPath)) {
                throw new Exception("Layout file not found: " . $layoutPath);
            }
            require $layoutPath;
            
            // Output the final result and clean up
            $output = ob_get_clean();
            echo $output;
            
        } catch (Exception $e) {
            error_log("Error in render method: " . $e->getMessage());
            throw $e;
        }
    }

    private function redirect($url) {
        ob_clean(); // Clear any output
        header("Location: " . $url);
        exit();
    }

    private function handleError($message, $redirectUrl = '/error') {
        $_SESSION['error'] = $message;
        $this->redirect($redirectUrl);
    }

    public function index() {
        try {
            error_log("Starting ReturnController::index");
            
            // Get tax returns
            $stmt = $this->taxReturn->read();
            if ($stmt === false) {
                throw new Exception("Failed to execute read query");
            }
            
            $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched returns: " . print_r($returns, true));
            
            $this->render('returns/index', [
                'title' => 'Tax Returns',
                'returns' => $returns
            ]);
        } catch (Exception $e) {
            error_log("Error in ReturnController::index: " . $e->getMessage());
            $_SESSION['error'] = "Error: " . $e->getMessage();
            $this->redirect('/error');
        }
    }

    public function create() {
        try {
            error_log("Starting ReturnController::create");
            
            // Get clients for the dropdown
            $clients = $this->client->read()->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched clients: " . print_r($clients, true));
            
            $this->render('returns/create', [
                'title' => 'Create Tax Return',
                'clients' => $clients,
                'currentPage' => 'returns'
            ]);
        } catch (Exception $e) {
            error_log("Error in ReturnController::create: " . $e->getMessage());
            $this->redirect('/error');
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->taxReturn->pan = $_POST['pan'];
                $this->taxReturn->assessment_year = $_POST['assessment_year'];
                $this->taxReturn->return_original_revised = isset($_POST['return_original_revised']) ? 1 : 0;
                $this->taxReturn->gross_income = $_POST['gross_income'];
                $this->taxReturn->deductions = $_POST['deductions'];
                $this->taxReturn->taxable_income = $_POST['taxable_income'];
                $this->taxReturn->tax_paid = $_POST['tax_paid'];

                if ($this->taxReturn->create()) {
                    $_SESSION['success'] = "Tax return created successfully.";
                    $this->redirect('/returns');
                } else {
                    $_SESSION['error'] = "Unable to create tax return.";
                    $this->redirect('/returns/create');
                }
            } catch (Exception $e) {
                error_log("Error in ReturnController::store: " . $e->getMessage());
                $_SESSION['error'] = "An error occurred while creating the tax return.";
                $this->redirect('/returns/create');
            }
        }
    }

    public function edit($id) {
        try {
            $this->taxReturn->id = $id;
            if ($this->taxReturn->readOne()) {
                $clients = $this->client->read()->fetchAll(PDO::FETCH_ASSOC);
                
                // Create an array with the tax return data
                $taxReturn = [
                    'id' => $this->taxReturn->id,
                    'pan' => $this->taxReturn->pan,
                    'assessment_year' => $this->taxReturn->assessment_year,
                    'return_original_revised' => $this->taxReturn->return_original_revised,
                    'gross_income' => $this->taxReturn->gross_income,
                    'deductions' => $this->taxReturn->deductions,
                    'taxable_income' => $this->taxReturn->taxable_income,
                    'tax_paid' => $this->taxReturn->tax_paid,
                    'created_at' => $this->taxReturn->created_at,
                    'updated_at' => $this->taxReturn->updated_at
                ];
                
                $this->render('returns/edit', [
                    'title' => 'Edit Tax Return',
                    'clients' => $clients,
                    'taxReturn' => $taxReturn
                ]);
            } else {
                $_SESSION['error'] = "Tax return not found.";
                $this->redirect('/returns');
            }
        } catch (Exception $e) {
            error_log("Error in ReturnController::edit: " . $e->getMessage());
            $this->redirect('/error');
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->taxReturn->id = $_POST['id'];
                $this->taxReturn->pan = $_POST['pan'];
                $this->taxReturn->assessment_year = $_POST['assessment_year'];
                $this->taxReturn->return_original_revised = isset($_POST['return_original_revised']) ? 1 : 0;
                $this->taxReturn->gross_income = $_POST['gross_income'];
                $this->taxReturn->deductions = $_POST['deductions'];
                $this->taxReturn->taxable_income = $_POST['taxable_income'];
                $this->taxReturn->tax_paid = $_POST['tax_paid'];

                if ($this->taxReturn->update()) {
                    $_SESSION['success'] = "Tax return updated successfully.";
                    $this->redirect('/returns');
                } else {
                    $_SESSION['error'] = "Unable to update tax return.";
                    $this->redirect('/returns/edit/' . $_POST['id']);
                }
            } catch (Exception $e) {
                error_log("Error in ReturnController::update: " . $e->getMessage());
                $_SESSION['error'] = "An error occurred while updating the tax return.";
                $this->redirect('/returns/edit/' . $_POST['id']);
            }
        }
    }

    public function delete($id) {
        try {
            $this->taxReturn->id = $id;
            if ($this->taxReturn->delete()) {
                $_SESSION['success'] = "Tax return deleted successfully.";
            } else {
                $_SESSION['error'] = "Unable to delete tax return.";
            }
            $this->redirect('/returns');
        } catch (Exception $e) {
            error_log("Error in ReturnController::delete: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while deleting the tax return.";
            $this->redirect('/returns');
        }
    }

    public function view($id) {
        try {
            $this->taxReturn->id = $id;
            if ($this->taxReturn->readOne()) {
                // Create an array with the tax return data
                $taxReturn = [
                    'id' => $this->taxReturn->id,
                    'pan' => $this->taxReturn->pan,
                    'assessment_year' => $this->taxReturn->assessment_year,
                    'return_original_revised' => $this->taxReturn->return_original_revised,
                    'gross_income' => $this->taxReturn->gross_income,
                    'deductions' => $this->taxReturn->deductions,
                    'taxable_income' => $this->taxReturn->taxable_income,
                    'tax_paid' => $this->taxReturn->tax_paid,
                    'created_at' => $this->taxReturn->created_at,
                    'updated_at' => $this->taxReturn->updated_at
                ];
                
                // Get client information
                $this->client->pan = $this->taxReturn->pan;
                if ($this->client->readOne()) {
                    $client = [
                        'name' => $this->client->name,
                        'pan' => $this->client->pan,
                        'email' => $this->client->email,
                        'phone' => $this->client->phone,
                        'address' => $this->client->address
                    ];
                } else {
                    $client = null;
                }
                
                $this->render('returns/view', [
                    'title' => 'View Tax Return',
                    'client' => $client,
                    'taxReturn' => $taxReturn
                ]);
            } else {
                $_SESSION['error'] = "Tax return not found.";
                $this->redirect('/returns');
            }
        } catch (Exception $e) {
            error_log("Error in ReturnController::view: " . $e->getMessage());
            $this->redirect('/error');
        }
    }

    public function clientHistory($pan) {
        $this->client->pan = $pan;
        if ($this->client->readOne()) {
            $taxReturns = $this->taxReturn->getByPan($pan);
            $tradingAccounts = $this->tradingAccount->getByPan($pan);
            $plAccounts = $this->plAccount->getByPan($pan);
            $balanceSheets = $this->balanceSheet->getByPan($pan);
            
            require_once '../views/reports/client_history.php';
        } else {
            $_SESSION['error'] = "Client not found!";
            $this->redirect('/reports');
        }
    }

    public function tradingReport($pan) {
        $this->client->pan = $pan;
        if ($this->client->readOne()) {
            $tradingAccounts = $this->tradingAccount->getByPan($pan);
            require_once '../views/reports/trading_report.php';
        } else {
            $_SESSION['error'] = "Client not found!";
            $this->redirect('/reports');
        }
    }

    public function plReport($pan) {
        $this->client->pan = $pan;
        if ($this->client->readOne()) {
            $plAccounts = $this->plAccount->getByPan($pan);
            require_once '../views/reports/pl_report.php';
        } else {
            $_SESSION['error'] = "Client not found!";
            $this->redirect('/reports');
        }
    }

    public function balanceReport($pan) {
        $this->client->pan = $pan;
        if ($this->client->readOne()) {
            $balanceSheets = $this->balanceSheet->getByPan($pan);
            require_once '../views/reports/balance_report.php';
        } else {
            $_SESSION['error'] = "Client not found!";
            $this->redirect('/reports');
        }
    }

    public function generatePDF($type, $pan) {
        require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Tax Assessment System');
        $pdf->SetTitle(ucfirst($type) . ' Report - ' . $pan);
        
        // Set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
        
        // Set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Generate content based on report type
        switch($type) {
            case 'trading':
                $this->generateTradingPDF($pdf, $pan);
                break;
            case 'pl':
                $this->generatePLPDF($pdf, $pan);
                break;
            case 'balance':
                $this->generateBalancePDF($pdf, $pan);
                break;
            default:
                $this->generateClientHistoryPDF($pdf, $pan);
        }
        
        // Close and output PDF document
        $pdf->Output(ucfirst($type) . '_Report_' . $pan . '.pdf', 'D');
    }

    private function generateTradingPDF($pdf, $pan) {
        $tradingAccounts = $this->tradingAccount->getByPan($pan);
        
        $html = '<h1>Trading Account Report</h1>';
        $html .= '<h2>PAN: ' . $pan . '</h2>';
        
        foreach($tradingAccounts as $account) {
            $html .= '<h3>Assessment Year: ' . $account['ASSES_YEAR_1'] . '-' . $account['ASSES_YEAR_2'] . '</h3>';
            $html .= '<table border="1" cellpadding="5">';
            $html .= '<tr><th>Particulars</th><th>Amount</th></tr>';
            $html .= '<tr><td>Opening Stock</td><td>' . $account['OPENING_STOCK'] . '</td></tr>';
            $html .= '<tr><td>Purchases</td><td>' . $account['PURCHASES'] . '</td></tr>';
            $html .= '<tr><td>Direct Expenses</td><td>' . $account['DIRECT_EXPENSES'] . '</td></tr>';
            $html .= '<tr><td>Closing Stock</td><td>' . $account['CLOSING_STOCK'] . '</td></tr>';
            $html .= '<tr><td>Gross Profit</td><td>' . $account['GROSS_PROFIT'] . '</td></tr>';
            $html .= '</table><br><br>';
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    private function generatePLPDF($pdf, $pan) {
        $plAccounts = $this->plAccount->getByPan($pan);
        
        $html = '<h1>Profit & Loss Account Report</h1>';
        $html .= '<h2>PAN: ' . $pan . '</h2>';
        
        foreach($plAccounts as $account) {
            $html .= '<h3>Assessment Year: ' . $account['ASSESSMENT_YEAR'] . '</h3>';
            $html .= '<table border="1" cellpadding="5">';
            $html .= '<tr><th>Particulars</th><th>Amount</th></tr>';
            $html .= '<tr><td>Gross Profit</td><td>' . $account['GROSS_PROFIT'] . '</td></tr>';
            $html .= '<tr><td>Indirect Income</td><td>' . $account['INDIRECT_INCOME'] . '</td></tr>';
            $html .= '<tr><td>Indirect Expenses</td><td>' . $account['INDIRECT_EXPENSES'] . '</td></tr>';
            $html .= '<tr><td>Net Profit</td><td>' . $account['NET_PROFIT'] . '</td></tr>';
            $html .= '</table><br><br>';
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    private function generateBalancePDF($pdf, $pan) {
        $balanceSheets = $this->balanceSheet->getByPan($pan);
        
        $html = '<h1>Balance Sheet Report</h1>';
        $html .= '<h2>PAN: ' . $pan . '</h2>';
        
        foreach($balanceSheets as $sheet) {
            $html .= '<h3>Assessment Year: ' . $sheet['ASSESSMENT_YEAR'] . '</h3>';
            $html .= '<table border="1" cellpadding="5">';
            $html .= '<tr><th>Assets</th><th>Liabilities</th></tr>';
            $html .= '<tr><td>' . nl2br($sheet['ASSETS']) . '</td><td>' . nl2br($sheet['LIABILITIES']) . '</td></tr>';
            $html .= '</table><br><br>';
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    private function generateClientHistoryPDF($pdf, $pan) {
        $taxReturns = $this->taxReturn->getByPan($pan);
        $tradingAccounts = $this->tradingAccount->getByPan($pan);
        $plAccounts = $this->plAccount->getByPan($pan);
        $balanceSheets = $this->balanceSheet->getByPan($pan);
        
        $html = '<h1>Client History Report</h1>';
        $html .= '<h2>PAN: ' . $pan . '</h2>';
        
        // Tax Returns
        $html .= '<h3>Tax Returns</h3>';
        foreach($taxReturns as $return) {
            $html .= '<p>Assessment Year: ' . $return['ASSESSMENT_YEAR'] . '</p>';
            $html .= '<p>Type: ' . ($return['RETURN_ORIGINAL_REVISED'] ? 'Revised' : 'Original') . '</p>';
            $html .= '<p>Taxable Income: ' . $return['TAXABLE_INCOME'] . '</p>';
            $html .= '<p>Tax Paid: ' . $return['TAX_PAID'] . '</p><br>';
        }
        
        // Trading Accounts
        $html .= '<h3>Trading Accounts</h3>';
        foreach($tradingAccounts as $account) {
            $html .= '<p>Period: ' . $account['ASSES_YEAR_1'] . '-' . $account['ASSES_YEAR_2'] . '</p>';
            $html .= '<p>Gross Profit: ' . $account['GROSS_PROFIT'] . '</p><br>';
        }
        
        // P&L Accounts
        $html .= '<h3>Profit & Loss Accounts</h3>';
        foreach($plAccounts as $account) {
            $html .= '<p>Assessment Year: ' . $account['ASSESSMENT_YEAR'] . '</p>';
            $html .= '<p>Net Profit: ' . $account['NET_PROFIT'] . '</p><br>';
        }
        
        // Balance Sheets
        $html .= '<h3>Balance Sheets</h3>';
        foreach($balanceSheets as $sheet) {
            $html .= '<p>Assessment Year: ' . $sheet['ASSESSMENT_YEAR'] . '</p>';
            $html .= '<p>Assets: ' . nl2br($sheet['ASSETS']) . '</p>';
            $html .= '<p>Liabilities: ' . nl2br($sheet['LIABILITIES']) . '</p><br>';
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
    }
}
?>
