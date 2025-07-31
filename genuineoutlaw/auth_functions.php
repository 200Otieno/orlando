<?php
require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function loginUser($email, $password, $securityKey) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && 
        password_verify($password, $user['password_hash']) && 
        password_verify($securityKey, $user['security_key_hash'])) {
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['loggedin'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        return true;
    }
    
    return false;
}

function logoutUser() {
    $_SESSION = array();
    session_destroy();
}

function registerUser($full_name, $email, $password, $securityKey) {
    global $pdo;
    
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $securityKeyHash = password_hash($securityKey, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, security_key_hash) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$full_name, $email, $passwordHash, $securityKeyHash]);
}

function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function verifySecurityKey($user_id, $entered_key) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT security_key_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($entered_key, $user['security_key_hash'])) {
        return true;
    }
    
    return false;
}
?>