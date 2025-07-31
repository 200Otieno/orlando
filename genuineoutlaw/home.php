<?php
require_once __DIR__ . '/auth_functions.php';
require_once __DIR__ . '/inventory_functions.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['logout'])) {
    logoutUser();
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';
$security_error = '';
$show_dashboard = false;

if (isset($_SESSION['security_verified']) && $_SESSION['security_verified'] === true) {
    $show_dashboard = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_security_key'])) {
    $entered_key = $_POST['security_key'] ?? '';
    if (verifySecurityKey($_SESSION['user_id'], $entered_key)) {
        $_SESSION['security_verified'] = true;
        $show_dashboard = true;
    } else {
        $security_error = "Invalid security key!";
    }
}

if ($show_dashboard && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $product_name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($product_name)) {
            $error = "Product name is required";
        } else {
            if (addProduct($product_name, $description)) {
                $success = "Product added successfully!";
            } else {
                $error = "Failed to add product";
            }
        }
    } elseif (isset($_POST['add_inventory'])) {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $batch_number = trim($_POST['batch_number'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        $expiry_date = $_POST['expiry_date'] ?? '';
        $added_by = $_SESSION['user_id'];
        
        $errors = [];
        if ($product_id <= 0) $errors[] = "Invalid product selection";
        if (empty($batch_number)) $errors[] = "Batch number is required";
        if ($quantity <= 0) $errors[] = "Quantity must be positive";
        if (empty($expiry_date)) $errors[] = "Expiry date is required";
        
        if (empty($errors)) {
            if (addInventoryItem($product_id, $batch_number, $quantity, $expiry_date, $added_by)) {
                $success = "Inventory item added successfully!";
            } else {
                $error = "Failed to add inventory item";
            }
        } else {
            $error = implode("<br>", $errors);
        }
    }
}

if ($show_dashboard) {
    $products = getAllProducts();
    $expiry_report = getExpiryReport();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OUTLAWZ PHARMACY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .security-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .security-modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
        }
    </style>
</head>
<body>
    <?php if (!$show_dashboard): ?>
    <div id="securityModal" class="security-modal">
        <div class="security-modal-content">
            <h2>Security Verification</h2>
            <p>Please enter your security key to continue</p>
            <?php if (isset($security_error)): ?>
                <div class="alert alert-error"><?= $security_error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <input type="password" name="security_key" placeholder="Enter security key" required autofocus>
                </div>
                <button type="submit" name="verify_security_key">Verify</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div id="mainContent" style="<?= $show_dashboard ? 'display: block;' : 'display: none;' ?>">
        <header>
            <a href="home.php" class="logo">OUTLAWZ PHARMACY</a>
            <nav>
                <a href="home.php">Dashboard</a>
                <a href="admin_page.php">Notifications</a>
                <a href="home.php?logout=1">Logout</a>
            </nav>
        </header>
        
        <div class="container">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="card">
                <h2>Add New Product</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>
                    <button type="submit" name="add_product">Add Product</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Add Inventory Item</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="product_id">Product</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['product_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="batch_number">Batch Number</label>
                        <input type="text" id="batch_number" name="batch_number" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" required>
                    </div>
                    <button type="submit" name="add_inventory">Add Inventory</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Expiry Report</h2>
                <?php if (empty($expiry_report)): ?>
                    <p>No inventory items found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Batch Number</th>
                                <th>Quantity</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiry_report as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= htmlspecialchars($item['batch_number']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= date('M d, Y', strtotime($item['expiry_date'])) ?></td>
                                    <td class="status-<?= strtolower(str_replace('-', '', $item['status'])) ?>">
                                        <?= $item['status'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>