<?php
require_once "conf.php";
require_once __DIR__ . "/Layout/layout.php";

$layout = new layout();
$layout->header($conf);
$layout->nav($conf);

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

// Get all products under that category
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = :cat_id");
$stmt->bindParam(':cat_id', $category_id);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
                    <p><strong>$<?php echo number_format($p['price'], 2); ?></strong></p>

                    <form method="post" action="cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <input type="number" name="quantity" value="1" min="1" max="10" required>
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products found in this category.</p>
        <?php endif; ?>
    </div>
</section>

<?php $layout->footer($conf); ?>
