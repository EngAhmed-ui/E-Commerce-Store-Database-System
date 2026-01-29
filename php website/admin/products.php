<?php
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";

global $conn; 

$err = $msg = "";
$edit_product = null;
$product_id_to_edit = intval($_GET['edit'] ?? 0);

// Admin access control
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied.", "error");
    header("Location: ../auth/login.php");
    exit;
}

// Fetch categories and status options
$categories_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$statuses = ['active', 'inactive', 'archived'];

// Fetch product for editing if requested
if ($product_id_to_edit > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $edit_product = $result->fetch_assoc();
    } else {
        $product_id_to_edit = 0;
        set_flash_message("Product not found for editing.", "error");
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle product add/edit
    if (isset($_POST['add_product']) || isset($_POST['edit_product'])) {
        $name = trim($_POST['name'] ?? "");
        $description = trim($_POST['description'] ?? "");
        $price = floatval($_POST['price'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $status = $_POST['status'] ?? 'inactive';
        $image_url = trim($_POST['image_url'] ?? "");
        
        // Get variant data
        $variant_stock = intval($_POST['variant_stock'] ?? 0);
        $variant_available = isset($_POST['variant_available']) ? 1 : 0;

        if(empty($name) || $price <= 0 || $category_id === 0){
            set_flash_message("Name, price, and category are required, and price must be greater than zero.", "error");
        } else if (!in_array($status, $statuses)) {
             set_flash_message("Invalid product status provided.", "error");
        } else {
            $conn->begin_transaction();
            
            try {
                if (isset($_POST['add_product'])) {
                    // Add new product
                    $sql = "INSERT INTO products (name, description, price, category_id, status, image_url) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdiss", $name, $description, $price, $category_id, $status, $image_url);
                    
                    if ($stmt->execute()) {
                        $new_product_id = $conn->insert_id;
                        
                        // Add default variant for the new product
                        if ($new_product_id > 0) {
                            $variant_sql = "INSERT INTO product_variants (product_id, stock_quantity, is_available) 
                                           VALUES (?, ?, ?)";
                            $variant_stmt = $conn->prepare($variant_sql);
                            $variant_stmt->bind_param("iii", $new_product_id, $variant_stock, $variant_available);
                            
                            if ($variant_stmt->execute()) {
                                // Add default attribute for the variant
                                $attribute_sql = "INSERT INTO variant_attributes (variant_id, attribute_name, attribute_value) 
                                                VALUES (?, ?, ?)";
                                $attribute_stmt = $conn->prepare($attribute_sql);
                                $default_attribute_value = "Default";
                                $attribute_name = "Type";
                                $last_variant_id = $conn->insert_id;
                                $attribute_stmt->bind_param("iss", $last_variant_id, $attribute_name, $default_attribute_value);
                                $attribute_stmt->execute();
                                $attribute_stmt->close();
                            }
                            $variant_stmt->close();
                        }
                        
                        $conn->commit();
                        set_flash_message("Product added successfully with default variant!", "success");
                        header("Location: products.php");
                        exit;
                    } else {
                        throw new Exception("Error adding product: " . $stmt->error);
                    }
                    $stmt->close();
                } else if (isset($_POST['edit_product'])) {
                    $product_id = intval($_POST['product_id']);
                    // Update existing product
                    $sql = "UPDATE products SET name=?, description=?, price=?, category_id=?, status=?, image_url=? WHERE product_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdisi", $name, $description, $price, $category_id, $status, $image_url, $product_id);

                    if ($stmt->execute()) {
                        $conn->commit();
                        set_flash_message("Product ID #{$product_id} updated successfully.", "success");
                        header("Location: products.php");
                        exit;
                    } else {
                        throw new Exception("Error updating product: " . $stmt->error);
                    }
                    $stmt->close();
                }
            } catch (Exception $e) {
                $conn->rollback();
                set_flash_message($e->getMessage(), "error");
            }
        }
    }
}

