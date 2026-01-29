<?php
include "../config/db.php";
include_once __DIR__ . '/../includes/functions.php'; 

if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit;
}

if(isset($_GET['cancel'])){
    $order_id = intval($_GET['cancel']);
    
    $check_res = $conn->query("SELECT status, user_id FROM Orders WHERE order_id=$order_id");
    $order_to_cancel = $check_res->fetch_assoc();
    
    if($order_to_cancel && $order_to_cancel['user_id'] == $user_id){
        if(strtolower($order_to_cancel['status']) == 'processing' || strtolower($order_to_cancel['status']) == 'pending'){
            if($conn->query("UPDATE Orders SET status='cancelled' WHERE order_id=$order_id")){
                set_flash_message("Order #$order_id has been successfully cancelled.", 'success');
            } else {
                set_flash_message("Database error during cancellation: " . $conn->error, 'error');
            }
        } else {
            set_flash_message("Order #$order_id cannot be cancelled as its status is " . ucfirst($order_to_cancel['status']) . ".", 'error');
        }
    } else {
        set_flash_message("Order not found or access denied.", 'error');
    }
    
    header("Location: orders.php"); 
    exit;
}

$orders_res = $conn->query("
    SELECT o.*, c.code AS coupon_code
    FROM Orders o
    LEFT JOIN Promotions_and_Coupons c ON o.coupon_id=c.coupon_id
    WHERE o.user_id=$user_id
    ORDER BY o.order_date DESC
");
?>

<h2 class="mb-4">📦 My Orders</h2>

<?php 
display_flash_message(); 
?>

<?php if ($orders_res->num_rows === 0): ?>
    <div class="alert alert-info" role="alert">You haven't placed any orders yet.</div>
<?php else: ?>
    <div class="list-group">
        <?php while($order = $orders_res->fetch_assoc()): ?>
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center mb-2 shadow-sm">
                <div>
                    <h5 class="mb-1">Order #<?php echo $order['order_id']; ?></h5>
                    <small class="text-muted">Date: <?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></small>
                </div>
                <div class="text-end">
                    <?php 
                        $status = strtolower($order['status']);
                        $status_class = match($status) {
                            'processing', 'pending' => 'bg-warning text-dark',
                            'shipped', 'delivered' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    ?>
                    <span class="badge rounded-pill <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                    
                    <p class="mb-0 mt-1 small">
                        Discount: $<?php echo number_format($order['discount_applied'] ?? 0, 2); ?> | 
                        Coupon: <strong><?php echo $order['coupon_code'] ?? 'N/A'; ?></strong>
                    </p>
                    
                    <?php if($status == 'processing' || $status == 'pending'): ?>
                        <a href="orders.php?cancel=<?php echo $order['order_id']; ?>" 
                           class="btn btn-sm btn-danger mt-2"
                           onclick="return confirm('Are you sure you want to cancel Order #<?php echo $order['order_id']; ?>?')">
                            Cancel Order
                        </a>
                    <?php endif; ?>
                    
                    <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary mt-2">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>