# Controllers Documentation

## Overview
Controllers in the Tax Assessment Management System handle the business logic and coordinate between models and views. They process incoming requests, interact with the database through models, and render appropriate views.

## ReturnController

### Class Definition
```php
class ReturnController {
    private $db;
    private $client;
    private $taxReturn;
    private $tradingAccount;
    private $plAccount;
    private $balanceSheet;
}
```

### Dependencies
- Database connection
- Client model
- TaxReturn model
- TradingAccount model
- PLAccount model
- BalanceSheet model

### Methods

#### Constructor
```php
public function __construct()
```
- Initializes database connection
- Creates instances of required models
- Handles connection errors

#### Render Method
```php
private function render($view, $data = [])
```
- Renders views with provided data
- Handles view file inclusion
- Manages output buffering
- Integrates with main layout

#### Index Method
```php
public function index()
```
- Lists all tax returns
- Renders the returns/index view
- Handles errors and redirects

#### Create Method
```php
public function create()
```
- Shows tax return creation form
- Loads client data for dropdown
- Renders returns/create view

#### Store Method
```php
public function store()
```
- Handles POST request for new tax return
- Validates input data
- Creates new tax return record
- Redirects with success/error message

#### Edit Method
```php
public function edit($id)
```
- Loads existing tax return data
- Renders edit form
- Handles not found cases

#### Update Method
```php
public function update()
```
- Processes tax return updates
- Validates input
- Updates database record
- Handles success/error cases

#### Delete Method
```php
public function delete($id)
```
- Removes tax return record
- Handles success/error cases
- Redirects to index

#### View Method
```php
public function view($id)
```
- Shows detailed tax return information
- Loads associated client data
- Renders detailed view

#### Report Generation Methods
```php
public function clientHistory($pan)
public function tradingReport($pan)
public function plReport($pan)
public function balanceReport($pan)
```
- Generate various types of reports
- Load relevant data
- Render report views

#### PDF Generation
```php
public function generatePDF($type, $pan)
```
- Creates PDF reports using TCPDF
- Supports multiple report types
- Handles document formatting

### Error Handling
- Uses try-catch blocks for error management
- Logs errors to application log
- Redirects to error page when necessary
- Sets session messages for user feedback

### Security Features
- Input validation
- SQL injection prevention
- XSS protection through htmlspecialchars
- Session management

## Best Practices
1. Always validate input data
2. Use prepared statements for database queries
3. Handle errors gracefully
4. Log important events and errors
5. Use proper redirects after actions
6. Maintain separation of concerns
7. Follow RESTful principles
8. Implement proper security measures

## Usage Example
```php
// Creating a new tax return
$controller = new ReturnController();
$controller->create();

// Viewing a tax return
$controller->view($id);

// Generating a report
$controller->generatePDF('trading', $pan);
``` 
