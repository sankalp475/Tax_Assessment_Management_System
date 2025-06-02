<?php
// Only require controllers that exist
require_once __DIR__ . '/../controllers/ReturnController.php';
require_once __DIR__ . '/../controllers/ClientController.php';
require_once __DIR__ . '/../controllers/TradingAccountController.php';
require_once __DIR__ . '/../controllers/PLAccountController.php';
require_once __DIR__ . '/../controllers/BalanceSheetController.php';
require_once __DIR__ . '/../controllers/ReportController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../controllers/ProfileController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ErrorController.php';

class Router {
    private $routes = [
        'GET' => [],
        'POST' => []
    ];
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    private function matchRoute($routePath, $requestPath) {
        // Convert route parameters to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $routePath);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';

        if (preg_match($pattern, $requestPath, $matches)) {
            // Remove the full match from the beginning
            array_shift($matches);
            return $matches;
        }

        return false;
    }

    public function resolve() {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Remove query string from path
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        
        // Remove trailing slash
        $path = rtrim($path, '/');
        
        // If path is empty, set it to root
        if (empty($path)) {
            $path = '/';
        }
        
        error_log("Request URI: " . $path);
        error_log("Request Method: " . $method);
        error_log("Resolving path: " . $path);
        error_log("Method: " . $method);
        error_log("Available routes: " . print_r($this->routes, true));
        
        // First try exact match
        $callback = $this->routes[$method][$path] ?? null;
        
        // If no exact match, try matching with parameters
        if ($callback === null) {
            foreach ($this->routes[$method] as $routePath => $routeCallback) {
                $params = $this->matchRoute($routePath, $path);
                if ($params !== false) {
                    $callback = $routeCallback;
                    break;
                }
            }
        }
        
        if ($callback === null) {
            error_log("No route found for: " . $path);
            $controller = new ErrorController();
            $controller->notFound();
            return;
        }
        
        if (is_array($callback)) {
            $controller = new $callback[0]($this->db);
            $action = $callback[1];
            
            // If we have parameters, pass them to the controller method
            if (!empty($params)) {
                $controller->$action(...$params);
            } else {
                $controller->$action();
            }
        } else {
            $callback();
        }
    }
}

// Create database connection
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=tax_assessment_db",
        "root",
        "root@475",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    // Log the error and show a user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    die("Could not connect to the database. Please check your database configuration.");
}

$router = new Router($db);

// Define routes for existing controllers only
$router->get('/clients', [ClientController::class, 'index']);
$router->get('/clients/create', [ClientController::class, 'create']);
$router->post('/clients/store', [ClientController::class, 'store']);
$router->get('/clients/edit/{id}', [ClientController::class, 'edit']);
$router->post('/clients/update', [ClientController::class, 'update']);
$router->post('/clients/delete/{id}', [ClientController::class, 'delete']);
$router->get('/clients/view/{id}', [ClientController::class, 'view']);

$router->get('/returns', [ReturnController::class, 'index']);
$router->get('/returns/create', [ReturnController::class, 'create']);
$router->post('/returns/store', [ReturnController::class, 'store']);
$router->get('/returns/edit/{id}', [ReturnController::class, 'edit']);
$router->post('/returns/update', [ReturnController::class, 'update']);
$router->post('/returns/delete/{id}', [ReturnController::class, 'delete']);
$router->get('/returns/view/{id}', [ReturnController::class, 'view']);

// Trading Accounts routes
$router->get('/trading-accounts', [TradingAccountController::class, 'index']);
$router->get('/trading-accounts/create', [TradingAccountController::class, 'create']);
$router->post('/trading-accounts/create', [TradingAccountController::class, 'create']);
$router->get('/trading-accounts/edit/{id}', [TradingAccountController::class, 'edit']);
$router->post('/trading-accounts/edit/{id}', [TradingAccountController::class, 'edit']);
$router->get('/trading-accounts/view/{id}', [TradingAccountController::class, 'view']);
$router->post('/trading-accounts/delete/{id}', [TradingAccountController::class, 'delete']);

// P&L Accounts routes
$router->get('/pl-accounts', [PLAccountController::class, 'index']);
$router->get('/pl-accounts/create', [PLAccountController::class, 'create']);
$router->post('/pl-accounts/store', [PLAccountController::class, 'store']);
$router->get('/pl-accounts/edit/{id}', [PLAccountController::class, 'edit']);
$router->post('/pl-accounts/update', [PLAccountController::class, 'update']);
$router->post('/pl-accounts/delete/{id}', [PLAccountController::class, 'delete']);
$router->get('/pl-accounts/view/{id}', [PLAccountController::class, 'view']);

// Balance Sheet routes
$router->get('/balance-sheets', [BalanceSheetController::class, 'index']);
$router->get('/balance-sheets/create', [BalanceSheetController::class, 'create']);
$router->post('/balance-sheets/store', [BalanceSheetController::class, 'store']);
$router->get('/balance-sheets/edit/{id}', [BalanceSheetController::class, 'edit']);
$router->post('/balance-sheets/update', [BalanceSheetController::class, 'update']);
$router->post('/balance-sheets/delete/{id}', [BalanceSheetController::class, 'delete']);
$router->get('/balance-sheets/view/{id}', [BalanceSheetController::class, 'view']);

// Report routes
$router->get('/reports', [ReportController::class, 'index']);
$router->get('/reports/generate', [ReportController::class, 'generate']);
$router->post('/reports/generate', [ReportController::class, 'generate']);
$router->get('/reports/view/{id}', [ReportController::class, 'view']);
$router->post('/reports/delete/{id}', [ReportController::class, 'delete']);

// Settings routes
$router->get('/settings', [SettingController::class, 'index']);
$router->post('/settings/update', [SettingController::class, 'update']);

// Error route
$router->get('/error', function() {
    $controller = new ErrorController();
    $controller->notFound();
});

// Add a default route for the root path
$router->get('/', [DashboardController::class, 'index']);

// Resolve the route
$router->resolve();
?>
