<?php
class Report {
    private $conn;
    private $client;
    private $taxReturn;
    private $tradingAccount;
    private $plAccount;
    private $balanceSheet;

    public function __construct($db) {
        $this->conn = $db;
        $this->client = new Client($db);
        $this->taxReturn = new TaxReturn($db);
        $this->tradingAccount = new TradingAccount($db);
        $this->plAccount = new PLAccount($db);
        $this->balanceSheet = new BalanceSheet($db);
    }

    public function getClientHistory($pan) {
        // Get client information
        $client = $this->client->getByPan($pan);
        if (!$client) {
            return false;
        }

        // Get all related records
        $taxReturns = $this->taxReturn->getByPan($pan);
        $tradingAccounts = $this->tradingAccount->getByPan($pan);
        $plAccounts = $this->plAccount->getByPan($pan);
        $balanceSheets = $this->balanceSheet->getByPan($pan);

        return [
            'client' => $client,
            'taxReturns' => $taxReturns,
            'tradingAccounts' => $tradingAccounts,
            'plAccounts' => $plAccounts,
            'balanceSheets' => $balanceSheets
        ];
    }

    public function getFinancialReport($pan, $reportType, $assessmentYear) {
        // Get client information
        $client = $this->client->getByPan($pan);
        if (!$client) {
            return false;
        }

        $data = [
            'client' => $client,
            'reportType' => $reportType,
            'assessmentYear' => $assessmentYear
        ];

        switch ($reportType) {
            case 'trading':
                $tradingAccounts = $this->tradingAccount->getByPan($pan);
                foreach ($tradingAccounts as $account) {
                    if ($account['ASSES_YEAR_1'] === $assessmentYear) {
                        $data['tradingAccount'] = $account;
                        break;
                    }
                }
                break;

            case 'pl':
                $plAccounts = $this->plAccount->getByPan($pan);
                foreach ($plAccounts as $account) {
                    if ($account['ASSESSMENT_YEAR'] === $assessmentYear) {
                        $data['plAccount'] = $account;
                        break;
                    }
                }
                break;

            case 'balance':
                $balanceSheets = $this->balanceSheet->getByPan($pan);
                foreach ($balanceSheets as $sheet) {
                    if ($sheet['ASSESSMENT_YEAR'] === $assessmentYear) {
                        $data['balanceSheet'] = $sheet;
                        break;
                    }
                }
                break;
        }

        return $data;
    }

    public function generatePDF($type, $data) {
        require_once('tcpdf/tcpdf.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Tax Assessment System');
        $pdf->SetTitle(ucfirst($type) . ' Report');

        // Set default header data
        $pdf->SetHeaderData('', 0, 'Tax Assessment System', 'Financial Report');

        // Set header and footer fonts
        $pdf->setHeaderFont(Array('helvetica', '', 12));
        $pdf->setFooterFont(Array('helvetica', '', 8));

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont('courier');

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Add a page
        $pdf->AddPage();

        // Generate report content based on type
        $html = $this->generateReportHTML($type, $data);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        $pdf->Output(ucfirst($type) . '_Report.pdf', 'I');
    }

    private function generateReportHTML($type, $data) {
        $html = '<h1>' . ucfirst($type) . ' Report</h1>';
        $html .= '<h2>Client Information</h2>';
        $html .= '<p><strong>PAN:</strong> ' . htmlspecialchars($data['client']->pan) . '</p>';
        $html .= '<p><strong>Name:</strong> ' . htmlspecialchars($data['client']->name) . '</p>';
        $html .= '<p><strong>Assessment Year:</strong> ' . htmlspecialchars($data['assessmentYear']) . '</p>';

        switch ($type) {
            case 'trading':
                if (isset($data['tradingAccount'])) {
                    $html .= '<h2>Trading Account</h2>';
                    $html .= '<table border="1" cellpadding="5">';
                    $html .= '<tr><th>Particulars</th><th>Amount</th></tr>';
                    $html .= '<tr><td>Opening Stock</td><td>' . number_format($data['tradingAccount']['OPENING_STOCK'], 2) . '</td></tr>';
                    $html .= '<tr><td>Purchases</td><td>' . number_format($data['tradingAccount']['PURCHASES'], 2) . '</td></tr>';
                    $html .= '<tr><td>Direct Expenses</td><td>' . number_format($data['tradingAccount']['DIRECT_EXPENSES'], 2) . '</td></tr>';
                    $html .= '<tr><td>Closing Stock</td><td>' . number_format($data['tradingAccount']['CLOSING_STOCK'], 2) . '</td></tr>';
                    $html .= '<tr><td><strong>Gross Profit</strong></td><td><strong>' . number_format($data['tradingAccount']['GROSS_PROFIT'], 2) . '</strong></td></tr>';
                    $html .= '</table>';
                }
                break;

            case 'pl':
                if (isset($data['plAccount'])) {
                    $html .= '<h2>Profit & Loss Account</h2>';
                    $html .= '<table border="1" cellpadding="5">';
                    $html .= '<tr><th>Particulars</th><th>Amount</th></tr>';
                    $html .= '<tr><td>Gross Profit</td><td>' . number_format($data['plAccount']['GROSS_PROFIT'], 2) . '</td></tr>';
                    $html .= '<tr><td>Indirect Income</td><td>' . number_format($data['plAccount']['INDIRECT_INCOME'], 2) . '</td></tr>';
                    $html .= '<tr><td>Indirect Expenses</td><td>' . number_format($data['plAccount']['INDIRECT_EXPENSES'], 2) . '</td></tr>';
                    $html .= '<tr><td><strong>Net Profit</strong></td><td><strong>' . number_format($data['plAccount']['NET_PROFIT'], 2) . '</strong></td></tr>';
                    $html .= '</table>';
                }
                break;

            case 'balance':
                if (isset($data['balanceSheet'])) {
                    $html .= '<h2>Balance Sheet</h2>';
                    $html .= '<table border="1" cellpadding="5">';
                    $html .= '<tr><th>Particulars</th><th>Amount</th></tr>';
                    $html .= '<tr><td>Assets</td><td>' . number_format($data['balanceSheet']['ASSETS'], 2) . '</td></tr>';
                    $html .= '<tr><td>Liabilities</td><td>' . number_format($data['balanceSheet']['LIABILITIES'], 2) . '</td></tr>';
                    $html .= '</table>';
                }
                break;

            case 'client':
                $html .= '<h2>Tax Returns</h2>';
                $html .= '<table border="1" cellpadding="5">';
                $html .= '<tr><th>Assessment Year</th><th>Return Type</th><th>Gross Income</th><th>Deductions</th><th>Taxable Income</th><th>Tax Paid</th></tr>';
                foreach ($data['taxReturns'] as $return) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($return['ASSESSMENT_YEAR']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($return['RETURN_ORIGINAL_REVISED']) . '</td>';
                    $html .= '<td>' . number_format($return['GROSS_INCOME'], 2) . '</td>';
                    $html .= '<td>' . number_format($return['DEDUCTIONS'], 2) . '</td>';
                    $html .= '<td>' . number_format($return['TAXABLE_INCOME'], 2) . '</td>';
                    $html .= '<td>' . number_format($return['TAX_PAID'], 2) . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';

                // Add other sections (Trading, P&L, Balance Sheet) similarly
                break;
        }

        return $html;
    }
}
?>
