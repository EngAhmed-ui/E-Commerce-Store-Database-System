<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit;
}

$user_res = $conn->query("SELECT user_id, full_name, email, phone_number FROM Users WHERE user_id=$user_id");
$user = $user_res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $full_name = $_POST['full_name'] ?? "";
    $phone = $_POST['phone'] ?? "";
    $password = $_POST['password'] ?? "";

    if ($full_name && $phone) {
        $sql = "UPDATE Users SET full_name=?, phone_number=?";
        $param_type = "ss";
        $params = [$full_name, $phone];
        
        if ($password) {
            $sql .= ", password=?";
            $param_type .= "s";
            $params[] = $password; 
        }
        $sql .= " WHERE user_id=?";
        $param_type .= "i";
        $params[] = $user_id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($param_type, ...$params);

        if ($stmt->execute()) {
            set_flash_message("Profile updated successfully.", "success");
            $user_res = $conn->query("SELECT user_id, full_name, email, phone_number FROM Users WHERE user_id=$user_id");
            $user = $user_res->fetch_assoc();
        } else {
            set_flash_message("Error updating profile.", "error");
        }
        $stmt->close();
    } else {
        set_flash_message("Full name and phone are required.", "warning");
    }
    header("Location: profile.php");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">👤 My Profile</h2>
        <div class="card shadow-sm p-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mt-3">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>