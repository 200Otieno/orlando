<?php
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
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        $errors = [];

        // Check if passwords match
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        // Check password strength (optional)
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

        // Check if username is already taken
        $stmt = $conn->prepare("SELECT id FROM members WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username already taken.";
        }

        // Check if email is already registered
        $stmt = $conn->prepare("SELECT id FROM members WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already registered.";
        }

        // If there are errors, redirect back to signup page with errors
        if (!empty($errors)) {
            $errorString = implode(" ", $errors);
            header("Location: signup.html?error=" . urlencode($errorString));
            exit();
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into database
        $stmt = $conn->prepare("INSERT INTO members (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->execute();

        // Redirect to login page with success message
        header("Location: login.html?success=Account created successfully. Please login.");
        exit();
    }
} catch(PDOException $e) {
    header("Location: signup.html?error=Database error: " . $e->getMessage());
    exit();
}
?>