<?php
session_start();
require_once "ClassAutoLoad.php";
require_once "conf.php";
require_once __DIR__ . "/Layout/layout.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: signin.php");
    exit;
}

$Objlayout = new layout();
$Objlayout->header($conf);
$Objlayout->nav($conf);

// Initialize cart 
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Message variable
$cart_message = '';

// Handle updating cart quantities
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
    $cart_message = " Cart updated!";
}

// Handle removing a single product
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$remove_id]);
    $cart_message =" Product removed from cart!";
}

// Get PDO connection
global $SQL, $conf;
$conn = $SQL->getConnection();
?>

<link rel="stylesheet" href="CSS/cart.css">

<div class="container mt-4">
    <h2> Your Cart</h2>
    <hr>

    <?php
    // Show message if any
    if (!empty($cart_message)) {
        echo '<div id="cart-message" class="alert alert-success">' . htmlspecialchars($cart_message) . '</div>';
    }

    if (!empty($_SESSION['cart'])):
        $ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <form method="post" action="cart.php">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($products as $p):
                        $qty = $_SESSION['cart'][$p['id']];
                        $subtotal = $p['price'] * $qty;
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td>KSh <?php echo number_format($p['price'], 0); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo $p['id']; ?>]" 
                                       value="<?php echo $qty; ?>" min="1" max="10" class="form-control">
                            </td>
                            <td>KSh <?php echo number_format($subtotal, 0); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td colspan="2"><strong>KSh <?php echo number_format($total, 0); ?></strong></td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        </form>

    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>

<script>
    // Fade out messages after 3 seconds
    document.addEventListener("DOMContentLoaded", function() {
        const msg = document.getElementById('cart-message');
        if (msg) {
            setTimeout(() => {
                msg.style.transition = "opacity 1s";
                msg.style.opacity = 0;
                setTimeout(() => msg.remove(), 1000);
            }, 3000);
        }
    });
</script>

<?php $Objlayout->footer($conf); ?>
