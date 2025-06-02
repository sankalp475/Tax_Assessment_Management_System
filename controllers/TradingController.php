<?php
require_once 'models/TradingAccount.php';

class TradingController {
    private $db;
    private $tradingAccount;

    public function __construct($db) {
        $this->db = $db;
        $this->tradingAccount = new TradingAccount($db);
    }

    public function index() {
        $stmt = $this->tradingAccount->read();
        $tradingAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once 'views/trading/index.php';
    }

    public function create() {
        require_once 'views/trading/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $errors = [];
            
            if (empty($_POST['pan']) || !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $_POST['pan'])) {
                $errors[] = "Invalid PAN number format";
            }
            
            if (empty($_POST['asses_year_1']) || !preg_match('/^[0-9]{4}-[0-9]{2}$/', $_POST['asses_year_1'])) {
                $errors[] = "Invalid assessment year format";
            }
            
            if (empty($_POST['asses_year_2']) || !preg_match('/^[0-9]{4}-[0-9]{2}$/', $_POST['asses_year_2'])) {
                $errors[] = "Invalid assessment year format";
            }
            
            if (!is_numeric($_POST['opening_stock'])) {
                $errors[] = "Opening stock must be a number";
            }
            
            if (!is_numeric($_POST['purchases'])) {
                $errors[] = "Purchases must be a number";
            }
            
            if (!is_numeric($_POST['direct_expenses'])) {
                $errors[] = "Direct expenses must be a number";
            }
            
            if (!is_numeric($_POST['closing_stock'])) {
                $errors[] = "Closing stock must be a number";
            }

            if (empty($errors)) {
                // Calculate gross profit
                $grossProfit = ($_POST['opening_stock'] + $_POST['purchases'] + $_POST['direct_expenses']) - $_POST['closing_stock'];

                // Set trading account properties
                $this->tradingAccount->pan = $_POST['pan'];
                $this->tradingAccount->asses_year_1 = $_POST['asses_year_1'];
                $this->tradingAccount->asses_year_2 = $_POST['asses_year_2'];
                $this->tradingAccount->opening_stock = $_POST['opening_stock'];
                $this->tradingAccount->purchases = $_POST['purchases'];
                $this->tradingAccount->direct_expenses = $_POST['direct_expenses'];
                $this->tradingAccount->closing_stock = $_POST['closing_stock'];
                $this->tradingAccount->gross_profit = $grossProfit;

                if ($this->tradingAccount->create()) {
                    $_SESSION['success'] = "Trading account created successfully";
                    header("Location: /trading");
                    exit();
                } else {
                    $_SESSION['error'] = "Unable to create trading account";
                }
            } else {
                $_SESSION['error'] = implode(", ", $errors);
            }
        }
        
        header("Location: /trading/create");
        exit();
    }

    public function edit($id) {
        $this->tradingAccount->id = $id;
        
        if ($this->tradingAccount->readOne()) {
            require_once 'views/trading/edit.php';
        } else {
            $_SESSION['error'] = "Trading account not found";
            header("Location: /trading");
            exit();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $errors = [];
            
            if (empty($_POST['pan']) || !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $_POST['pan'])) {
                $errors[] = "Invalid PAN number format";
            }
            
            if (empty($_POST['asses_year_1']) || !preg_match('/^[0-9]{4}-[0-9]{2}$/', $_POST['asses_year_1'])) {
                $errors[] = "Invalid assessment year format";
            }
            
            if (empty($_POST['asses_year_2']) || !preg_match('/^[0-9]{4}-[0-9]{2}$/', $_POST['asses_year_2'])) {
                $errors[] = "Invalid assessment year format";
            }
            
            if (!is_numeric($_POST['opening_stock'])) {
                $errors[] = "Opening stock must be a number";
            }
            
            if (!is_numeric($_POST['purchases'])) {
                $errors[] = "Purchases must be a number";
            }
            
            if (!is_numeric($_POST['direct_expenses'])) {
                $errors[] = "Direct expenses must be a number";
            }
            
            if (!is_numeric($_POST['closing_stock'])) {
                $errors[] = "Closing stock must be a number";
            }

            if (empty($errors)) {
                // Calculate gross profit
                $grossProfit = ($_POST['opening_stock'] + $_POST['purchases'] + $_POST['direct_expenses']) - $_POST['closing_stock'];

                // Set trading account properties
                $this->tradingAccount->id = $_POST['id'];
                $this->tradingAccount->pan = $_POST['pan'];
                $this->tradingAccount->asses_year_1 = $_POST['asses_year_1'];
                $this->tradingAccount->asses_year_2 = $_POST['asses_year_2'];
                $this->tradingAccount->opening_stock = $_POST['opening_stock'];
                $this->tradingAccount->purchases = $_POST['purchases'];
                $this->tradingAccount->direct_expenses = $_POST['direct_expenses'];
                $this->tradingAccount->closing_stock = $_POST['closing_stock'];
                $this->tradingAccount->gross_profit = $grossProfit;

                if ($this->tradingAccount->update()) {
                    $_SESSION['success'] = "Trading account updated successfully";
                    header("Location: /trading");
                    exit();
                } else {
                    $_SESSION['error'] = "Unable to update trading account";
                }
            } else {
                $_SESSION['error'] = implode(", ", $errors);
            }
        }
        
        header("Location: /trading/edit/" . $_POST['id']);
        exit();
    }

    public function delete($id) {
        $this->tradingAccount->id = $id;
        
        if ($this->tradingAccount->delete()) {
            $_SESSION['success'] = "Trading account deleted successfully";
        } else {
            $_SESSION['error'] = "Unable to delete trading account";
        }
        
        header("Location: /trading");
        exit();
    }
}
?>
