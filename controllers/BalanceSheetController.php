<?php
require_once '../config/database.php';
require_once '../models/BalanceSheet.php';

class BalanceSheetController {
    private $db;
    private $balanceSheet;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->balanceSheet = new BalanceSheet($this->db);
    }

    public function index() {
        $stmt = $this->balanceSheet->read();
        $balanceSheets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure all required fields exist
        foreach ($balanceSheets as &$sheet) {
            if (!isset($sheet['ASSETS'])) {
                $sheet['ASSETS'] = json_encode(['current_assets' => 0, 'fixed_assets' => 0]);
            }
            if (!isset($sheet['LIABILITIES'])) {
                $sheet['LIABILITIES'] = json_encode(['current_liabilities' => 0, 'long_term_liabilities' => 0]);
            }
        }
        
        require_once '../views/balance/index.php';
    }

    public function create() {
        require_once '../views/balance/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->balanceSheet->pan = $_POST['pan'];
            $this->balanceSheet->assessment_year = $_POST['assessment_year'];
            
            // Create assets JSON
            $assets = [
                'current_assets' => floatval($_POST['current_assets']),
                'fixed_assets' => floatval($_POST['fixed_assets'])
            ];
            $this->balanceSheet->assets = json_encode($assets);
            
            // Create liabilities JSON
            $liabilities = [
                'current_liabilities' => floatval($_POST['current_liabilities']),
                'long_term_liabilities' => floatval($_POST['long_term_liabilities'])
            ];
            $this->balanceSheet->liabilities = json_encode($liabilities);

            if ($this->balanceSheet->create()) {
                $_SESSION['success'] = "Balance Sheet created successfully!";
                header("Location: /balance-sheets");
                exit();
            } else {
                $_SESSION['error'] = "Unable to create balance sheet.";
                header("Location: /balance-sheets/create");
                exit();
            }
        }
    }

    public function edit($id) {
        $this->balanceSheet->id = $id;
        if ($this->balanceSheet->readOne()) {
            $balanceSheet = [
                'ID' => $this->balanceSheet->id,
                'PAN' => $this->balanceSheet->pan,
                'ASSESSMENT_YEAR' => $this->balanceSheet->assessment_year,
                'ASSETS' => $this->balanceSheet->assets,
                'LIABILITIES' => $this->balanceSheet->liabilities
            ];
            require_once '../views/balance/edit.php';
        } else {
            $_SESSION['error'] = "Balance Sheet not found!";
            header("Location: /balance-sheets");
            exit();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->balanceSheet->id = $_POST['id'];
            $this->balanceSheet->pan = $_POST['pan'];
            $this->balanceSheet->assessment_year = $_POST['assessment_year'];
            
            // Create assets JSON
            $assets = [
                'current_assets' => floatval($_POST['current_assets']),
                'fixed_assets' => floatval($_POST['fixed_assets'])
            ];
            $this->balanceSheet->assets = json_encode($assets);
            
            // Create liabilities JSON
            $liabilities = [
                'current_liabilities' => floatval($_POST['current_liabilities']),
                'long_term_liabilities' => floatval($_POST['long_term_liabilities'])
            ];
            $this->balanceSheet->liabilities = json_encode($liabilities);

            if ($this->balanceSheet->update()) {
                $_SESSION['success'] = "Balance Sheet updated successfully!";
                header("Location: /balance-sheets");
                exit();
            } else {
                $_SESSION['error'] = "Unable to update balance sheet.";
                header("Location: /balance-sheets/edit/" . $this->balanceSheet->id);
                exit();
            }
        }
    }

    public function delete($id) {
        $this->balanceSheet->id = $id;
        if ($this->balanceSheet->delete()) {
            $_SESSION['success'] = "Balance Sheet deleted successfully!";
        } else {
            $_SESSION['error'] = "Unable to delete balance sheet.";
        }
        header("Location: /balance-sheets");
        exit();
    }
}
?>
