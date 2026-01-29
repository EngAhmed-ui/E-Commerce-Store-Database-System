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

// --- CRUD Operations ---
$self_user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'view';
$user_id = intval($_GET['id'] ?? 0);
$err = $msg = "";


// --- 1. HANDLE DELETE (Transactional Deletion for Safety) ---
if ($action === 'delete' && $user_id > 0) {
    if ($user_id == $self_user_id) {
        set_flash_message("Error: You cannot delete your own account while logged in.", "error");
    } else {
        // Start a transaction for safe multi-step deletion
        $conn->begin_transaction();
        
        try {
            // Correct deletion order to handle all foreign key constraints:
            // 1. First, find all orders by this user to delete payments
            $order_ids = [];
            $stmt_get_orders = $conn->prepare("SELECT order_id FROM orders WHERE user_id = ?");
            $stmt_get_orders->bind_param("i", $user_id);
            $stmt_get_orders->execute();
            $orders_result = $stmt_get_orders->get_result();
            while ($row = $orders_result->fetch_assoc()) {
                $order_ids[] = $row['order_id'];
            }
            $stmt_get_orders->close();
            
            // 2. Delete payments for each order (payments -> orders)
            if (!empty($order_ids)) {
                $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
                $stmt_delete_payments = $conn->prepare("DELETE FROM payments WHERE order_id IN ($placeholders)");
                $types = str_repeat('i', count($order_ids));
                $stmt_delete_payments->bind_param($types, ...$order_ids);
                $stmt_delete_payments->execute();
                $stmt_delete_payments->close();
                
                // 3. Delete order items for each order
                $stmt_delete_items = $conn->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)");
                $stmt_delete_items->bind_param($types, ...$order_ids);
                $stmt_delete_items->execute();
                $stmt_delete_items->close();
                
                // 4. Delete the orders
                $stmt_delete_orders = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
                $stmt_delete_orders->bind_param("i", $user_id);
                $stmt_delete_orders->execute();
                $stmt_delete_orders->close();
            }
            
            // 5. Delete the user's shopping cart items
            $stmt_cart = $conn->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
            $stmt_cart->bind_param("i", $user_id);
            $stmt_cart->execute();
            $stmt_cart->close();

            // 6. Delete the user's addresses
            $stmt_addr = $conn->prepare("DELETE FROM user_addresses WHERE user_id = ?");
            $stmt_addr->bind_param("i", $user_id);
            $stmt_addr->execute();
            $stmt_addr->close();
            
            // 7. Finally, delete the user record itself
            $stmt_user = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt_user->bind_param("i", $user_id);
            
            if ($stmt_user->execute()) {
                $conn->commit();
                set_flash_message("User ID #{$user_id} and all related data deleted successfully.", "success");
            } else {
                throw new Exception("Error deleting user record. Details: " . $stmt_user->error);
            }
            $stmt_user->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            set_flash_message("Transaction failed: Could not delete user. " . $e->getMessage(), "error");
        }
    }
    header("Location: manage_users.php");
    exit;
}
// END DELETE LOGIC

// --- 2. ADD EDIT FUNCTIONALITY ---
// Handle edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $edit_user_id = intval($_POST['user_id']);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'customer');
    $phone_number = trim($_POST['phone_number'] ?? '');
    
    $valid_roles = ['customer', 'admin', 'moderator'];
    
    if (empty($full_name) || empty($email) || empty($phone_number)) {
        set_flash_message("All fields are required.", "error");
    } elseif (!in_array($role, $valid_roles)) {
        set_flash_message("Invalid role selected.", "error");
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, phone_number = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $role, $phone_number, $edit_user_id);
        
        if ($stmt->execute()) {
            set_flash_message("User ID #{$edit_user_id} updated successfully.", "success");
        } else {
            set_flash_message("Error updating user: " . $stmt->error, "error");
        }
        $stmt->close();
    }
    header("Location: manage_users.php");
    exit;
}

// Fetch user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_user_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT user_id, full_name, email, role, phone_number, created_at FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $edit_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $edit_user = $result->fetch_assoc();
    } else {
        set_flash_message("User not found.", "error");
    }
    $stmt->close();
}
// END EDIT FUNCTIONALITY

// --- 3. Filtering, Searching, and Pagination Logic ---

// Pagination variables
$limit = 20;
$page = intval($_GET['p'] ?? 1);
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Filter and Search variables
$search_term = trim($_GET['search'] ?? '');
$filter_role = trim($_GET['role'] ?? '');
$valid_roles = ['customer', 'admin', 'moderator'];

$where_clauses = [];
$bind_params = '';
$bind_values = [];

// A. Role Filter
if (!empty($filter_role) && in_array($filter_role, $valid_roles)) {
    $where_clauses[] = "role = ?";
    $bind_params .= "s";
    $bind_values[] = $filter_role;
}

