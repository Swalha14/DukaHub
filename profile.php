<?php
session_start();
require_once "ClassAutoLoad.php";
require_once "conf.php";
require_once __DIR__ . "/Layout/layout.php";

// Ensure user is logged in
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

// Fetch user info
$user_stmt = $conn->prepare("SELECT username, email FROM users WHERE id = :id");
$user_stmt->execute([':id' => $user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch orders for the user
$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
$orders_stmt->execute([':user_id' => $user_id]);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate orders into ongoing and completed
$ongoing_orders = [];
$completed_orders = [];
foreach ($orders as $order) {
    if ($order['order_status'] === 'Delivered') {
        $completed_orders[] = $order;
    } else {
        $ongoing_orders[] = $order;
    }
}

// Fetch order items for all orders
$order_items = [];
$all_orders = array_merge($ongoing_orders, $completed_orders);
if ($all_orders) {
    $order_ids = array_column($all_orders, 'id');
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    $items_stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.id
                                  WHERE oi.order_id IN ($placeholders)
                                  ORDER BY oi.order_id");
    $items_stmt->execute($order_ids);
    $items_result = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items_result as $item) {
        $order_items[$item['order_id']][] = $item;
    }
}
?>

<link rel="stylesheet" href="CSS/profile.css">

<div class="profile-container">
    <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>

    <!-- Ongoing Orders -->
    <h3>Ongoing Orders</h3>
    <?php if ($ongoing_orders): ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ongoing_orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date("d M Y H:i", strtotime($order['created_at'])); ?></td>
                        <td>KSh <?php echo number_format($order['total'],0); ?></td>
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
                        <td colspan="5">
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
                                            <td>KSh <?php echo number_format($item['price'],0); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>KSh <?php echo number_format($item['price'] * $item['quantity'],0); ?></td>
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
        <p>No ongoing orders.</p>
    <?php endif; ?>

    <!-- Completed Orders -->
    <h3>Completed Orders</h3>
    <?php if ($completed_orders): ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($completed_orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date("d M Y H:i", strtotime($order['created_at'])); ?></td>
                        <td>KSh <?php echo number_format($order['total'],0); ?></td>
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
                        <td colspan="5">
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
                                            <td>KSh <?php echo number_format($item['price'],0); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>KSh <?php echo number_format($item['price'] * $item['quantity'],0); ?></td>
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
</div>

<script>
function toggleItems(orderId) {
    const row = document.getElementById('order-items-' + orderId);
    row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
}
</script>

<?php $Objlayout->footer($conf); ?>
