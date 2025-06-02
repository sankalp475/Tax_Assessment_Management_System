<?php
require_once __DIR__ . '/../models/Setting.php';

class SettingController {
    private $db;
    private $setting;

    public function __construct($db) {
        $this->db = $db;
        $this->setting = new Setting($db);
    }

    public function index() {
        try {
            $settings = $this->setting->getAllSettings();
            $this->render('settings/index', [
                'settings' => $settings
            ]);
        } catch (Exception $e) {
            error_log("Error in SettingController::index: " . $e->getMessage());
            $_SESSION['error'] = "Failed to load settings.";
            $this->redirect('/error');
        }
    }

    public function update() {
        try {
            $settings = [
                'company_name' => $_POST['company_name'] ?? '',
                'company_address' => $_POST['company_address'] ?? '',
                'company_phone' => $_POST['company_phone'] ?? '',
                'company_email' => $_POST['company_email'] ?? '',
                'tax_year_start' => $_POST['tax_year_start'] ?? '',
                'tax_year_end' => $_POST['tax_year_end'] ?? ''
            ];

            $this->setting->updateSettings($settings);
            $_SESSION['success'] = "Settings updated successfully.";
        } catch (Exception $e) {
            error_log("Error in SettingController::update: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update settings: " . $e->getMessage();
        }

        $this->redirect('/settings');
    }

    private function render($view, $data = []) {
        $viewFile = __DIR__ . "/../views/{$view}.php";
        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }
        
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        require $viewFile;
        
        // Get the contents of the buffer
        $content = ob_get_clean();
        
        // Include the layout with the content
        require __DIR__ . "/../views/layouts/main.php";
    }

    private function redirect($path) {
        header("Location: {$path}");
        exit;
    }
} 
