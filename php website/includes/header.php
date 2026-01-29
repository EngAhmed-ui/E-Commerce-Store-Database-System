<?php
include_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .flash-container { margin-top: 15px; }
        .alert-error { background-color: #f8d7da; color: #842029; border-color: #f5c2c7; }
        header { background-color: #343a40; }
        header a { color: #ffffff !important; margin-left: 15px; }
        header a:hover { color: #adb5bd !important; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center py-3">
                <a href="<?php echo BASE_URL; ?>/index.php" class="logo text-decoration-none text-white fs-4 fw-bold">E-Store</a>
                <nav>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/customer/products.php">Products</a>
                        <a href="<?php echo BASE_URL; ?>/cart/cart.php">Cart</a>
                        <a href="<?php echo BASE_URL; ?>/customer/orders.php">Orders</a>
                        <a href="<?php echo BASE_URL; ?>/customer/profile.php">Profile</a>
                        <a href="<?php echo BASE_URL; ?>/customer/manage_addresses.php">Addresses</a> 
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Admin</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/auth/logout.php">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/auth/login.php">Login</a>
                        <a href="<?php echo BASE_URL; ?>/auth/register.php">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mt-4">
        <?php if ($flash): ?>
            <div class="alert alert-dismissible fade show alert-<?php echo htmlspecialchars($flash['type'] == 'error' ? 'danger' : $flash['type']); ?> flash-container" role="alert">
                <?php echo htmlspecialchars($flash['content']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>