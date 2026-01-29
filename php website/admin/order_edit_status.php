<?php
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php"; 

$err = $msg = "";

// Admin access control
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied.", "error");
    header("Location: ../auth/login.php");
    exit;
}

// Valid order statuses
$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

// Get order ID
$order_id = intval($_GET['id'] ?? 0);

if($order_id === 0){
    set_flash_message("Invalid order ID.", "error");
    header("Location: orders.php");
    exit;
}

// Handle status update
if(isset($_POST['update_status'])){
    $new_status = $conn->real_escape_string($_POST['status']);
    
    // Validate status
    if (!in_array($new_status, $statuses)) {
        set_flash_message("Invalid status value submitted.", "error");
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        
        if($stmt->execute()){
            set_flash_message("Order #$order_id status updated to " . ucfirst($new_status) . ".", "success");
            header("Location: orders.php");
            exit;
        } else {
            $err = "Error updating status: " . $stmt->error;
            set_flash_message($err, "error");
        }
        $stmt->close();
    }
}

// Fetch order details
$order_query = $conn->prepare("
    SELECT o.order_id, o.order_date, o.status, u.full_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$result = $order_query->get_result();

if($result->num_rows === 0){
    set_flash_message("Order not found.", "error");
    header("Location: orders.php");
    exit;
}
$order = $result->fetch_assoc();

include "../includes/header.php"; 
?>

<h2>Edit Order Status #<?php echo $order['order_id']; ?></h2>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
<?php endif; ?>

<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        Order Information
    </div>
    <div class="card-body">
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['full_name']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
        <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></p>
        <p><strong>Current Status:</strong> <span class="badge bg-warning text-dark"><?php echo ucfirst($order['status']); ?></span></p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Update Status
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="status" class="form-label">New Order Status</label>
                <select name="status" id="status" class="form-select">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status; ?>" 
                            <?php if ($status === $order['status']) echo 'selected'; ?>>
                            <?php echo ucfirst($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn btn-success">Save Status</button>
            <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>