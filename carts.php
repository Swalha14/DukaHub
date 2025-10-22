<?php
session_start();
require_once "conf.php";

if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $item = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'image' => $product['image']
        ];

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['id'] == $item['id']) {
                $cart_item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = $item;
        }

        header("Location: view_cart.php");
        exit;
    }
}
?>
