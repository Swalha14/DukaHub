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
?>

<link rel="stylesheet" href="CSS/profile.css">

<div class="profile-container">
    <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>

    <h3>Your Order History</h3>

    <?php if ($orders): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment Status</th>
                    <th>Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date("d M Y H:i", strtotime($order['created_at'])); ?></td>
                        <td>KSh <?php echo number_format($order['total'], 0); ?></td>
                        <td class="status <?php echo strtolower($order['payment_status']); ?>">
                            <?php echo htmlspecialchars($order['payment_status']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($order['transaction_id'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You haven’t placed any orders yet.</p>
    <?php endif; ?>
</div>

<?php $Objlayout->footer($conf); ?>
