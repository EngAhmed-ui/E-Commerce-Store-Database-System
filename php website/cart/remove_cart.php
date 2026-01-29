<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['cart_item_id'])) {
    $cart_item_id = $_GET['cart_item_id'];
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("DELETE FROM Shopping_Cart WHERE cart_item_id=? AND user_id=?");
        $stmt->bind_param("ii", $cart_item_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: cart.php");
exit;
?>