// --- DELETION LOGIC (Transactional & Already Fixed) ---
if(isset($_GET['delete'])){
    $product_id = intval($_GET['delete']);

    $conn->begin_transaction();

    try {
        // 1. Find all variants related to this product
        $variants_res = $conn->query("SELECT variant_id FROM product_variants WHERE product_id = {$product_id}");
        $variant_ids = [];
        while ($row = $variants_res->fetch_assoc()) {
            $variant_ids[] = $row['variant_id'];
        }

        if (!empty($variant_ids)) {
            $placeholders = implode(',', array_fill(0, count($variant_ids), '?'));
            
            // 2. Delete all records from 'order_items'
            $stmt1 = $conn->prepare("DELETE FROM order_items WHERE variant_id IN ({$placeholders})");
            $types = str_repeat('i', count($variant_ids));
            $stmt1->bind_param($types, ...$variant_ids);
            $stmt1->execute();
            $stmt1->close();
            
            // 3. Delete all records from 'shopping_cart'
            $stmt2 = $conn->prepare("DELETE FROM shopping_cart WHERE variant_id IN ({$placeholders})");
            $stmt2->bind_param($types, ...$variant_ids);
            $stmt2->execute();
            $stmt2->close();

            // 4. Delete all attributes associated with these variants
            $stmt3 = $conn->prepare("DELETE FROM variant_attributes WHERE variant_id IN ({$placeholders})");
            $stmt3->bind_param($types, ...$variant_ids);
            $stmt3->execute();
            $stmt3->close();
            
            // 5. Delete all product variants
            $stmt4 = $conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
            $stmt4->bind_param("i", $product_id);
            $stmt4->execute();
            $stmt4->close();
        }

        // 6. Delete the parent product record
        $stmt_product = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt_product->bind_param("i", $product_id);
        
        if ($stmt_product->execute()) {
            if ($conn->affected_rows > 0) {
                $conn->commit();
                set_flash_message("Product ID #{$product_id} and all related data deleted successfully.", "success");
            } else {
                $conn->rollback();
                set_flash_message("Error: Product ID #{$product_id} not found.", "error");
            }
        } else {
             throw new Exception("Error deleting product record: " . $stmt_product->error);
        }
        $stmt_product->close();

    } catch (Exception $e) {
        $conn->rollback();
        set_flash_message("Transaction failed: " . $e->getMessage(), "error");
    }
    header("Location: products.php");
    exit;
}
// --- END DELETION LOGIC ---

