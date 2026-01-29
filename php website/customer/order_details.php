<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

$user_id = $_SESSION['user_id'] ?? 0;
$order_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    header("Location: ../auth/login.php");
    exit;
}

if ($order_id === 0) {
    echo "<div class='alert alert-danger'>Invalid order ID provided.</div>";
    include "../includes/footer.php";
    exit;
}

$order_query = $conn->prepare("
    SELECT 
        o.*,
        c.code AS coupon_code, 
        u.full_name AS customer_name,
        a.label, a.street_address, a.city, a.zip_code
    FROM Orders o
    LEFT JOIN Promotions_and_Coupons c ON o.coupon_id=c.coupon_id
    LEFT JOIN Users u ON o.user_id = u.user_id
    LEFT JOIN User_Addresses a ON o.address_id = a.address_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order_res = $order_query->get_result();

if($order_res->num_rows === 0){
    echo "<div class='alert alert-danger'>Order not found or access denied.</div>";
    include "../includes/footer.php";
    exit;
}

$order = $order_res->fetch_assoc();

$items_query = $conn->prepare("
    SELECT 
        oi.*, 
        p.name AS product_name 
    FROM Order_Items oi
    LEFT JOIN Product_Variants pv ON oi.variant_id = pv.variant_id
    LEFT JOIN Products p ON pv.product_id = p.product_id
    WHERE oi.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items_res = $items_query->get_result();
?>

<h2 class="mb-4">Details for Order #<?php echo $order['order_id']; ?></h2>
<div class="row">
    <div class="col-md-5">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">Order Summary</div>
            <div class="card-body">
                <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-primary"><?php echo ucfirst($order['status']); ?></span></p>
                <p><strong>Shipping Address:</strong> 
                    <?php 
                    if($order['label']) {
                        echo htmlspecialchars($order['label']) . ': ' . 
                             htmlspecialchars($order['street_address']) . ', ' . 
                             htmlspecialchars($order['city']) . ', ' . 
                             htmlspecialchars($order['zip_code']);
                    } else {
                        echo 'Address not available';
                    }
                    ?>
                </p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></p>
                <hr>
                
                <p><strong>Subtotal:</strong> $<?php 
                    $subtotal_calc = ($order['final_total'] ?? 0) - ($order['shipping_cost'] ?? 0) + ($order['discount_applied'] ?? 0);
                    echo number_format($subtotal_calc, 2); 
                ?></p>
                <p><strong>Discount (<?php echo $order['coupon_code'] ?? 'N/A'; ?>):</strong> $<?php echo number_format($order['discount_applied'] ?? 0, 2); ?></p>
                <p><strong>Shipping Cost:</strong> $<?php echo number_format($order['shipping_cost'] ?? 0, 2); ?></p>
                <h5 class="mt-3"><strong>TOTAL AMOUNT:</strong> $<?php echo number_format($order['final_total'] ?? 0, 2); ?></h5>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">Order Items</div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while($item = $items_res->fetch_assoc()): 
                            $item_total = $item['quantity'] * $item['unit_price'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td>$<?php echo number_format($item_total, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>