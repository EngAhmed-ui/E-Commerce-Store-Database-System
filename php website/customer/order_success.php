<?php
include_once __DIR__ . '/../includes/functions.php';
if (!defined('DB_CONFIG_PATH')) {
    define('DB_CONFIG_PATH', __DIR__ . '/../config/db.php');
}
require_once DB_CONFIG_PATH;

if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

$order_id = (int)($_GET['order_id'] ?? 0);

if ($order_id > 0) {
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT order_date, status FROM Orders WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $stmt->close();

    if ($order_result->num_rows > 0) {
        $order = $order_result->fetch_assoc();
        ?>
        <div class="alert alert-success p-5 text-center mx-auto shadow-lg" style="max-width: 600px;">
            <h2 class="text-success mb-4 fs-1">🎉 Order Placed Successfully!</h2>
            <p class="fs-5">Your order number is: <strong>#<?= $order_id ?></strong></p>
            <p class="text-muted">Date: <?= htmlspecialchars($order['order_date']) ?></p>
            <p class="mb-4">Status: <span class="badge bg-success fs-6 text-uppercase"><?= htmlspecialchars($order['status']) ?></span></p>
            
            <p class="lead">
                Thank you for shopping with us!
            </p>
            
            <div class="mt-5 d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a href="orders.php" class="btn btn-primary btn-lg">View Your Orders</a>
                <a href="products.php" class="btn btn-outline-primary btn-lg">Continue Shopping</a>
            </div>
        </div>
        <?php
    } else {
        echo "<div class='alert alert-danger text-center mx-auto' style='max-width: 600px;'>Error: Order not found.</div>";
    }
} else {
    echo "<div class='alert alert-danger text-center mx-auto' style='max-width: 600px;'>Invalid Order ID.</div>";
}

include "../includes/footer.php";
?>