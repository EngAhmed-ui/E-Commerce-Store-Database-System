<?php
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php"; // Include functions for set_flash_message

// Ensure $conn is available (mysqli object)
global $conn; 

$err = $msg = "";
$variants = null; // Initialize the variable to null
$product_id_filter = intval($_GET['product_id'] ?? 0);

// Admin access control
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied.", "error");
    header("Location: ../auth/login.php");
    exit;
}

// Fetch all products for dropdown
$products = $conn->query("SELECT product_id, name FROM products ORDER BY name ASC");

// Fetch product name for display if filter is active
$product_name = '';
if ($product_id_filter > 0) {
    $stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product_name = $result->fetch_assoc()['name'];
    }
    $stmt->close();
}


// Add variant (Using prepared statements now)
if(isset($_POST['add_variant'])){
    $product_id = intval($_POST['product_id']);
    $stock = intval($_POST['stock_quantity']);
    $available = isset($_POST['is_available']) ? 1 : 0;

    if($product_id == 0){
        set_flash_message("Select a product.", "error");
    } else {
        $sql = "INSERT INTO product_variants (product_id, stock_quantity, is_available) 
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $product_id, $stock, $available);

        if($stmt->execute()){
            set_flash_message("Variant added successfully.", "success");
            header("Location: variants.php" . ($product_id ? "?product_id={$product_id}" : ""));
            exit;
        } else {
            set_flash_message("Error adding variant: " . $stmt->error, "error");
        }
        $stmt->close();
    }
}

// --- CRITICAL FIX: HANDLE TRANSACTIONAL DELETE ---
if(isset($_GET['delete'])){
    $variant_id = intval($_GET['delete']);

    // Start a transaction for safe, multi-query operation
    $conn->begin_transaction();

    try {
        // Deletion Order: Deepest dependencies first.

        // 1. Delete associated records from 'order_items' (Grandchild Table)
        $stmt_order_items = $conn->prepare("DELETE FROM order_items WHERE variant_id = ?");
        $stmt_order_items->bind_param("i", $variant_id);
        $stmt_order_items->execute();
        $stmt_order_items->close();
        
        // 2. Delete associated records from 'shopping_cart' (Grandchild Table)
        $stmt_cart = $conn->prepare("DELETE FROM shopping_cart WHERE variant_id = ?");
        $stmt_cart->bind_param("i", $variant_id);
        $stmt_cart->execute();
        $stmt_cart->close();

        // 3. Delete all attributes associated with this variant.
        $stmt_attr = $conn->prepare("DELETE FROM variant_attributes WHERE variant_id = ?");
        $stmt_attr->bind_param("i", $variant_id);
        $stmt_attr->execute();
        $stmt_attr->close();

        // 4. Now delete the parent product variant record.
        $stmt_variant = $conn->prepare("DELETE FROM product_variants WHERE variant_id = ?");
        $stmt_variant->bind_param("i", $variant_id);
        
        if ($stmt_variant->execute()) {
            if ($conn->affected_rows > 0) {
                $conn->commit();
                set_flash_message("Variant ID #{$variant_id} and all related data deleted successfully.", "success");
            } else {
                $conn->rollback();
                set_flash_message("Error: Variant ID #{$variant_id} not found.", "error");
            }
        } else {
            // Throw exception for any other failure
            throw new Exception("Error deleting variant record: " . $stmt_variant->error);
        }
        $stmt_variant->close();

    } catch (Exception $e) {
        $conn->rollback();
        set_flash_message("Transaction failed: " . $e->getMessage(), "error");
    }
    header("Location: variants.php" . ($product_id_filter ? "?product_id={$product_id_filter}" : ""));
    exit;
}
// --- END HANDLE TRANSACTIONAL DELETE ---


// Fetch all variants with product names
$sql = "
    SELECT v.*, p.name AS product_name 
    FROM product_variants v
    JOIN products p ON v.product_id = p.product_id
";
$where_clause = "";
if ($product_id_filter > 0) {
    $where_clause = " WHERE v.product_id = " . $product_id_filter; // Filter by product
}
$sql .= $where_clause . " ORDER BY v.variant_id DESC";


$variants = $conn->query($sql);

if(!$variants){
    set_flash_message("Query failed: " . $conn->error, "error");
}

include "../includes/header.php"; 
?>

<h2>📦 Manage Product Variants <?php if($product_name): ?><small class="text-muted">(Product: <?php echo htmlspecialchars($product_name); ?>)</small><?php endif; ?></h2>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
<?php endif; ?>

<div class="card p-3 mb-4 shadow-sm">
    <h4 class="card-title">Add New Variant</h4>
    <form method="POST" class="row g-3">
        <div class="col-md-5">
            <label for="product_id" class="form-label">Product</label>
            <select name="product_id" id="product_id" class="form-select" required>
                <option value="">Select Product</option>
                <?php while($p = $products->fetch_assoc()): ?>
                    <option value="<?php echo $p['product_id']; ?>" 
                        <?php if ($p['product_id'] == $product_id_filter) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($p['name']); ?> (ID: <?php echo $p['product_id']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="col-md-3">
            <label for="stock_quantity" class="form-label">Stock Quantity</label>
            <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" value="1" min="0" required>
        </div>
        
        <div class="col-md-2 d-flex align-items-end mb-3">
            <div class="form-check">
                <input type="checkbox" name="is_available" value="1" class="form-check-input" id="isAvailableCheck" checked>
                <label class="form-check-label" for="isAvailableCheck">Available</label>
            </div>
        </div>
        
        <div class="col-md-2 d-flex align-items-end">
             <button type="submit" name="add_variant" class="btn btn-primary w-100">Add Variant</button>
        </div>
    </form>
</div>

<h3>Variant List</h3>
<p class="text-muted">Use the "Manage Attributes" button below to add size, color, or other variant-specific data.</p>

<table class="table table-bordered table-striped align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Stock Quantity</th>
            <th>Available</th>
            <th style="width: 250px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // FIX: Check if $variants is a valid result object before attempting to fetch_assoc()
        if ($variants && $variants->num_rows > 0): 
            while($v = $variants->fetch_assoc()): 
        ?>
        <tr>
            <td><?php echo $v['variant_id']; ?></td>
            <td>
                <?php echo htmlspecialchars($v['product_name']); ?>
                <a href="?product_id=<?php echo $v['product_id']; ?>" class="badge bg-secondary ms-1">Filter</a>
            </td>
            <td><?php echo $v['stock_quantity']; ?></td>
            <td>
                <span class="badge bg-<?php echo $v['is_available'] ? "success" : "danger"; ?>">
                    <?php echo $v['is_available'] ? "Yes" : "No"; ?>
                </span>
            </td>
            <td>
                <a href="variant_attributes.php?variant_id=<?php echo $v['variant_id']; ?>" class="btn btn-sm btn-secondary">Manage Attributes</a>
                <a href="variants.php?delete=<?php echo $v['variant_id']; ?>" 
                   onclick="return confirm('WARNING: Deleting this variant will also delete ALL related attributes, cart items, and order items. Proceed?')" 
                   class="btn btn-sm btn-danger">Delete</a>
            </td>
        </tr>
        <?php 
            endwhile; 
        else:
        ?>
        <tr>
            <td colspan="5" class="text-center">No product variants found or a database error occurred.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include "../includes/footer.php"; ?>