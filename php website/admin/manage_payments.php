<?php
include "../config/db.php"; 
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";
include "../includes/header.php"; 

global $conn; 

// --- Admin Access Check ---
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied. Please log in as an administrator.", "error");
    header("Location: ../auth/login.php");
    exit;
}

// --- FETCH PAYMENT/TRANSACTION DATA ---
$transactions = [];
$total_revenue = 0.00;

try {
    // FIX: 'completed' status doesn't exist in your schema, use 'delivered'
    $revenue_calc_sql = "SELECT SUM(final_total) AS total FROM orders WHERE status = 'delivered'";
    $revenue_stmt = $conn->query($revenue_calc_sql);
    $total_revenue = $revenue_stmt->fetch_assoc()['total'] ?? 0.00;
    
    // FIX: Remove 'completed' from status filter
    $sql = "SELECT 
                o.order_id, 
                u.full_name, 
                u.email, 
                o.order_date, 
                o.final_total, 
                o.shipping_cost, 
                o.discount_applied,
                o.status
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            WHERE o.status IN ('delivered', 'shipped', 'processing', 'pending', 'cancelled')
            ORDER BY o.order_date DESC";
            
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
    }

} catch (\Exception $e) {
    set_flash_message("A critical database error occurred: " . $e->getMessage(), "error");
}
?>

<h2 class="mb-4">💰 Payment & Transaction Management</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total Revenue (Delivered Orders)</h5>
                <p class="card-text fs-3">$<?= number_format($total_revenue, 2) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">Order Transaction Report</div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Shipping</th>
                    <th>Discount</th>
                    <th>Final Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= $transaction['order_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($transaction['full_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($transaction['email']) ?></small>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($transaction['order_date'])) ?></td>
                            <td>$<?= number_format($transaction['shipping_cost'], 2) ?></td>
                            <td>$<?= number_format($transaction['discount_applied'], 2) ?></td>
                            <td><strong>$<?= number_format($transaction['final_total'], 2) ?></strong></td>
                            <td>
                                <span class="badge bg-<?php 
                                    $status = strtolower($transaction['status']);
                                    if ($status == 'delivered') echo 'success';
                                    else if ($status == 'shipped') echo 'info';
                                    else if ($status == 'pending' || $status == 'processing') echo 'warning';
                                    else if ($status == 'cancelled') echo 'danger';
                                    else echo 'secondary';
                                ?>">
                                    <?= ucfirst(htmlspecialchars($transaction['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_edit_status.php?id=<?= $transaction['order_id'] ?>" class="btn btn-sm btn-primary">View/Edit Order</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>