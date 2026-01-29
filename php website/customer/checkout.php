<?php
include_once __DIR__ . '/../includes/functions.php';

if (!defined('DB_CONFIG_PATH')) {
    define('DB_CONFIG_PATH', __DIR__ . '/../config/db.php');
}
require_once DB_CONFIG_PATH;

if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    set_flash_message("Please log in to proceed to checkout.", "error");
    header("Location: ../auth/login.php");
    exit();
}

$stmt = $conn->prepare("SELECT address_id, label, street_address, city, zip_code FROM User_Addresses WHERE user_id = ? ORDER BY address_id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses_res = $stmt->get_result();

if ($addresses_res->num_rows == 0) {
    set_flash_message("Please add a shipping address before checking out.", "warning");
    header("Location: manage_addresses.php");
    exit;
}
$addresses = $addresses_res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("
    SELECT sc.quantity, p.name, p.price, pv.stock_quantity
    FROM Shopping_Cart sc
    JOIN Product_Variants pv ON sc.variant_id = pv.variant_id
    JOIN Products p ON pv.product_id = p.product_id
    WHERE sc.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_res = $stmt->get_result();
$stmt->close();

if ($cart_res->num_rows === 0) {
    set_flash_message("Your cart is empty.", "error");
    header("Location: products.php");
    exit();
}

$cart_items = $cart_res->fetch_all(MYSQLI_ASSOC);
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$coupons_res = $conn->query("
    SELECT coupon_id, code, discount_value, min_order_total
    FROM Promotions_and_Coupons
    WHERE expires_at >= CURDATE()
    ORDER BY min_order_total
");
$coupons = $coupons_res->fetch_all(MYSQLI_ASSOC);
?>

<h2 class="mb-4">🛒 Checkout</h2>

<div class="row">
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm p-4">
            <h4 class="card-title mb-3">1. Select Shipping Address</h4>
            
            <form method="POST" action="payment.php">
                <div class="list-group mb-4">
                    <?php foreach ($addresses as $index => $addr): ?>
                        <label class="list-group-item d-flex align-items-center">
                            <input class="form-check-input me-3" 
                                   type="radio" 
                                   name="address_id" 
                                   value="<?= $addr['address_id'] ?>" 
                                   required 
                                   <?= ($index === 0 ? 'checked' : '') ?>>
                            
                            <div>
                                <strong class="text-primary"><?= htmlspecialchars($addr['label']) ?></strong>
                                <p class="mb-0 small text-muted">
                                    <?= htmlspecialchars($addr['street_address']) ?>, 
                                    <?= htmlspecialchars($addr['city']) ?>, ZIP: 
                                    <?= htmlspecialchars($addr['zip_code']) ?>
                                </p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <a href="manage_addresses.php" class="btn btn-outline-secondary btn-sm">
                    Manage Addresses
                </a>

                <hr class="mt-4">

                <h4 class="card-title mb-3">2. Apply Coupon</h4>
                <div class="mb-3">
                    <select class="form-select" name="coupon_id">
                        <option value="0">No Coupon Applied</option>
                        <?php foreach ($coupons as $coupon): ?>
                            <option value="<?= $coupon['coupon_id'] ?>">
                                <?= htmlspecialchars($coupon['code']) ?> (<?= $coupon['discount_value'] ?> OFF, Min: $<?= number_format($coupon['min_order_total'], 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <input type="hidden" name="total" value="<?= $total ?>">
                
                <hr class="mt-4">
                
                <button type="submit" class="btn btn-danger btn-lg w-100">
                    Proceed to Payment ($<?= number_format($total, 2) ?>)
                </button>
            </form>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card shadow-sm p-4 bg-light">
            <h4 class="card-title mb-3">3. Order Summary</h4>
            <table class="table table-sm mb-4">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Item</th>
                        <th scope="col" class="text-center">Qty</th>
                        <th scope="col" class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-end">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <h5 class="mb-0">Grand Total:</h5>
                <h5 class="text-danger mb-0">$<?= number_format($total, 2) ?></h5>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>