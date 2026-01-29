<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT sc.cart_item_id, sc.quantity, pv.variant_id, pv.product_id, 
    p.name AS product_name, p.price AS product_price, p.image_url, 
    pv.stock_quantity, pv.is_available
    FROM Shopping_Cart sc
    LEFT JOIN Product_Variants pv ON sc.variant_id = pv.variant_id
    LEFT JOIN Products p ON pv.product_id = p.product_id
    WHERE sc.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<h2 class="mb-4">🛒 Your Shopping Cart</h2>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped shadow-sm align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Price</th>
                    <th scope="col" class="text-center">Quantity</th>
                    <th scope="col" class="text-end">Total</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grand_total = 0;
                while ($row = $result->fetch_assoc()):
                    $total = $row['product_price'] * $row['quantity'];
                    $grand_total += $total;
                    
                    $is_available = $row['is_available'] && $row['stock_quantity'] > 0;
                    $row_class = $is_available ? '' : 'table-danger';
                ?>
                <tr class="<?= $row_class ?>">
                    <td>
                        <a href="../product.php?id=<?php echo $row['product_id']; ?>" class="text-decoration-none fw-bold">
                            <?php echo htmlspecialchars($row['product_name']); ?>
                        </a>
                        <?php if (!$is_available): ?>
                            <span class="badge bg-danger ms-2">Unavailable/Out of Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>$<?php echo number_format($row['product_price'], 2); ?></td>
                    <td class="text-center">
                        <form method="POST" action="update_cart.php" class="d-flex justify-content-center align-items-center">
                            <input type="hidden" name="cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                            <input type="number" 
                                   name="quantity" 
                                   value="<?php echo $row['quantity']; ?>" 
                                   min="1" 
                                   max="<?php echo $row['stock_quantity'] ?: 1; ?>"
                                   required
                                   class="form-control form-control-sm text-center me-2" 
                                   style="width: 80px;"
                                   <?= $is_available ? '' : 'disabled' ?>>
                            <button type="submit" class="btn btn-sm btn-outline-secondary" <?= $is_available ? '' : 'disabled' ?>>
                                Update
                            </button>
                        </form>
                    </td>
                    <td class="text-end fw-bold">$<?php echo number_format($total, 2); ?></td>
                    <td>
                        <a href="remove_cart.php?cart_item_id=<?php echo $row['cart_item_id']; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Remove this item from cart?');">
                           Remove
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <td colspan="3" class="text-end fs-5 fw-bold">Grand Total:</td>
                    <td class="text-end fs-5 fw-bold text-danger">$<?php echo number_format($grand_total, 2); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="d-flex justify-content-end mt-4">
        <a href="../customer/products.php" class="btn btn-outline-primary me-3">Continue Shopping</a>
        <?php if ($grand_total > 0): ?>
            <a href="../customer/checkout.php" class="btn btn-success btn-lg">
                Proceed to Checkout
            </a>
        <?php else: ?>
             <button disabled class="btn btn-secondary btn-lg">Checkout Unavailable</button>
        <?php endif; ?>
    </div>

<?php else: ?>
    <div class="alert alert-warning text-center p-4" role="alert">
        Your shopping cart is currently empty.
        <div class="mt-3">
            <a href="../customer/products.php" class="btn btn-primary">Start Shopping Now</a>
        </div>
    </div>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>