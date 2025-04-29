<?php
/**
 * Registration process handler
 * 
 * This file handles user registration requests
 */

// Initialize the response array
$response = array(
    'status' => 'error',
    'message' => 'An unknown error occurred'
);

// Include database connection
require_once "config/database.php";

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all required fields are present
    if (
        isset($_POST['action']) && 
        $_POST['action'] === 'register' && 
        isset($_POST['fullname']) && 
        isset($_POST['email']) && 
        isset($_POST['username']) && 
        isset($_POST['password'])
    ) {
        // Validate and sanitize input
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        // Validate input
        $validation_errors = array();
        
        // Validate full name
        if (empty($fullname)) {
            $validation_errors[] = 'Full name is required';
        } elseif (strlen($fullname) < 3 || strlen($fullname) > 100) {
            $validation_errors[] = 'Full name must be between 3 and 100 characters';
        }
        
        // Validate email
        if (empty($email)) {
            $validation_errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validation_errors[] = 'Invalid email format';
        }
        
        // Validate username
        if (empty($username)) {
            $validation_errors[] = 'Username is required';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $validation_errors[] = 'Username must be between 3 and 50 characters';
        }
        
        // Validate password
        if (empty($password)) {
            $validation_errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $validation_errors[] = 'Password must be at least 6 characters';
        }
        
        // If validation passes
        if (empty($validation_errors)) {
            try {
                // Get database connection
                $database = Database::getInstance();
                $conn = $database->getConnection();
                
                // Check if username already exists
                $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->fetchColumn() > 0) {
                    $response['message'] = 'Username already exists. Please choose a different username.';
                } else {
                    // Check if email already exists
                    $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->execute();
                    
                    if ($stmt->fetchColumn() > 0) {
                        $response['message'] = 'Email already exists. Please use a different email.';
                    } else {
                        // Hash the password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new user
                        $sql = "INSERT INTO users (username, password, fullname, email, role) VALUES (:username, :password, :fullname, :email, 'user')";
                        $stmt = $conn->prepare($sql);
                        
                        // Bind parameters
                        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                        $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        
                        // Execute the query
                        if ($stmt->execute()) {
                            $response['status'] = 'success';
                            $response['message'] = 'Registration successful! Please login.';
                        } else {
                            $response['message'] = 'Something went wrong. Please try again later.';
                        }
                    }
                }
            } catch (PDOException $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode(', ', $validation_errors);
        }
    } else {
        $response['message'] = 'Missing required fields';
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;