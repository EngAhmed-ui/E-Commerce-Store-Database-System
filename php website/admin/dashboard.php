<?php
// Core setup and security
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";

// Only admin can access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied. Please log in as an administrator.", "error");
    header("Location: ../auth/login.php");
    exit;
}

include "../includes/header.php";
?>

<div class="card p-4 shadow-sm">
    <h2 class="card-title">Admin Dashboard</h2>
    <p class="lead">Welcome, **<?php echo htmlspecialchars($_SESSION['full_name']); ?>**! Use the links below to manage the store.</p>

    <div class="list-group">
        <a href="manage_users.php" class="list-group-item list-group-item-action">👥 Manage Users & Roles</a>
        <a href="manage_user_addresses.php" class="list-group-item list-group-item-action">🏠 Manage All User Addresses</a>
        <a href="manage_payments.php" class="list-group-item list-group-item-action">💳 Payment & Transaction Report</a>
        <a href="categories.php" class="list-group-item list-group-item-action">📦 Manage Categories</a>
        <a href="products.php" class="list-group-item list-group-item-action">📦 Manage Products</a>
        <a href="variants.php" class="list-group-item list-group-item-action">📦 Manage Variants</a>
        <a href="variant_attributes.php" class="list-group-item list-group-item-action">📦 Variant Attributes</a>
        <a href="orders.php" class="list-group-item list-group-item-action">🛒 Manage Orders</a>
        <a href="coupons.php" class="list-group-item list-group-item-action">🏷️ Manage Coupons</a>
    </div>
</div>

<?php include "../includes/footer.php"; ?>