// B. Search Filter (by Name or Email)
if (!empty($search_term)) {
    $where_clauses[] = "(full_name LIKE ? OR email LIKE ?)";
    $search_like = "%" . $search_term . "%";
    $bind_params .= "ss";
    $bind_values[] = $search_like;
    $bind_values[] = $search_like;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// 3. Count total rows for pagination
$count_sql = "SELECT COUNT(user_id) AS total_count FROM users " . $where_sql;
$stmt_count = $conn->prepare($count_sql);

if (!empty($bind_values)) {
    $stmt_count->bind_param($bind_params, ...$bind_values);
}

$stmt_count->execute();
$count_res = $stmt_count->get_result();
$stmt_count->close();

$total_records = $count_res->fetch_assoc()['total_count'] ?? 0;
$total_pages = ceil($total_records / $limit);
$current_url_params = http_build_query(['search' => $search_term, 'role' => $filter_role]);

// 4. Fetch the limited, filtered, and searched users
$sql = "SELECT user_id, full_name, email, role, phone_number, created_at FROM users 
        " . $where_sql . " 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";

$final_bind_params = $bind_params . "ii";
$final_bind_values = $bind_values;
$final_bind_values[] = $limit;
$final_bind_values[] = $offset;

$stmt_users = $conn->prepare($sql);
if (!empty($final_bind_values)) {
    $stmt_users->bind_param($final_bind_params, ...$final_bind_values);
}
$stmt_users->execute();
$users_res = $stmt_users->get_result();
$stmt_users->close();
?>

<h2>👤 Manage All Users</h2>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?= get_flash_message('success') ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?= get_flash_message('error') ?></div>
<?php endif; ?>

<!-- EDIT USER FORM -->
<?php if($edit_user): ?>
<div class="card mb-4 p-3 border-primary">
    <h3 class="card-title text-primary">Edit User: <?php echo htmlspecialchars($edit_user['full_name']); ?></h3>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
        <input type="hidden" name="edit_user" value="1">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($edit_user['phone_number']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <?php foreach ($valid_roles as $role_option): ?>
                        <option value="<?php echo $role_option; ?>" 
                            <?php if ($edit_user['role'] === $role_option) echo 'selected'; ?>>
                            <?php echo ucfirst($role_option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-success">Update User</button>
        <a href="manage_users.php" class="btn btn-outline-secondary">Cancel Edit</a>
    </form>
</div>
<?php endif; ?>

<div class="card p-3 mb-4 shadow-sm">
    <form method="GET" class="row g-3 align-items-end">
        
        <div class="col-md-5">
            <label for="search-input" class="form-label">Search (Name, Email)</label>
            <input type="text" name="search" id="search-input" class="form-control" 
                   placeholder="Enter Name or Email" 
                   value="<?= htmlspecialchars($search_term) ?>">
        </div>
        
        <div class="col-md-4">
            <label for="role-filter" class="form-label">Filter by Role</label>
            <select name="role" id="role-filter" class="form-select">
                <option value="">-- All Roles --</option>
                <?php foreach ($valid_roles as $role_option): ?>
                    <option value="<?= $role_option ?>" 
                        <?php if ($filter_role === $role_option) echo 'selected'; ?>>
                        <?= ucfirst($role_option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
            <a href="manage_users.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>
<div class="alert alert-info">
    Showing **<?= $users_res->num_rows ?? 0 ?>** of **<?= $total_records ?>** users.
</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer/Admin</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($users_res && $users_res->num_rows > 0): ?>
            <?php while($user = $users_res->fetch_assoc()): ?>
            <tr>
                <td><?= $user['user_id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($user['phone_number']) ?></td>
                <td>
                    <span class="badge bg-<?= 
                        ($user['role'] === 'admin') ? 'danger' : 
                        (($user['role'] === 'moderator') ? 'warning' : 'success') 
                    ?>"><?= ucfirst($user['role']) ?></span>
                </td>
                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                <td>
                    <a href="manage_users.php?edit=<?= $user['user_id'] ?>" class="btn btn-sm btn-info">Edit</a> 
                    <?php if ($user['user_id'] != $self_user_id): ?>
                        <a href="manage_users.php?action=delete&id=<?= $user['user_id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('WARNING: This will delete the user and all their related orders, payments, addresses, and shopping cart records. Proceed?')">Delete</a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-outline-danger" disabled>Self-Delete Blocked</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No users found matching your criteria.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<nav>
  <ul class="pagination justify-content-center">
    <?php if ($page > 1): ?>
        <li class="page-item"><a class="page-link" href="?p=<?= $page - 1 ?>&<?= $current_url_params ?>">Previous</a></li>
    <?php else: ?>
        <li class="page-item disabled"><span class="page-link">Previous</span></li>
    <?php endif; ?>

    <?php 
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);

    if ($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

    for ($i = $start; $i <= $end; $i++): 
    ?>
        <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
            <a class="page-link" href="?p=<?= $i ?>&<?= $current_url_params ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>

    <?php if ($end < $total_pages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; ?>

    <?php if ($page < $total_pages): ?>
        <li class="page-item"><a class="page-link" href="?p=<?= $page + 1 ?>&<?= $current_url_params ?>">Next</a></li>
    <?php else: ?>
        <li class="page-item disabled"><span class="page-link">Next</span></li>
    <?php endif; ?>
  </ul>
</nav>
<?php include "../includes/footer.php"; ?>