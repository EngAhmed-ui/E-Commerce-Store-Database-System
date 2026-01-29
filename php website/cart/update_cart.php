<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_item_id'])) {
    $cart_item_id = $_POST['cart_item_id'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("UPDATE Shopping_Cart SET quantity=? WHERE cart_item_id=?");
    $stmt->bind_param("ii", $quantity, $cart_item_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: cart.php");
exit;
?>