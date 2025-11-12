<?php
session_start();
require_once 'ClassAutoLoad.php';
require_once 'conf.php';

// Restrict access to admins only
if (!isset($_SESSION['admin_id'])) {
    header("Location: signin.php");
    exit();
}

$Objlayout = new layout();
global $SQL, $conf;
$conn = $SQL->getConnection();

$adminEmail = $_SESSION['admin_email'] ?? 'admin@dukahub.com';

$Objlayout->header($conf);
$Objlayout->nav($conf);

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['order_status'])) {
    $update_stmt = $conn->prepare("UPDATE orders SET order_status = :status, updated_at = NOW() WHERE id = :id");
    $update_stmt->execute([
        ':status' => $_POST['order_status'],
        ':id' => $_POST['order_id']
    ]);
    echo "<p style='color:green;'>Order #".$_POST['order_id']." status updated to ".htmlspecialchars($_POST['order_status'])."</p>";
}

// Fetch all orders with user info
$orders_stmt = $conn->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders_stmt->execute();
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate orders into pending and completed
$pending_orders = [];
$completed_orders = [];

foreach ($orders as $order) {
    if ($order['order_status'] === 'Delivered') {
        $completed_orders[] = $order;
    } else {
        $pending_orders[] = $order;
    }
}

// Fetch order items for all orders
$order_items = [];
$all_orders = array_merge($pending_orders, $completed_orders);
if ($all_orders) {
    $order_ids = array_column($all_orders, 'id');
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    $items_stmt = $conn->prepare("
        SELECT oi.*, p.name 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id IN ($placeholders)
        ORDER BY oi.order_id
    ");
    $items_stmt->execute($order_ids);
    $items_result = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items_result as $item) {
        $order_items[$item['order_id']][] = $item;
    }
}
?>

<link rel="stylesheet" href="CSS/profile.css">

<main class="admin-orders-container">
    <h1>Welcome, <?php echo htmlspecialchars($adminEmail); ?></h1>

    <!-- Pending Orders -->
    <section>
        <h2>View Orders (Pending)</h2>
        <?php if ($pending_orders): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo date("d M Y H:i", strtotime($order['created_at'])); ?></td>
                            <td>KSh <?php echo number_format($order['total'], 2); ?></td>
                            <td><?php echo $order['payment_method'] ?? '-'; ?></td>
                            <td><?php echo $order['payment_status'] ?? '-'; ?></td>
                            <td>
                                <span class="status <?php echo strtolower($order['order_status']); ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="order_status" required>
                                        <option value="Pending" <?php if($order['order_status']=='Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                    <button type="submit">Update</button>
                                </form>

                                <?php if (!empty($order_items[$order['id']])): ?>
                                    <button class="view-items-btn" onclick="toggleItems(<?php echo $order['id']; ?>)">
                                        View Items
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr id="order-items-<?php echo $order['id']; ?>" class="order-items-row" style="display:none;">
                            <td colspan="9">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items[$order['id']] as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td>KSh <?php echo number_format($item['price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>KSh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending orders.</p>
        <?php endif; ?>
    </section>

    <!-- Completed Orders -->
    <section>
        <h2>Completed Orders (Delivered)</h2>
        <?php if ($completed_orders): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completed_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo date("d M Y H:i", strtotime($order['created_at'])); ?></td>
                            <td>KSh <?php echo number_format($order['total'], 2); ?></td>
                            <td><?php echo $order['payment_method'] ?? '-'; ?></td>
                            <td><?php echo $order['payment_status'] ?? '-'; ?></td>
                            <td>
                                <span class="status <?php echo strtolower($order['order_status']); ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($order_items[$order['id']])): ?>
                                    <button class="view-items-btn" onclick="toggleItems(<?php echo $order['id']; ?>)">
                                        View Items
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr id="order-items-<?php echo $order['id']; ?>" class="order-items-row" style="display:none;">
                            <td colspan="9">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items[$order['id']] as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td>KSh <?php echo number_format($item['price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>KSh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No completed orders.</p>
        <?php endif; ?>
    </section>

</main>

<script>
function toggleItems(orderId) {
    const row = document.getElementById('order-items-' + orderId);
    row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
}
</script>

<?php
$Objlayout->footer($conf);
?>
