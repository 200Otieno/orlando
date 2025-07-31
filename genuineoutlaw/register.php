<?php
require_once __DIR__ . '/auth_functions.php';

if (isLoggedIn()) {
    header('Location: home.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $securityKey = $_POST['security_key'] ?? '';
    $confirm_security_key = $_POST['confirm_security_key'] ?? '';
    
    if (empty($full_name) || empty($email) || empty($password) || empty($securityKey)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif ($securityKey !== $confirm_security_key) {
        $error = 'Security keys do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        if (getUserByEmail($email)) {
            $error = 'Email already registered.';
        } else {
            if (registerUser($full_name, $email, $password, $securityKey)) {
                $success = 'Registration successful! You can now <a href="index.php">login</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - OUTLAWZ PHARMACY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="security_key">Security Key</label>
                <input type="password" id="security_key" name="security_key" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_security_key">Confirm Security Key</label>
                <input type="password" id="confirm_security_key" name="confirm_security_key" required minlength="8">
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="index.php">Login here</a></p>
    </div>
</body>
</html>