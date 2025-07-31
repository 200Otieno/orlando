<?php
require_once __DIR__ . '/config.php';

function addProduct($product_name, $description) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO products (product_name, description) VALUES (?, ?)");
    return $stmt->execute([$product_name, $description]);
}

function addInventoryItem($product_id, $batch_number, $quantity, $expiry_date, $added_by) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO inventory (product_id, batch_number, quantity, expiry_date, added_by) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$product_id, $batch_number, $quantity, $expiry_date, $added_by]);
}

function getExpiryReport() {
    global $pdo;
    
    $today = date('Y-m-d');
    $soon = date('Y-m-d', strtotime('+30 days'));
    
    $query = "
        SELECT 
            p.product_name,
            i.batch_number,
            i.quantity,
            i.expiry_date,
            CASE 
                WHEN i.expiry_date < ? THEN 'Expired'
                WHEN i.expiry_date <= ? THEN 'Soon-to-Expire'
                ELSE 'Safe'
            END AS status
        FROM inventory i
        JOIN products p ON i.product_id = p.id
        ORDER BY i.expiry_date ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$today, $soon]);
    return $stmt->fetchAll();
}

function getAllProducts() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products ORDER BY product_name");
    return $stmt->fetchAll();
}
?>