// Fetch all products with category names
$products = $conn->query("
    SELECT p.*, c.name AS category_name,
           (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.product_id) as variant_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_id DESC
");

if(!$products){
    set_flash_message("Query failed: " . $conn->error, "error");
}

include "../includes/header.php"; 
?>

<h2>🛍️ Manage Products</h2>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
<?php endif; ?>

<div class="card p-3 mb-4 shadow-sm">
    <h4 class="card-title"><?php echo $edit_product ? 'Edit Product ID #' . $edit_product['product_id'] : 'Add New Product'; ?></h4>
    <form method="POST" class="row g-3">
        <?php if ($edit_product): ?>
            <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
        <?php endif; ?>
        
        <div class="col-md-6">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" name="name" id="name" class="form-control" required
                   value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>">
        </div>
        
        <div class="col-md-3">
            <label for="price" class="form-label">Price ($)</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0.01" required
                   value="<?php echo htmlspecialchars($edit_product['price'] ?? 0.01); ?>">
        </div>
        
        <div class="col-md-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?php echo $s; ?>" 
                        <?php if (($edit_product['status'] ?? 'inactive') === $s) echo 'selected'; ?>>
                        <?php echo ucfirst($s); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-12">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
        </div>

        <div class="col-md-6">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php 
                // Reset pointer for use in form
                if($categories_res) $categories_res->data_seek(0);
                while($c = $categories_res->fetch_assoc()): 
                ?>
                    <option value="<?php echo $c['category_id']; ?>" 
                        <?php if (($edit_product['category_id'] ?? 0) == $c['category_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="col-md-6">
            <label for="image_url" class="form-label">Image URL</label>
            <input type="url" name="image_url" id="image_url" class="form-control" placeholder="Optional: full link to image"
                   value="<?php echo htmlspecialchars($edit_product['image_url'] ?? ''); ?>">
        </div>
        
        <?php if (!$edit_product): ?>
        <!-- Variant fields for new products only -->
        <div class="col-12 mt-3">
            <h5 class="border-bottom pb-2">Default Variant Settings</h5>
        </div>
        
        <div class="col-md-4">
            <label for="variant_stock" class="form-label">Initial Stock Quantity</label>
            <input type="number" name="variant_stock" id="variant_stock" class="form-control" min="0" value="10" required>
            <small class="text-muted">Initial stock for the default variant</small>
        </div>
        
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check form-switch">
                <input type="checkbox" name="variant_available" value="1" class="form-check-input" id="variantAvailable" checked>
                <label class="form-check-label" for="variantAvailable">Variant Available</label>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="alert alert-info small">
                <i class="fas fa-info-circle"></i> A default variant will be created automatically. 
                You can add more variants from the Variants page.
            </div>
        </div>
        <?php endif; ?>
        
        <div class="col-12 mt-3">
            <?php if ($edit_product): ?>
                <button type="submit" name="edit_product" class="btn btn-success me-2">Update Product</button>
                <a href="products.php" class="btn btn-outline-secondary">Cancel Edit</a>
            <?php else: ?>
                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<h3>Product List</h3>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> Click "Variants" to manage product variants (sizes, colors, etc.)
</div>

<table class="table table-bordered table-striped align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Variants</th>
            <th>Status</th>
            <th>Image</th>
            <th style="width: 250px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if ($products && $products->num_rows > 0): 
            while($row = $products->fetch_assoc()): 
        ?>
        <tr>
            <td><?php echo $row['product_id']; ?></td>
            <td>
                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                <?php if(!empty($row['description'])): ?>
                    <br><small class="text-muted"><?php echo substr(htmlspecialchars($row['description']), 0, 50); ?>...</small>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['category_name'] ?? 'Unassigned'); ?></td>
            <td class="fw-bold text-danger">$<?php echo number_format($row['price'], 2); ?></td>
            <td>
                <span class="badge <?php echo $row['variant_count'] > 0 ? 'bg-success' : 'bg-warning'; ?>">
                    <?php echo $row['variant_count']; ?> variant(s)
                </span>
            </td>
            <td>
                <span class="badge bg-<?php 
                    $status = strtolower($row['status']);
                    if ($status == 'active') echo 'success'; 
                    elseif ($status == 'inactive') echo 'secondary';
                    else echo 'danger'; 
                ?>"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span>
            </td>
            <td>
                <?php if($row['image_url']): ?>
                    <img src='<?php echo htmlspecialchars($row['image_url']); ?>' 
                         width='50' 
                         height='50' 
                         class='img-thumbnail object-fit-cover'
                         style="object-fit: cover;"
                         alt='<?php echo htmlspecialchars($row['name']); ?>'
                         onerror="this.onerror=null;this.src='../assets/images/placeholder.png';">
                <?php else: ?>
                    <div class="text-muted small">No image</div>
                <?php endif; ?>
            </td>
            <td>
                <a href="variants.php?product_id=<?php echo $row['product_id']; ?>" 
                   class="btn btn-sm btn-info" 
                   title="Manage Variants">
                    <i class="fas fa-list"></i> Variants
                </a>
                <a href="products.php?edit=<?php echo $row['product_id']; ?>" 
                   class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="products.php?delete=<?php echo $row['product_id']; ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('WARNING: Deleting this product will also delete ALL related variants and attributes. Are you sure?')">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </td>
        </tr>
        <?php 
            endwhile; 
        else:
        ?>
        <tr>
            <td colspan="8" class="text-center">
                <div class="py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No products found in the database.</p>
                    <a href="products.php" class="btn btn-primary">Add Your First Product</a>
                </div>
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<style>
.object-fit-cover {
    object-fit: cover;
}
</style>

<?php include "../includes/footer.php"; ?>