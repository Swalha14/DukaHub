<?php
session_start();
require_once "conf.php";
require_once __DIR__ . "/Layout/layout.php";

$layout = new layout();
$layout->header($conf);
$layout->nav($conf);

// Detect if user is logged in
$loggedIn = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Banner message
$cart_message = '';

// Handle add to cart 
if ($loggedIn && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    $cart_message = " Product added to cart!";
}

// Ensure a category is selected
if (!isset($_GET['category_id'])) {
    echo "<p>No category selected.</p>";
    exit;
}

$category_id = intval($_GET['category_id']);

// Get category info
$cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = :id");
$cat_stmt->bindParam(':id', $category_id);
$cat_stmt->execute();
$category = $cat_stmt->fetch(PDO::FETCH_ASSOC);
$category_name = $category ? $category['name'] : "Products";

// Get products in this category
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = :cat_id");
$stmt->bindParam(':cat_id', $category_id);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

    <!-- Banner message -->
    <?php if (!empty($cart_message)): ?>
        <div id="cart-message" class="alert alert-success text-center" style="font-size:1.2em;">
            <?php echo htmlspecialchars($cart_message); ?>
        </div>
    <?php endif; ?>

    <section class="products">
        <h2><?php echo htmlspecialchars($category_name); ?></h2>
        <div class="product-grid">
            <?php if ($products): ?>
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <img src="Images/Products/<?php echo htmlspecialchars($p['image']); ?>" 
                             alt="<?php echo htmlspecialchars($p['name']); ?>">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p><?php echo htmlspecialchars($p['description']); ?></p>
                        <p><strong>KSh <?php echo number_format($p['price'], 0); ?></strong></p>

                        <?php if ($loggedIn): ?>
                            <form method="post" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="10" required>
                                <button type="submit" name="add_to_cart">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-warning" onclick="alert('Please log in to add products to cart.')">
                                Add to Cart
                            </button>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found in this category.</p>
            <?php endif; ?>
        </div>
    </section>

</div>

<script>
    // Fade out banner after 3 seconds
    document.addEventListener("DOMContentLoaded", function() {
        const msg = document.getElementById('cart-message');
        if (msg) {
            setTimeout(() => {
                msg.style.transition = "opacity 1s";
                msg.style.opacity = 0;
                setTimeout(() => msg.remove(), 1000);
            }, 3000);
        }

        // Ensure logged out users cannot add to cart
        <?php if (!$loggedIn): ?>
        const forms = document.querySelectorAll('.add-to-cart-form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                alert("Please log in to add products to cart.");
            });
        });
        <?php endif; ?>
    });
</script>

<?php $layout->footer($conf); ?>
