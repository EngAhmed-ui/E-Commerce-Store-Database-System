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
    set_flash_message("You must be logged in to manage addresses.", "error");
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_address'])) {
        $stmt = $conn->prepare("INSERT INTO User_Addresses (user_id, label, street_address, city, zip_code) VALUES (?, ?, ?, ?, ?)");
        
        $zip_code = (int)$_POST['zip_code']; 
        
        $stmt->bind_param("isssi", $user_id, $_POST['label'], $_POST['street'], $_POST['city'], $zip_code);
        
        if ($stmt->execute()) {
            set_flash_message("Address added successfully!", "success");
        } else {
            set_flash_message("Failed to add address: " . $stmt->error, "error");
        }
        $stmt->close();

    } elseif (isset($_POST['delete_address'])) {
        $address_id = (int)$_POST['address_id'];
        $stmt = $conn->prepare("DELETE FROM User_Addresses WHERE address_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        
        try {
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    set_flash_message("Address deleted successfully.", "success");
                } else {
                    set_flash_message("Address not found or not authorized to delete.", "error");
                }
            }
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'a foreign key constraint fails') !== false) {
                 set_flash_message("Failed to delete address: This address is currently used for a previous order and cannot be removed.", "error");
            } else {
                set_flash_message("Failed to delete address: " . $e->getMessage(), "error");
            }
        }
        $stmt->close();
    }
    
    header("Location: manage_addresses.php");
    exit;
}

$stmt = $conn->prepare("SELECT address_id, label, street_address, city, zip_code FROM User_Addresses WHERE user_id = ? ORDER BY address_id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses_res = $stmt->get_result();
$stmt->close();

?>

<h2 class="mb-4">🏠 Manage Shipping Addresses</h2>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm p-4">
            <h4 class="card-title mb-3 text-primary">Add a New Address</h4>
            
            <form method="POST">
                <input type="hidden" name="add_address" value="1">
                
                <div class="mb-3">
                    <label for="label" class="form-label">Address Label</label>
                    <input type="text" class="form-control" id="label" name="label" required>
                </div>
                
                <div class="mb-3">
                    <label for="street" class="form-label">Street Address</label>
                    <input type="text" class="form-control" id="street" name="street" required>
                </div>
                
                <div class="mb-3">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" required>
                </div>
                
                <div class="mb-3">
                    <label for="zip_code" class="form-label">ZIP Code</label>
                    <input type="text" class="form-control" id="zip_code" name="zip_code" required pattern="\d{5,10}" title="Enter a valid ZIP/Postal code.">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mt-2">Save Address</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <h4 class="mb-3">Your Saved Addresses</h4>
        
        <?php if ($addresses_res->num_rows > 0): ?>
            <?php while ($addr = $addresses_res->fetch_assoc()): ?>
                <div class="card mb-3 border-secondary shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title text-success"><?= htmlspecialchars($addr['label']) ?></h5>
                            <p class="card-text mb-0"><?= htmlspecialchars($addr['street_address']) ?></p>
                            <p class="card-text small text-muted"><?= htmlspecialchars($addr['city']) ?>, ZIP: <?= htmlspecialchars($addr['zip_code']) ?></p>
                        </div>
                        
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                            <input type="hidden" name="delete_address" value="1">
                            <input type="hidden" name="address_id" value="<?= $addr['address_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                You currently have no saved addresses.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>