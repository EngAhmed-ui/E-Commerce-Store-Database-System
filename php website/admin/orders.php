<?php
include "../config/db.php";
if(session_status() === PHP_SESSION_NONE) session_start();
include "../includes/functions.php";

global $conn; 

// Admin access control
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    set_flash_message("Access denied.", "error");
    header("Location: ../auth/login.php");
    exit;
}

// FIX: Valid order statuses from your database schema
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

// --- 1. HANDLE ORDER DELETION (Transactional Delete with Payment deletion) ---
if(isset($_GET['delete'])){
    $order_id = intval($_GET['delete']);

    $conn->begin_transaction();

    try {
        // 1. Delete payment record first (foreign key constraint: payments.order_id references orders.order_id)
        $stmt_payment = $conn->prepare("DELETE FROM payments WHERE order_id = ?");
        $stmt_payment->bind_param("i", $order_id);
        $stmt_payment->execute();
        $stmt_payment->close();

        // 2. Delete all child records from order_items
        $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $stmt_items->close();

        // 3. Now delete the parent record from orders
        $stmt_order = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $stmt_order->close();

        $conn->commit();
        set_flash_message("Order #$order_id removed successfully.", "success");
    } catch (Exception $e) {
        $conn->rollback();
        set_flash_message("Error removing order #$order_id: " . $e->getMessage(), "error");
    }
    header("Location: orders.php");
    exit;
}
// END DELETE LOGIC

// --- 2. Filtering, Searching, and Pagination Logic ---

$limit = 20;
$page = intval($_GET['p'] ?? 1);
$page = max(1, $page); 
$offset = ($page - 1) * $limit;

$search_term = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['status'] ?? '');

$where_clauses = [];
$bind_params = '';
$bind_values = [];

// A. Status Filter
if (!empty($filter_status) && in_array($filter_status, $valid_statuses)) {
    $where_clauses[] = "o.status = ?";
    $bind_params .= "s";
    $bind_values[] = $filter_status;
}

// B. Search Filter (Order ID, Customer Name, or Email)
if (!empty($search_term)) {
    $where_clauses[] = "(o.order_id = ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $search_like = "%" . $search_term . "%"; 
    $order_id_search = intval($search_term);

    $bind_params .= "iss";
    $bind_values[] = $order_id_search;
    $bind_values[] = $search_like;
    $bind_values[] = $search_like;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// 3. Count total rows for pagination
$count_sql = "SELECT COUNT(o.order_id) AS total_count 
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.user_id " . $where_sql;

$stmt_count = $conn->prepare($count_sql);

if (count($bind_values) > 0) {
    // Adjust for count query (remove LIMIT/OFFSET binding)
    $count_bind_params = $bind_params;
    $count_bind_values = $bind_values;
    $stmt_count->bind_param($count_bind_params, ...$count_bind_values);
}

$stmt_count->execute();
$count_res = $stmt_count->get_result();
$stmt_count->close();

$total_records = $count_res->fetch_assoc()['total_count'] ?? 0;
$total_pages = ceil($total_records / $limit);
$current_url_params = http_build_query(['search' => $search_term, 'status' => $filter_status]);

// 4. Fetch the limited, filtered, and searched orders
$sql = "SELECT 
            o.*, 
            u.full_name, 
            u.email,
            a.street_address,
            a.city,
            a.zip_code
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN user_addresses a ON o.address_id = a.address_id
        " . $where_sql . " 
        ORDER BY o.order_date DESC 
        LIMIT ? OFFSET ?";

$stmt_orders = $conn->prepare($sql);
$final_bind_params = $bind_params . "ii";
$final_bind_values = $bind_values;
$final_bind_values[] = $limit;
$final_bind_values[] = $offset;

if (!empty($final_bind_values)) {
    $stmt_orders->bind_param($final_bind_params, ...$final_bind_values);
}

$stmt_orders->execute();
$result = $stmt_orders->get_result();
$stmt_orders->close();
?>

<?php include "../includes/header.php"; ?>

<h2>📦 Manage Customer Orders</h2>

<?php if (get_flash_message('success')): ?>
    <div class="alert alert-success"><?= get_flash_message('success') ?></div>
<?php endif; ?>
<?php if (get_flash_message('error')): ?>
    <div class="alert alert-danger"><?= get_flash_message('error') ?></div>
<?php endif; ?>

<div class="card p-3 mb-4 shadow-sm">
    <form method="GET" class="row g-3 align-items-end">
        
        <div class="col-md-5">
            <label for="search-input" class="form-label">Search (Order ID, Name, Email)</label>
            <input type="text" name="search" id="search-input" class="form-control" 
                   placeholder="Enter ID, Name, or Email" 
                   value="<?= htmlspecialchars($search_term) ?>">
        </div>
        
        <div class="col-md-4">
            <label for="status-filter" class="form-label">Filter by Status</label>
            <select name="status" id="status-filter" class="form-select">
                <option value="">-- All Statuses --</option>
                <?php foreach ($valid_statuses as $status_option): ?>
                    <option value="<?= $status_option ?>" 
                        <?php if ($filter_status === $status_option) echo 'selected'; ?>>
                        <?= ucfirst($status_option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
            <a href="orders.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>
<div class="alert alert-info">
    Showing **<?= $result->num_rows ?? 0 ?>** of **<?= $total_records ?>** orders.
</div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Shipping Address</th>
            <th>Status</th>
            <th>Total</th>
            <th>Order Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($order = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $order['order_id']; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($order['email']); ?></small>
                </td>
                <td> 
                    <?php if (!empty($order['street_address'])): ?>
                        <?php echo htmlspecialchars($order['street_address']); ?><br>
                        <small><?php echo htmlspecialchars($order['city']); ?>, ZIP: <?php echo htmlspecialchars($order['zip_code']); ?></small>
                    <?php else: ?>
                        <span class="text-danger">Address Missing/Unlinked</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge bg-<?php 
                    $status = strtolower($order['status']);
                    if ($status == 'delivered') echo 'success'; 
                    elseif ($status == 'pending' || $status == 'processing') echo 'warning'; 
                    elseif ($status == 'shipped') echo 'info';
                    else echo 'danger'; 
                ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                <td><strong>$<?php echo number_format($order['final_total'], 2); ?></strong></td>
                <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                <td>
                    <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-secondary">View Details</a>
                    <a href="order_edit_status.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">Edit Status</a>
                    <a href="orders.php?delete=<?php echo $order['order_id']; ?>" 
                       class="btn btn-sm btn-danger mt-1" 
                       onclick="return confirm('Are you sure you want to remove Order #<?php echo $order['order_id']; ?>? This will delete the order, payment, and all order items.')">
                       Remove Order
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No orders found matching your criteria.</td>
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