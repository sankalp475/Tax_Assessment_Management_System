<?php
require_once '../config/database.php';
require_once '../models/PLAccount.php';

class PLController {
    private $db;
    private $plAccount;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->plAccount = new PLAccount($this->db);
    }

    public function index() {
        $stmt = $this->plAccount->read();
        $plAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once '../views/pl/index.php';
    }

    public function create() {
        require_once '../views/pl/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->plAccount->pan = $_POST['pan'];
            $this->plAccount->assessment_year = $_POST['assessment_year'];
            $this->plAccount->gross_profit = $_POST['gross_profit'];
            $this->plAccount->indirect_income = $_POST['indirect_income'];
            $this->plAccount->indirect_expenses = $_POST['indirect_expenses'];
            $this->plAccount->net_profit = $_POST['net_profit'];

            if ($this->plAccount->create()) {
                $_SESSION['success'] = "P&L Account created successfully!";
                header("Location: /pl");
                exit();
            } else {
                $_SESSION['error'] = "Unable to create P&L account.";
                header("Location: /pl/create");
                exit();
            }
        }
    }

    public function edit($id) {
        $this->plAccount->id = $id;
        if ($this->plAccount->readOne()) {
            require_once '../views/pl/edit.php';
        } else {
            $_SESSION['error'] = "P&L Account not found!";
            header("Location: /pl");
            exit();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->plAccount->id = $_POST['id'];
            $this->plAccount->pan = $_POST['pan'];
            $this->plAccount->assessment_year = $_POST['assessment_year'];
            $this->plAccount->gross_profit = $_POST['gross_profit'];
            $this->plAccount->indirect_income = $_POST['indirect_income'];
            $this->plAccount->indirect_expenses = $_POST['indirect_expenses'];
            $this->plAccount->net_profit = $_POST['net_profit'];

            if ($this->plAccount->update()) {
                $_SESSION['success'] = "P&L Account updated successfully!";
                header("Location: /pl");
                exit();
            } else {
                $_SESSION['error'] = "Unable to update P&L account.";
                header("Location: /pl/edit/" . $this->plAccount->id);
                exit();
            }
        }
    }

    public function delete($id) {
        $this->plAccount->id = $id;
        if ($this->plAccount->delete()) {
            $_SESSION['success'] = "P&L Account deleted successfully!";
        } else {
            $_SESSION['error'] = "Unable to delete P&L account.";
        }
        header("Location: /pl");
        exit();
    }
}
?>
