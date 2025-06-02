<?php

class TradingAccountController {
    private $model;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new TradingAccount($db);
    }

    public function index() {
        $tradingAccounts = $this->model->getAll();
        $this->render('trading-accounts/index', [
            'tradingAccounts' => $tradingAccounts
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'PAN' => $_POST['PAN'],
                'ASSES_YEAR_1' => $_POST['ASSES_YEAR_1'],
                'ASSES_YEAR_2' => $_POST['ASSES_YEAR_2'],
                'OPENING_STOCK' => $_POST['OPENING_STOCK'],
                'PURCHASES' => $_POST['PURCHASES'],
                'DIRECT_EXPENSES' => $_POST['DIRECT_EXPENSES'],
                'CLOSING_STOCK' => $_POST['CLOSING_STOCK'],
                'GROSS_PROFIT' => $_POST['GROSS_PROFIT']
            ];

            if ($this->model->create($data)) {
                $_SESSION['success'] = "Trading account created successfully.";
                header('Location: /trading-accounts');
                exit;
            } else {
                $_SESSION['error'] = "Error creating trading account.";
            }
        }

        $this->render('trading-accounts/create');
    }

    public function edit($id) {
        $account = $this->model->getById($id);
        
        if (!$account) {
            $_SESSION['error'] = "Trading account not found.";
            header('Location: /trading-accounts');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'PAN' => $_POST['PAN'],
                'ASSES_YEAR_1' => $_POST['ASSES_YEAR_1'],
                'ASSES_YEAR_2' => $_POST['ASSES_YEAR_2'],
                'OPENING_STOCK' => $_POST['OPENING_STOCK'],
                'PURCHASES' => $_POST['PURCHASES'],
                'DIRECT_EXPENSES' => $_POST['DIRECT_EXPENSES'],
                'CLOSING_STOCK' => $_POST['CLOSING_STOCK'],
                'GROSS_PROFIT' => $_POST['GROSS_PROFIT']
            ];

            if ($this->model->update($id, $data)) {
                $_SESSION['success'] = "Trading account updated successfully.";
                header('Location: /trading-accounts');
                exit;
            } else {
                $_SESSION['error'] = "Error updating trading account.";
            }
        }

        $this->render('trading-accounts/edit', [
            'account' => $account
        ]);
    }

    public function view($id) {
        $account = $this->model->getById($id);
        
        if (!$account) {
            $_SESSION['error'] = "Trading account not found.";
            header('Location: /trading-accounts');
            exit;
        }

        $this->render('trading-accounts/view', [
            'account' => $account
        ]);
    }

    public function delete($id) {
        if ($this->model->delete($id)) {
            $_SESSION['success'] = "Trading account deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting trading account.";
        }
        header('Location: /trading-accounts');
        exit;
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }
} 
