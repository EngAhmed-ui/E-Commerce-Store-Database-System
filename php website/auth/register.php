<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
include "../includes/header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST["full_name"] ?? "";
    $email = $_POST["email"] ?? "";
    $phone = $_POST["phone"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($full_name === "" || $email === "" || $phone === "" || $password === "") {
        set_flash_message("All fields are required!", "warning");
    } else {
        $sql = "INSERT INTO Users (full_name, email, phone_number, password, created_at, role) VALUES (?, ?, ?, ?, NOW(), 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $full_name, $email, $phone, $password);

        if ($stmt->execute()) {
            set_flash_message("Account created successfully.", "success");
            header("Location: login.php");
            exit;
        } else {
            set_flash_message("Error creating account.", "error");
        }
        $stmt->close();
    }
    header("Location: register.php");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4 text-center">📝 Register Account</h2>
        <div class="card shadow-lg p-4">
            <h5 class="card-title text-center mb-4">Join our store</h5>
            
            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="full_name" class="form-control form-control-lg" placeholder="Full Name" required>
                </div>
                
                <div class="mb-3">
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="Email Address" required>
                </div>
                
                <div class="mb-3">
                    <input type="tel" name="phone" class="form-control form-control-lg" placeholder="Phone Number" required>
                </div>
                
                <div class="mb-4">
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                </div>
                
                <button type="submit" class="btn btn-success btn-lg w-100 mb-3">Create Account</button>
            </form>
            
            <p class="text-center mt-3">
                Already have an account? <a href="login.php" class="text-decoration-none">Login Here</a>
            </p>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>