<?php
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/PLAccount.php';

class PLAccountController {
    private $db;
    private $client;
    private $plAccount;

    public function __construct($db) {
        try {
            $this->db = $db;
            $this->client = new Client($db);
            $this->plAccount = new PLAccount($db);
        } catch (Exception $e) {
            error_log("Error initializing PLAccountController: " . $e->getMessage());
            $_SESSION['error'] = "Failed to initialize P&L Account controller.";
            header('Location: /error');
            exit;
        }
    }

    private function render($view, $data = []) {
        try {
            // Extract data to make variables available in view
            extract($data);
            
            // Start output buffering
            ob_start();
            
            // Include the view file
            $viewFile = __DIR__ . "/../views/{$view}.php";
            if (!file_exists($viewFile)) {
                throw new Exception("View file not found: {$viewFile}");
            }
            include $viewFile;
            
            // Get the contents of the buffer
            $content = ob_get_clean();
            
            // Include the layout
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
            $accounts = $this->plAccount->read();
            $this->render('pl-accounts/index', ['accounts' => $accounts]);
        } catch (Exception $e) {
            error_log("Error in PLAccountController::index: " . $e->getMessage());
            $_SESSION['error'] = "Failed to load P&L accounts.";
            $this->redirect('/error');
        }
    }

    public function create() {
        try {
            $clients = $this->client->read();
            $this->render('pl-accounts/create', ['clients' => $clients]);
        } catch (Exception $e) {
            error_log("Error in PLAccountController::create: " . $e->getMessage());
            $_SESSION['error'] = "Failed to load create form.";
            $this->redirect('/error');
        }
    }

    public function store() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'pan' => $_POST['pan'],
                    'assessment_year' => $_POST['assessment_year'],
                    'gross_profit' => $_POST['gross_profit'],
                    'indirect_income' => $_POST['indirect_income'],
                    'indirect_expenses' => $_POST['indirect_expenses'],
                    'net_profit' => $_POST['net_profit']
                ];

                if ($this->plAccount->create($data)) {
                    $_SESSION['success'] = "P&L Account created successfully.";
                    $this->redirect('/pl-accounts');
                } else {
                    throw new Exception("Failed to create P&L account.");
                }
            }
        } catch (Exception $e) {
            error_log("Error in PLAccountController::store: " . $e->getMessage());
            $_SESSION['error'] = "Failed to create P&L account.";
            $this->redirect('/pl-accounts/create');
        }
    }

    public function edit($id) {
        try {
            $this->plAccount->id = $id;
            if (!$this->plAccount->readOne()) {
                throw new Exception("P&L Account not found.");
            }
            
            // Create an array with the PLAccount data
            $plAccount = [
                'id' => $this->plAccount->id,
                'pan' => $this->plAccount->pan,
                'assessment_year' => $this->plAccount->assessment_year,
                'gross_profit' => $this->plAccount->gross_profit,
                'indirect_income' => $this->plAccount->indirect_income,
                'indirect_expenses' => $this->plAccount->indirect_expenses,
                'net_profit' => $this->plAccount->net_profit,
                'created_at' => $this->plAccount->created_at,
                'updated_at' => $this->plAccount->updated_at
            ];
            
            // Get client information for the dropdown
            $clients = $this->client->read();
            $clients = $clients->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('pl-accounts/edit', [
                'plAccount' => $plAccount,
                'clients' => $clients
            ]);
        } catch (Exception $e) {
            error_log("Error in PLAccountController::edit: " . $e->getMessage());
            $_SESSION['error'] = "Failed to load P&L account for editing.";
            $this->redirect('/pl-accounts');
        }
    }

    public function update() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'id' => $_POST['id'],
                    'pan' => $_POST['pan'],
                    'assessment_year' => $_POST['assessment_year'],
                    'gross_profit' => $_POST['gross_profit'],
                    'indirect_income' => $_POST['indirect_income'],
                    'indirect_expenses' => $_POST['indirect_expenses'],
                    'net_profit' => $_POST['net_profit']
                ];

                if ($this->plAccount->update($data)) {
                    $_SESSION['success'] = "P&L Account updated successfully.";
                    $this->redirect('/pl-accounts');
                } else {
                    throw new Exception("Failed to update P&L account.");
                }
            }
        } catch (Exception $e) {
            error_log("Error in PLAccountController::update: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update P&L account.";
            $this->redirect('/pl-accounts');
        }
    }

    public function delete($id) {
        try {
            if ($this->plAccount->delete($id)) {
                $_SESSION['success'] = "P&L Account deleted successfully.";
            } else {
                throw new Exception("Failed to delete P&L account.");
            }
        } catch (Exception $e) {
            error_log("Error in PLAccountController::delete: " . $e->getMessage());
            $_SESSION['error'] = "Failed to delete P&L account.";
        }
        $this->redirect('/pl-accounts');
    }

    public function view($id) {
        try {
            $this->plAccount->id = $id;
            if (!$this->plAccount->readOne()) {
                throw new Exception("P&L Account not found.");
            }
            
            // Create an array with the PLAccount data
            $plAccount = [
                'id' => $this->plAccount->id,
                'pan' => $this->plAccount->pan,
                'assessment_year' => $this->plAccount->assessment_year,
                'gross_profit' => $this->plAccount->gross_profit,
                'indirect_income' => $this->plAccount->indirect_income,
                'indirect_expenses' => $this->plAccount->indirect_expenses,
                'net_profit' => $this->plAccount->net_profit,
                'created_at' => $this->plAccount->created_at,
                'updated_at' => $this->plAccount->updated_at
            ];
            
            // Get client information
            $client = $this->client->getByPan($plAccount['pan']);
            
            $this->render('pl-accounts/view', [
                'plAccount' => $plAccount,
                'client' => $client
            ]);
        } catch (Exception $e) {
            error_log("Error in PLAccountController::view: " . $e->getMessage());
            $_SESSION['error'] = "Failed to load P&L account details.";
            $this->redirect('/pl-accounts');
        }
    }
}
?> 
