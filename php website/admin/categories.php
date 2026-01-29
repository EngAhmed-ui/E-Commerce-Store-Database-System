<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";

// Admin access control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    set_flash_message("Access denied.", "error");
    header('Location: ../auth/login.php'); 
    exit; 
}

// Handle add category
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? "");
    if ($name == "") {
        set_flash_message("Category name cannot be empty.", "error");
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);

        if ($stmt->execute()) {
            set_flash_message("Category added successfully.", "success");
        } else {
            set_flash_message("Error: Category name must be unique. " . $stmt->error, "error");
        }
        $stmt->close();
        header("Location: categories.php"); 
        exit;
    }
}

// --- Handle delete category with transaction ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // First, check if this is category 1 (Electronics) - we shouldn't delete the default category
    if ($id == 1) {
        set_flash_message("Cannot delete the default Electronics category.", "error");
        header('Location: categories.php'); 
        exit;
    }
    
    $conn->begin_transaction();
    
    try {
        // Get the Electronics category ID (should be 1)
        $default_category_id = 1;
        
        // Update products to reassign to Electronics category
        $stmt_update = $conn->prepare("UPDATE products SET category_id = ? WHERE category_id = ?");
        $stmt_update->bind_param("ii", $default_category_id, $id);
        
        if (!$stmt_update->execute()) {
            throw new Exception("Error reassigning products: " . $stmt_update->error);
        }
        $updated_count = $conn->affected_rows;
        $stmt_update->close();

        // Delete the category record
        $stmt_delete = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt_delete->bind_param("i", $id);
        
        if ($stmt_delete->execute()) {
            if ($conn->affected_rows > 0) {
                $conn->commit();
                set_flash_message("Category ID #{$id} deleted successfully. {$updated_count} product(s) reassigned to Electronics.", "success");
            } else {
                $conn->rollback();
                set_flash_message("Error: Category ID #{$id} not found.", "error");
            }
        } else {
            throw new Exception("Error deleting category record: " . $stmt_delete->error);
        }
        $stmt_delete->close();

    } catch (Exception $e) {
        $conn->rollback();
        set_flash_message("Transaction failed: " . $e->getMessage(), "error");
    }

    header('Location: categories.php'); 
    exit;
}

// Fetch all categories for display
$result = $conn->query("SELECT c.*, 
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) as product_count 
                       FROM categories c ORDER BY c.category_id DESC");
?>

<?php include "../includes/header.php"; ?>

<h2>Manage Categories</h2>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
<?php endif; ?>

<form method="POST" class="d-flex mb-4">
    <input type="text" name="name" placeholder="Category Name" class="form-control me-2" required>
    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
</form>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Product Count</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['category_id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo $row['product_count']; ?></td>
            <td>
                <?php if ($row['category_id'] != 1): ?>
                    <a href="categories.php?delete=<?php echo $row['category_id']; ?>" class="btn btn-sm btn-danger" 
                       onclick="return confirm('WARNING: Deleting this category will reassign <?php echo $row['product_count']; ?> product(s) to Electronics category. Are you sure?')">
                        Delete
                    </a>
                <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" disabled>Default Category</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include "../includes/footer.php"; ?>