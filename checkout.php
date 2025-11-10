<?php
session_start();
require_once "ClassAutoLoad.php";
require_once "conf.php";
require_once __DIR__ . "/Layout/layout.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit;
}

$Objlayout = new layout();
$Objlayout->header($conf);
$Objlayout->nav($conf);

global $SQL, $conf;
$conn = $SQL->getConnection();
$user_id = $_SESSION['user_id'];

if (empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. <a href='index.php'>Go shopping</a></p>";
    $Objlayout->footer($conf);
    exit;
}

// Fetch user info
$user_stmt = $conn->prepare("SELECT username, email FROM users WHERE id = :id");
$user_stmt->execute([':id' => $user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch cart products
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']];
    $total += $p['price'] * $qty;
}

// Handle "Pay to Order" submission
$order_success = '';
if (isset($_POST['pay_order'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'] ?? 'Online';

    // Simulate payment success
    $payment_success = rand(0, 1) === 1; // 50% chance success
    if (!$payment_success) {
        $order_success = "Payment failed. Please try again.";
    } else {
        $transaction_id = 'TXN' . time();
        $payment_status = 'Paid';

        try {
            $conn->beginTransaction();

            // Insert order
            $order_stmt = $conn->prepare("INSERT INTO orders 
                (user_id, fullname, email, phone, address, total, payment_method, payment_status, transaction_id)
                VALUES (:user_id, :fullname, :email, :phone, :address, :total, :payment_method, :payment_status, :transaction_id)");
            $order_stmt->execute([
                ':user_id' => $user_id,
                ':fullname' => $fullname,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':total' => $total,
                ':payment_method' => $payment_method,
                ':payment_status' => $payment_status,
                ':transaction_id' => $transaction_id
            ]);

            $order_id = $conn->lastInsertId();

            // Insert order items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                         VALUES (:order_id, :product_id, :quantity, :price)");
            foreach ($products as $p) {
                $item_stmt->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $p['id'],
                    ':quantity' => $_SESSION['cart'][$p['id']],
                    ':price' => $p['price']
                ]);
            }

            $conn->commit();
            $_SESSION['cart'] = [];
            $order_success = "Payment successful! Your order has been placed.";
        } catch (Exception $e) {
            $conn->rollBack();
            $order_success = "Something went wrong: " . $e->getMessage();
        }
    }
}
?>

<link rel="stylesheet" href="css/checkout.css">

<div class="checkout-container">
    <h2>Checkout</h2>

    <?php if ($order_success): ?>
        <div class="alert alert-success text-center"><?php echo htmlspecialchars($order_success); ?></div>
        <p class="text-center"><a href="index.php">Continue Shopping</a></p>
    <?php else: ?>

        <form method="post">
            <div class="shipping-info">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required>

                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3" required></textarea>

                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="COD">Cash on Delivery</option>
                    <option value="Online">Online Payment</option>
                </select>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p):
                            $qty = $_SESSION['cart'][$p['id']];
                            $subtotal = $p['price'] * $qty;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td>KSh <?php echo number_format($p['price'],0); ?></td>
                            <td><?php echo $qty; ?></td>
                            <td>KSh <?php echo number_format($subtotal,0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total">Total: KSh <?php echo number_format($total,0); ?></div>
            </div>

            <button type="submit" name="pay_order" class="place-order-btn">Pay to Order</button>
        </form>

    <?php endif; ?>
</div>

<?php $Objlayout->footer($conf); ?>
