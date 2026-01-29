<?php
include_once __DIR__ . '/../includes/functions.php';
if (!defined('DB_CONFIG_PATH')) {
    define('DB_CONFIG_PATH', __DIR__ . '/../config/db.php');
}
require_once DB_CONFIG_PATH;

if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

global $conn;

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    set_flash_message("Please log in to complete your order.", "error");
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['address_id']) || !isset($_POST['total'])) {
    set_flash_message("Invalid access to payment page.", "error");
    header("Location: checkout.php");
    exit();
}

$address_id = (int)$_POST['address_id'];
$coupon_id = (int)($_POST['coupon_id'] ?? 0);
$base_total = floatval($_POST['total']);

$discount_applied = 0.00;
$shipping_cost = 5.00;
$final_total = $base_total;
$coupon_code = 'N/A';
$coupon_to_insert = NULL;

if ($coupon_id > 0) {
    try {
        $stmt = $conn->prepare("
            SELECT code, discount_value, min_order_total
            FROM Promotions_and_Coupons
            WHERE coupon_id = ? AND expires_at >= CURDATE()
        ");
        $stmt->bind_param("i", $coupon_id);
        $stmt->execute();
        $coupon_res = $stmt->get_result();
        
        if ($coupon_res->num_rows > 0) {
            $coupon = $coupon_res->fetch_assoc();
            
            if ($base_total >= $coupon['min_order_total']) {
                $coupon_code = $coupon['code'];
                $discount_applied = $coupon['discount_value'];
                $final_total = $base_total - $discount_applied;
                $coupon_to_insert = $coupon_id;
            } else {
                $coupon_id = 0;
                set_flash_message("Coupon requires a minimum order total of $" . number_format($coupon['min_order_total'], 2) . ". Coupon not applied.", "warning");
            }
        } else {
            $coupon_id = 0;
            set_flash_message("Selected coupon is invalid or expired.", "warning");
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Coupon lookup error: " . $e->getMessage());
    }
}

$final_total = $final_total + $shipping_cost;

if (isset($_POST['process_payment'])) {
    $stmt = $conn->prepare("
        SELECT sc.quantity, pv.variant_id, p.price
        FROM Shopping_Cart sc
        JOIN Product_Variants pv ON sc.variant_id = pv.variant_id
        JOIN Products p ON pv.product_id = p.product_id
        WHERE sc.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    $order_items = $cart_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($order_items)) {
        set_flash_message("Your cart is empty.", "error");
        header("Location: products.php");
        exit();
    }

    $payment_successful = true;
    $payment_method = 'Credit Card';

    if ($payment_successful) {
        $conn->begin_transaction();
        
        try {
            $order_status = 'processing';
            $stmt = $conn->prepare("
                INSERT INTO Orders 
                (user_id, order_date, status, coupon_id, discount_applied, address_id, final_total, shipping_cost, payment_method) 
                VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("isidddds", 
                $user_id, 
                $order_status, 
                $coupon_to_insert, 
                $discount_applied, 
                $address_id,
                $final_total,
                $shipping_cost,
                $payment_method
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Order insertion failed: " . $stmt->error);
            }
            
            $order_id = $conn->insert_id;
            $stmt->close();

            $item_stmt = $conn->prepare("INSERT INTO Order_Items (order_id, quantity, unit_price, variant_id) VALUES (?, ?, ?, ?)");
            $stock_stmt = $conn->prepare("UPDATE Product_Variants SET stock_quantity = stock_quantity - ? WHERE variant_id = ? AND stock_quantity >= ?");

            foreach ($order_items as $it) {
                $variant = $it["variant_id"];
                $qty = $it["quantity"];
                $price = $it["price"];

                $item_stmt->bind_param("iidi", $order_id, $qty, $price, $variant);
                $item_stmt->execute();

                $stock_stmt->bind_param("iii", $qty, $variant, $qty);
                $stock_stmt->execute();

                if ($stock_stmt->affected_rows === 0) {
                    throw new Exception("Stock quantity insufficient for variant ID: $variant");
                }
            }
            $item_stmt->close();
            $stock_stmt->close();

            $stmt = $conn->prepare("DELETE FROM Shopping_Cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            
            $payment_stmt = $conn->prepare("INSERT INTO Payments (order_id, payment_date, amount) VALUES (?, NOW(), ?)");
            $payment_stmt->bind_param("id", $order_id, $final_total);
            $payment_stmt->execute();
            $payment_stmt->close();
            
            set_flash_message("Your order #$order_id has been placed successfully! Thank you.", "success");
            header("Location: order_success.php?order_id=" . $order_id);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            set_flash_message("Payment successful but order failed: " . $e->getMessage(), "error");
            header("Location: checkout.php");
            exit();
        }
    } else {
        set_flash_message("Payment failed. Please try again or use a different method.", "error");
        header("Location: checkout.php");
        exit();
    }
}
?>

<h2 class="mb-4">💳 Payment Gateway</h2>

<div class="card shadow-lg mx-auto" style="max-width: 400px;">
    <div class="card-body p-4 text-center">
        <h3 class="card-title mb-3">Finalizing Order</h3>
        
        <?php if ($discount_applied > 0): ?>
            <p class="fs-6 text-success mb-2">Coupon Applied: <strong><?= $coupon_code ?></strong></p>
            <p class="fs-6 text-muted mb-1">Subtotal (before shipping): $<?= number_format($base_total, 2) ?></p>
            <p class="fs-5 text-danger border-bottom pb-2 mb-2">Discount: -$<?= number_format($discount_applied, 2) ?></p>
        <?php else: ?>
             <p class="fs-6 text-success mb-2">No Coupon Applied</p>
             <p class="fs-6 text-muted mb-1">Subtotal (before shipping): $<?= number_format($base_total, 2) ?></p>
        <?php endif; ?>

        <p class="fs-6 text-muted mb-1">Shipping Cost: +$<?= number_format($shipping_cost, 2) ?></p>
        
        <p class="fs-4 text-primary border-top pt-2 mt-2">Total Amount Due: <strong>$<?= number_format($final_total, 2) ?></strong></p>
        
        <div class="alert alert-info py-2" role="alert">Simulating external payment gateway...</div>
        
        <form method="POST">
            <input type="hidden" name="address_id" value="<?= $address_id ?>">
            <input type="hidden" name="total" value="<?= $base_total ?>">
            <input type="hidden" name="coupon_id" value="<?= $coupon_id ?>">
            <input type="hidden" name="process_payment" value="1">
            
            <div class="mb-3">
                <input type="text" 
                       class="form-control form-control-lg" 
                       placeholder="Card Number (4444 xxxx xxxx xxxx)" 
                       required
                       pattern="\d{16,19}" 
                       maxlength="19"      
                       title="Enter a valid credit card number (16-19 digits).">
            </div>
            <div class="row mb-4">
                <div class="col-6">
                    <input type="text" 
                           class="form-control" 
                           placeholder="MM/YY" 
                           required
                           pattern="(0[1-9]|1[0-2])\/\d{2}" 
                           title="Enter month/year in MM/YY format.">
                </div>
                <div class="col-6">
                    <input type="text" 
                           class="form-control" 
                           placeholder="CVC" 
                           required
                           pattern="\d{3,4}" 
                           maxlength="4" 
                           title="Enter 3 or 4 digit CVC code.">
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100">
                Pay $<?= number_format($final_total, 2) ?>
            </button>
        </form>
        
        <a href="checkout.php" class="btn btn-sm btn-outline-secondary mt-3">Back to Checkout</a>
    </div>
</div>

<?php include "../includes/footer.php"; ?>