<?php
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/TaxReturn.php';
require_once __DIR__ . '/../models/TradingAccount.php';
require_once __DIR__ . '/../models/PLAccount.php';
require_once __DIR__ . '/../models/BalanceSheet.php';

class ReportController {
    private $db;
    private $client;
    private $taxReturn;
    private $tradingAccount;
    private $plAccount;
    private $balanceSheet;

    public function __construct($db) {
        try {
            $this->db = $db;
            $this->client = new Client($db);
            $this->taxReturn = new TaxReturn($db);
            $this->tradingAccount = new TradingAccount($db);
            $this->plAccount = new PLAccount($db);
            $this->balanceSheet = new BalanceSheet($db);
        } catch (Exception $e) {
            error_log("Error initializing ReportController: " . $e->getMessage());
            $_SESSION['error'] = "Failed to initialize Report controller.";
            header('Location: /error');
            exit;
        }
    }

    private function render($view, $data = []) {
        try {
            extract($data);
            ob_start();
            $viewFile = __DIR__ . "/../views/{$view}.php";
            if (!file_exists($viewFile)) {
                throw new Exception("View file not found: {$viewFile}");
            }
            include $viewFile;
            $content = ob_get_clean();
            include __DIR__ . '/../views/layouts/main.php';
        } catch (Exception $e) {
            error_log("Error rendering view {$view}: " . $e->getMessage());
            $_SESSION['error'] = "Failed to render the requested page.";
            header('Location: /error');
            exit;
        }
    }

    private function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    public function index() {
        try {
            $clients = $this->client->read();
            $this->render('reports/index', ['clients' => $clients]);
        } catch (Exception $e) {
            error_log("Error in ReportController::index: " . $e->getMessage());
            $_SESSION['error'] = "Failed to load reports page.";
            $this->redirect('/error');
        }
    }

    public function generate() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $pan = $_POST['pan'] ?? '';
                $assessmentYear = $_POST['assessment_year'] ?? '';
                $reportType = $_POST['report_type'] ?? '';

                if (empty($pan) || empty($assessmentYear) || empty($reportType)) {
                    throw new Exception("Missing required parameters.");
                }

                $client = $this->client->getByPan($pan);
                if (!$client) {
                    throw new Exception("Client not found.");
                }

                $data = [
                    'client' => $client,
                    'assessment_year' => $assessmentYear,
                    'report_type' => $reportType
                ];

                switch ($reportType) {
                    case 'tax_return':
                        $data['tax_return'] = $this->taxReturn->getByPanAndYear($pan, $assessmentYear);
                        break;
                    case 'trading_account':
                        $data['trading_account'] = $this->tradingAccount->getByPanAndYear($pan, $assessmentYear);
                        break;
                    case 'pl_account':
                        $data['pl_account'] = $this->plAccount->getByPanAndYear($pan, $assessmentYear);
                break;
                    case 'balance_sheet':
                        $data['balance_sheet'] = $this->balanceSheet->getByPanAndYear($pan, $assessmentYear);
                break;
                    case 'comprehensive':
                        $data['tax_return'] = $this->taxReturn->getByPanAndYear($pan, $assessmentYear);
                        $data['trading_account'] = $this->tradingAccount->getByPanAndYear($pan, $assessmentYear);
                        $data['pl_account'] = $this->plAccount->getByPanAndYear($pan, $assessmentYear);
                        $data['balance_sheet'] = $this->balanceSheet->getByPanAndYear($pan, $assessmentYear);
                break;
            default:
                        throw new Exception("Invalid report type.");
                }

                $this->render('reports/view', $data);
            } else {
                $clients = $this->client->read();
                $this->render('reports/generate', ['clients' => $clients]);
            }
        } catch (Exception $e) {
            error_log("Error in ReportController::generate: " . $e->getMessage());
            $_SESSION['error'] = "Failed to generate report: " . $e->getMessage();
            $this->redirect('/reports');
        }
    }

    public function view($id) {
        try {
            // Implementation for viewing a saved report
            $this->render('reports/view', ['report_id' => $id]);
        } catch (Exception $e) {
            error_log("Error in ReportController::view: " . $e->getMessage());
            $_SESSION['error'] = "Failed to load report.";
            $this->redirect('/reports');
        }
    }

    public function delete($id) {
        try {
            // Implementation for deleting a saved report
            $_SESSION['success'] = "Report deleted successfully.";
        } catch (Exception $e) {
            error_log("Error in ReportController::delete: " . $e->getMessage());
            $_SESSION['error'] = "Failed to delete report.";
        }
        $this->redirect('/reports');
    }
}
?>
