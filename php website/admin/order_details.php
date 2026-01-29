<?php
// admin/order_details.php
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";

// Admin access control
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied.", "error");
    header("Location: ../auth/login.php");
    exit;
}

$order_id = intval($_GET['id'] ?? 0);

if($order_id === 0){
    set_flash_message("Invalid order ID.", "error");
    header("Location: orders.php");
    exit;
}

// Fetch order details
$order_query = $conn->prepare("
    SELECT o.*, u.full_name, u.email, u.phone_number,
           a.street_address, a.city, a.zip_code, a.label as address_label,
           pc.code as coupon_code
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN user_addresses a ON o.address_id = a.address_id
    LEFT JOIN promotions_and_coupons pc ON o.coupon_id = pc.coupon_id
    WHERE o.order_id = ?
");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$order_result = $order_query->get_result();

if($order_result->num_rows === 0){
    set_flash_message("Order not found.", "error");
    header("Location: orders.php");
    exit;
}
$order = $order_result->fetch_assoc();

// Fetch order items
$items_query = $conn->prepare("
    SELECT oi.*, p.name as product_name, pv.variant_id,
           va.attribute_name, va.attribute_value
    FROM order_items oi
    JOIN product_variants pv ON oi.variant_id = pv.variant_id
    JOIN products p ON pv.product_id = p.product_id
    LEFT JOIN variant_attributes va ON pv.variant_id = va.variant_id
    WHERE oi.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items_result = $items_query->get_result();

include "../includes/header.php"; 
?>

<h2>Order Details #<?php echo $order['order_id']; ?></h2>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Order Information
            </div>
            <div class="card-body">
                <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
                <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-<?php 
                    $status = strtolower($order['status']);
                    if ($status == 'delivered') echo 'success'; 
                    elseif ($status == 'pending' || $status == 'processing') echo 'warning'; 
                    elseif ($status == 'shipped') echo 'info';
                    else echo 'danger'; 
                ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Customer Information
            </div>
            <div class="card-body">
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone_number']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        Shipping Address
    </div>
    <div class="card-body">
        <?php if (!empty($order['street_address'])): ?>
            <p><strong>Label:</strong> <?php echo htmlspecialchars($order['address_label'] ?? 'Primary'); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['street_address']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($order['city']); ?>, ZIP: <?php echo htmlspecialchars($order['zip_code']); ?></p>
        <?php else: ?>
            <p class="text-danger">Shipping address not available</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        Order Items
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                if ($items_result->num_rows > 0): 
                    while($item = $items_result->fetch_assoc()): 
                        $item_total = $item['quantity'] * $item['unit_price'];
                        $subtotal += $item_total;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>
                        <?php echo "Variant #" . $item['variant_id']; ?>
                        <?php if (!empty($item['attribute_name'])): ?>
                            <br><small><?php echo htmlspecialchars($item['attribute_name']) . ": " . htmlspecialchars($item['attribute_value']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>$<?php echo number_format($item_total, 2); ?></td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No items found for this order</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        Order Summary
    </div>
    <div class="card-body">
        <table class="table">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-end">$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr>
                <td><strong>Shipping Cost:</strong></td>
                <td class="text-end">$<?php echo number_format($order['shipping_cost'], 2); ?></td>
            </tr>
            <?php if ($order['discount_applied'] > 0): ?>
            <tr>
                <td><strong>Discount (<?php echo htmlspecialchars($order['coupon_code'] ?? 'Coupon'); ?>):</strong></td>
                <td class="text-end text-danger">-$<?php echo number_format($order['discount_applied'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="table-active">
                <td><strong>Final Total:</strong></td>
                <td class="text-end"><strong>$<?php echo number_format($order['final_total'], 2); ?></strong></td>
            </tr>
        </table>
    </div>
</div>

<div class="mt-4">
    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
    <a href="order_edit_status.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary">Edit Status</a>
</div>

<?php include "../includes/footer.php"; ?>