<?php
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";

// Admin access control
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied.", "error");
    header('Location: ../auth/login.php'); 
    exit; 
}

$err = $msg = "";
$edit_coupon = null;

// Handle add/edit coupon
if(isset($_POST['add_coupon']) || isset($_POST['edit_coupon'])){
    $code = trim($_POST['code'] ?? "");
    $discount = floatval($_POST['discount_value'] ?? 0);
    $valid_from = $_POST['valid_from'] ?? null;
    $expires_at = $_POST['expires_at'] ?? null;
    $min_order = floatval($_POST['min_order_total'] ?? 0);
    $coupon_id = intval($_POST['coupon_id'] ?? 0);

    // FIX: Discount is monetary amount, not percentage. Remove percentage validation.
    if($code == "" || $discount < 0){
        set_flash_message("Coupon code is required, and discount must be a positive value.", "error");
    } else {
        if(isset($_POST['add_coupon'])) {
            $sql = "INSERT INTO promotions_and_coupons (code, discount_value, valid_from, expires_at, min_order_total) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdssd", $code, $discount, $valid_from, $expires_at, $min_order);
        } else {
            $sql = "UPDATE promotions_and_coupons SET code=?, discount_value=?, valid_from=?, expires_at=?, min_order_total=? WHERE coupon_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdssdi", $code, $discount, $valid_from, $expires_at, $min_order, $coupon_id);
        }
        
        if($stmt->execute()){
            $msg = isset($_POST['add_coupon']) ? "Coupon added successfully." : "Coupon updated successfully.";
            set_flash_message($msg, "success");
            header("Location: coupons.php");
            exit;
        } else {
            $err = "Error saving coupon: " . $stmt->error;
            set_flash_message($err, "error");
        }
        $stmt->close();
    }
}

// Handle delete coupon
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->begin_transaction();
    
    try {
        // 1. Unlink coupon from orders (set coupon_id to NULL in orders table)
        $stmt_update = $conn->prepare("UPDATE orders SET coupon_id = NULL WHERE coupon_id = ?");
        $stmt_update->bind_param("i", $id);
        $stmt_update->execute();
        $stmt_update->close();
        
        // 2. Delete the coupon itself
        $stmt_delete = $conn->prepare("DELETE FROM promotions_and_coupons WHERE coupon_id = ?");
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();
        
        if ($stmt_delete->affected_rows > 0) {
            $conn->commit();
            set_flash_message("Coupon ID #{$id} deleted successfully. Orders using it are unlinked.", "success");
        } else {
            $conn->rollback();
            set_flash_message("Coupon not found or could not be deleted.", "error");
        }
        $stmt_delete->close();
        
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        set_flash_message("Error deleting coupon: Dependent records still exist. MySQL Error: " . htmlspecialchars($e->getMessage()), "error");
    }
    header("Location: coupons.php");
    exit;
}

// Fetch coupon for editing
if(isset($_GET['edit'])){
    $coupon_id_to_edit = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM promotions_and_coupons WHERE coupon_id=?");
    $stmt->bind_param("i", $coupon_id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows === 1){
        $edit_coupon = $result->fetch_assoc();
    } else {
        set_flash_message("Coupon not found.", "error");
        header("Location: coupons.php");
        exit;
    }
    $stmt->close();
}

// Fetch all coupons
$coupons = $conn->query("SELECT * FROM promotions_and_coupons ORDER BY coupon_id DESC");

include "../includes/header.php";
?>

<h2>Manage Coupons</h2>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
<?php endif; ?>

<?php if($edit_coupon): ?>
<div class="card mb-4 p-3 bg-light">
    <h3 class="card-title text-primary">Edit Coupon: <?php echo htmlspecialchars($edit_coupon['code']); ?></h3>
    <form method="POST">
        <input type="hidden" name="coupon_id" value="<?php echo $edit_coupon['coupon_id']; ?>">
        <input type="hidden" name="edit_coupon" value="1">

        <div class="mb-3"><input type="text" name="code" class="form-control" placeholder="Coupon Code" value="<?php echo htmlspecialchars($edit_coupon['code']); ?>" required></div>
        <div class="mb-3"><input type="number" step="0.01" name="discount_value" class="form-control" placeholder="Discount Amount (e.g., 15.00 for $15 off)" value="<?php echo $edit_coupon['discount_value']; ?>" required min="0.01"></div>
        <div class="mb-3"><input type="date" name="valid_from" class="form-control" placeholder="Valid From" value="<?php echo $edit_coupon['valid_from']; ?>"></div>
        <div class="mb-3"><input type="date" name="expires_at" class="form-control" placeholder="Expires At" value="<?php echo $edit_coupon['expires_at']; ?>"></div>
        <div class="mb-3"><input type="number" step="0.01" name="min_order_total" class="form-control" placeholder="Minimum Order Total" value="<?php echo $edit_coupon['min_order_total']; ?>"></div>
        
        <button type="submit" class="btn btn-success">Update Coupon</button>
        <a href="coupons.php" class="btn btn-secondary">Cancel Edit</a>
    </form>
</div>
<?php else: ?>
<div class="card mb-4 p-3 bg-light">
    <h3 class="card-title">Add New Coupon</h3>
    <form method="POST">
        <input type="hidden" name="add_coupon" value="1">
        <div class="mb-3"><input type="text" name="code" class="form-control" placeholder="Coupon Code" required></div>
        <div class="mb-3"><input type="number" step="0.01" name="discount_value" class="form-control" placeholder="Discount Amount (e.g., 15.00 for $15 off)" required min="0.01"></div>
        <div class="mb-3"><input type="date" name="valid_from" class="form-control" placeholder="Valid From"></div>
        <div class="mb-3"><input type="date" name="expires_at" class="form-control" placeholder="Expires At"></div>
        <div class="mb-3"><input type="number" step="0.01" name="min_order_total" class="form-control" placeholder="Minimum Order Total" value="0"></div>
        <button type="submit" class="btn btn-primary">Add Coupon</button>
    </form>
</div>
<?php endif; ?>

<h3>Current Coupons</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Discount Amount</th>
            <th>Valid From</th>
            <th>Expires At</th>
            <th>Min Order Total</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $coupons->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['coupon_id']; ?></td>
            <td><?php echo htmlspecialchars($row['code']); ?></td>
            <td>$<?php echo number_format($row['discount_value'], 2); ?></td>
            <td><?php echo $row['valid_from'] ?? 'N/A'; ?></td>
            <td><?php echo $row['expires_at'] ?? 'N/A'; ?></td>
            <td>$<?php echo number_format($row['min_order_total'], 2); ?></td>
            <td>
                <a href="coupons.php?edit=<?php echo $row['coupon_id']; ?>" class="btn btn-sm btn-info">Edit</a>
                <a href="coupons.php?delete=<?php echo $row['coupon_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete coupon <?php echo htmlspecialchars($row['code']); ?>? If this coupon is used in an existing order, the deletion will fail.')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include "../includes/footer.php"; ?>