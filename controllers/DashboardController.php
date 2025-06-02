<?php
class DashboardController {
    private $client;
    private $taxReturn;
    private $tradingAccount;
    private $plAccount;
    private $balanceSheet;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->client = new Client($db);
        $this->taxReturn = new TaxReturn($db);
        $this->tradingAccount = new TradingAccount($db);
        $this->plAccount = new PLAccount($db);
        $this->balanceSheet = new BalanceSheet($db);
    }

    public function index() {
        try {
            // Get counts for dashboard
            $totalClients = $this->client->getTotalCount();
            $totalReturns = $this->taxReturn->getTotalCount();
            $totalTradingAccounts = $this->tradingAccount->getTotalCount();
            $totalPLAccounts = $this->plAccount->getTotalCount();
            $totalBalanceSheets = $this->balanceSheet->getTotalCount();

            // Get recent activities
            $recentReturns = $this->taxReturn->getRecent(5);
            $recentClients = $this->client->getRecent(5);

            // Get statistics
            $stats = [
                'total_tax_paid' => $this->taxReturn->getTotalTaxPaid(),
                'average_tax_paid' => $this->taxReturn->getAverageTaxPaid(),
                'total_income' => $this->taxReturn->getTotalIncome(),
                'total_deductions' => $this->taxReturn->getTotalDeductions()
            ];

            $this->render('dashboard/index', [
                'title' => 'Dashboard',
                'currentPage' => 'dashboard',
                'totalClients' => $totalClients,
                'totalReturns' => $totalReturns,
                'totalTradingAccounts' => $totalTradingAccounts,
                'totalPLAccounts' => $totalPLAccounts,
                'totalBalanceSheets' => $totalBalanceSheets,
                'recentReturns' => $recentReturns,
                'recentClients' => $recentClients,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            error_log("Error in DashboardController::index: " . $e->getMessage());
            $_SESSION['error'] = "Failed to load dashboard data.";
            $this->redirect('/error');
        }
    }

    private function render($view, $data = []) {
        $content = __DIR__ . "/../views/{$view}.php";
        extract($data);
        include __DIR__ . "/../views/layouts/main.php";
    }

    private function redirect($url) {
        header("Location: {$url}");
        exit;
    }
} 
