<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'auth_system';
$username = 'root'; // Change this to your database username
$password = ''; // Change this to your database password

try {
    // Create connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userInput = $_POST['username'];
        $password = $_POST['password'];

        // Check if input is email or username
        if (filter_var($userInput, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT * FROM members WHERE email = :input";
        } else {
            $sql = "SELECT * FROM members WHERE username = :input";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':input', $userInput);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect to a welcome page or dashboard
                header("Location: welcome.php");
                exit();
            } else {
                // Invalid password
                header("Location: login.html?error=Invalid username or password");
                exit();
            }
        } else {
            // User not found
            header("Location: login.html?error=Invalid username or password");
            exit();
        }
    }
} catch(PDOException $e) {
    header("Location: login.html?error=Database error: " . $e->getMessage());
    exit();
}
?>