<?php
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";
include "../includes/header.php"; 

global $conn; 

// Admin Access Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied.", "error");
    header("Location: ../auth/login.php");
    exit;
}

// --- HANDLE DELETE (Check for orders before deletion) ---
if (isset($_GET['delete'])) {
    $address_id = intval($_GET['delete']);
    
    $conn->begin_transaction();
    $success = false;
    
    try {
        // 1. Check if any orders use this address (address_id is NOT NULL in orders)
        $check_stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE address_id = ?");
        $check_stmt->bind_param("i", $address_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $order_count = $check_result->fetch_assoc()['order_count'];
        $check_stmt->close();
        
        if ($order_count > 0) {
            throw new Exception("Cannot delete address used by $order_count order(s). Update or delete those orders first.");
        }
        
        // 2. Now delete the user address record.
        $stmt_delete = $conn->prepare("DELETE FROM user_addresses WHERE address_id = ?");
        $stmt_delete->bind_param("i", $address_id);
        
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $success = true;
            } else {
                 set_flash_message("Address not found.", "error");
            }
        }
        $stmt_delete->close();
        
        // Commit only if the deletion was successful
        if ($success) {
            $conn->commit();
            set_flash_message("Address ID #{$address_id} deleted successfully.", "success");
        } else {
            $conn->rollback();
        }

    } catch (Exception $e) {
        $conn->rollback();
        set_flash_message("Error deleting address: " . $e->getMessage(), "error");
    }
    
    header("Location: manage_user_addresses.php");
    exit;
}
// --- END HANDLE DELETE ---

// --- FETCH ALL ADDRESSES ---
$sql = "SELECT 
            ua.*, 
            u.full_name, 
            u.email 
        FROM user_addresses ua
        LEFT JOIN users u ON ua.user_id = u.user_id
        ORDER BY ua.address_id DESC";
$addresses_res = $conn->query($sql);
?>
---
<h2 class="mb-4">🏠 Manage All User Addresses</h2>
<p class="lead">View and manage shipping addresses used by all customers.</p>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?= get_flash_message('success') ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?= get_flash_message('error') ?></div>
<?php endif; ?>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Label</th>
            <th>Full Address</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if ($addresses_res && $addresses_res->num_rows > 0) :
            while($addr = $addresses_res->fetch_assoc()): 
        ?>
        <tr>
            <td><?= $addr['address_id'] ?></td>
            <td>
                <strong><?= htmlspecialchars($addr['full_name'] ?? 'N/A') ?></strong><br>
                <small class="text-muted"><?= htmlspecialchars($addr['email'] ?? 'N/A') ?></small>
            </td>
            <td><?= htmlspecialchars($addr['label'] ?? 'Home/Work') ?></td>
            <td>
                <?= htmlspecialchars($addr['street_address']) ?><br>
                <?= htmlspecialchars($addr['city']) ?>, ZIP: <?= htmlspecialchars($addr['zip_code']) ?>
            </td>
            <td>
                <a href="manage_user_addresses.php?delete=<?= $addr['address_id'] ?>" 
                   class="btn btn-sm btn-danger" 
                   onclick="return confirm('WARNING: Delete address ID <?= $addr['address_id'] ?>? If this address is used in any orders, deletion will fail.')">Delete</a>
            </td>
        </tr>
        <?php 
            endwhile; 
        else:
        ?>
        <tr>
            <td colspan="5" class="text-center">No addresses found in the database.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include "../includes/footer.php"; ?>