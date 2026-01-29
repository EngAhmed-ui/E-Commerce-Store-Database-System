<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";

    if ($email === "" || $password === "") {
        set_flash_message("Email and password are required!", "warning");
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, role FROM Users WHERE email=? AND password=?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows == 1) {
            $user = $res->fetch_assoc();

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            set_flash_message("Invalid email or password.", "error");
        }
        $stmt->close();
    }
    header("Location: login.php");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4 text-center">🔐 Customer Login</h2>
        <div class="card shadow-lg p-4">
            <h5 class="card-title text-center mb-4">Sign in to your account</h5>
            
            <form method="POST">
                <div class="mb-3">
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="Email Address" required>
                </div>
                
                <div class="mb-4">
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Login</button>
            </form>
            
            <p class="text-center mt-3">
                Don't have an account? <a href="register.php" class="text-decoration-none">Create Account</a>
            </p>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>