<?php
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";

$err = $msg = "";
$edit_attribute = null;
$variant_id_filter = intval($_GET['variant_id'] ?? 0);

// Admin access control
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied.", "error");
    header("Location: ../auth/login.php");
    exit;
}

// Handle add attribute
if(isset($_POST['add_attribute'])){
    $variant_id = intval($_POST['variant_id']);
    $name = trim($_POST['attribute_name']);
    $value = trim($_POST['attribute_value']);

    if($variant_id === 0 || $name === "" || $value === ""){
        set_flash_message("All fields are required.", "error");
    } else {
        $stmt = $conn->prepare("INSERT INTO variant_attributes (variant_id, attribute_name, attribute_value) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $variant_id, $name, $value);

        if($stmt->execute()){
            set_flash_message("Attribute added successfully.", "success");
        } else {
            set_flash_message("Error adding attribute: " . $stmt->error, "error");
        }
        $stmt->close();
    }
    header("Location: variant_attributes.php?variant_id=" . $variant_id);
    exit;
}

// Handle delete attribute
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM variant_attributes WHERE variant_attribute_id=?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()){
        set_flash_message("Attribute deleted successfully.", "success");
    } else {
        set_flash_message("Error deleting attribute: " . $stmt->error, "error");
    }
    
    header("Location: variant_attributes.php" . ($variant_id_filter > 0 ? "?variant_id=$variant_id_filter" : ""));
    exit;
}

// Handle edit attribute
if(isset($_POST['edit_attribute'])){
    $id = intval($_POST['variant_attribute_id']);
    $name = trim($_POST['attribute_name']);
    $value = trim($_POST['attribute_value']);

    if($name === "" || $value === "") {
        set_flash_message("Attribute name and value are required.", "error");
    } else {
        $stmt = $conn->prepare("UPDATE variant_attributes SET attribute_name=?, attribute_value=? WHERE variant_attribute_id=?");
        $stmt->bind_param("ssi", $name, $value, $id);
        
        if($stmt->execute()){
            set_flash_message("Attribute updated successfully.", "success");
        } else {
            set_flash_message("Error updating attribute: " . $stmt->error, "error");
        }
        $stmt->close();
    }
    header("Location: variant_attributes.php" . ($variant_id_filter > 0 ? "?variant_id=$variant_id_filter" : ""));
    exit;
}

// Fetch attribute for editing
if(isset($_GET['edit'])){
    $id_to_edit = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM variant_attributes WHERE variant_attribute_id=?");
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows === 1){
        $edit_attribute = $result->fetch_assoc();
    } else {
        set_flash_message("Attribute not found.", "error");
    }
    $stmt->close();
}

// Fetch all attributes with filtering
$sql = "
    SELECT 
        va.*, 
        p.name AS product_name 
    FROM variant_attributes va
    JOIN product_variants pv ON va.variant_id = pv.variant_id
    JOIN products p ON pv.product_id = p.product_id
";
$where_clause = "";

if ($variant_id_filter > 0) {
    $where_clause = " WHERE va.variant_id = $variant_id_filter";
    $product_res = $conn->query("SELECT p.name FROM product_variants pv JOIN products p ON pv.product_id = p.product_id WHERE pv.variant_id = $variant_id_filter");
    $product_name = $product_res->fetch_assoc()['name'] ?? 'Unknown Product';
}

$sql .= $where_clause . " ORDER BY va.variant_id DESC, va.attribute_name ASC";
$result = $conn->query($sql);

// Fetch all variants for dropdown
$variants_res = $conn->query("
    SELECT pv.variant_id, p.name AS product_name
    FROM product_variants pv
    JOIN products p ON pv.product_id = p.product_id
    ORDER BY p.name, pv.variant_id
");

include "../includes/header.php"; 
?>

<h2>
    Manage Variant Attributes 
    <?php if ($variant_id_filter > 0): ?>
        <small class="text-muted fs-5">(For Product: <?php echo htmlspecialchars($product_name); ?> / Variant ID: <?php echo $variant_id_filter; ?>)</small>
        <a href="variant_attributes.php" class="btn btn-sm btn-secondary ms-3">Clear Filter</a>
    <?php endif; ?>
</h2>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?php echo get_flash_message('success'); ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?php echo get_flash_message('error'); ?></div>
<?php endif; ?>

<div class="card mb-4 p-3 bg-light">
    <h3 class="card-title">Add New Attribute</h3>
    <form method="POST">
        <input type="hidden" name="add_attribute" value="1">
        
        <div class="mb-3">
            <label for="variant_id" class="form-label">Variant</label>
            <select name="variant_id" id="variant_id" class="form-select" required>
                <option value="">Select Variant</option>
                <?php while($v = $variants_res->fetch_assoc()): ?>
                    <option value="<?php echo $v['variant_id']; ?>" 
                        <?php if ($v['variant_id'] == $variant_id_filter) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($v['product_name']); ?> (ID: <?php echo $v['variant_id']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="row">
            <div class="col-6 mb-3">
                <input type="text" name="attribute_name" class="form-control" placeholder="Attribute Name (e.g., Color)" required>
            </div>
            <div class="col-6 mb-3">
                <input type="text" name="attribute_value" class="form-control" placeholder="Attribute Value (e.g., Red)" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Add Attribute</button>
    </form>
</div>

<?php if($edit_attribute): ?>
<div class="card mb-4 p-3 border-info">
    <h3 class="card-title text-info">Edit Attribute ID: <?php echo $edit_attribute['variant_attribute_id']; ?></h3>
    <form method="POST">
        <input type="hidden" name="variant_attribute_id" value="<?php echo $edit_attribute['variant_attribute_id']; ?>">
        <input type="hidden" name="edit_attribute" value="1">

        <div class="row">
            <div class="col-6 mb-3">
                <input type="text" name="attribute_name" class="form-control" placeholder="Attribute Name" value="<?php echo htmlspecialchars($edit_attribute['attribute_name']); ?>" required>
            </div>
            <div class="col-6 mb-3">
                <input type="text" name="attribute_value" class="form-control" placeholder="Attribute Value" value="<?php echo htmlspecialchars($edit_attribute['attribute_value']); ?>" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-success">Update Attribute</button>
        <a href="variant_attributes.php" class="btn btn-outline-secondary">Cancel Edit</a>
    </form>
</div>
<?php endif; ?>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Attribute ID</th>
            <th>Variant ID</th>
            <th>Product</th>
            <th>Attribute Name</th>
            <th>Attribute Value</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($va = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $va['variant_attribute_id']; ?></td>
            <td>
                <?php echo $va['variant_id']; ?>
                <a href="?variant_id=<?php echo $va['variant_id']; ?>" class="badge bg-secondary ms-1">Filter</a>
            </td>
            <td><?php echo htmlspecialchars($va['product_name']); ?></td>
            <td><?php echo htmlspecialchars($va['attribute_name']); ?></td>
            <td><?php echo htmlspecialchars($va['attribute_value']); ?></td>
            <td>
                <a href="variant_attributes.php?edit=<?php echo $va['variant_attribute_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="variant_attributes.php?delete=<?php echo $va['variant_attribute_id']; ?>" onclick="return confirm('Delete this attribute?');" class="btn btn-sm btn-danger">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include "../includes/footer.php"; ?>