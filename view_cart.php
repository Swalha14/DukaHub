<?php
session_start();
require_once "layout.php";

$layout = new layout();
$layout->header(['site_name' => 'DukaHub']);
$layout->nav(['site_name' => 'DukaHub']);
?>

<section class="cart">
    <h2>Your Shopping Cart</h2>
    <?php if (!empty($_SESSION['cart'])): ?>
        <table class="cart-table">
            <tr>
                <th>Image</th><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th>
            </tr>
            <?php $total = 0; ?>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <?php $subtotal = $item['price'] * $item['quantity']; $total += $subtotal; ?>
                <tr>
                    <td><img src="Images/Products/<?php echo htmlspecialchars($item['image']); ?>" width="60"></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>KSh<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>KSh<?php echo number_format($subtotal, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <h3>Total: KSh<?php echo number_format($total, 2); ?></h3>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</section>

<?php $layout->footer(['site_name' => 'DukaHub']); ?>

