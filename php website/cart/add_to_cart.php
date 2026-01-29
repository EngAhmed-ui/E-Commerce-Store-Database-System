<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $variant_id = $_POST['variant_id'];
    $quantity = $_POST['quantity'] ?? 1;

    $check = $conn->query("SELECT cart_item_id, quantity FROM Shopping_Cart WHERE user_id=$user_id AND variant_id=$variant_id");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $new_qty = $row['quantity'] + $quantity;
        $conn->query("UPDATE Shopping_Cart SET quantity=$new_qty WHERE cart_item_id=".$row['cart_item_id']);
    } else {
        $conn->query("INSERT INTO Shopping_Cart (user_id, variant_id, quantity) VALUES ($user_id, $variant_id, $quantity)");
    }
}

header("Location: cart.php");
exit;
?>