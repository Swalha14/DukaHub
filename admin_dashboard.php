<?php
session_start();
require_once 'ClassAutoLoad.php';

//  Restrict access to admins only
if (!isset($_SESSION['admin_id'])) {
    header("Location: signin.php");
    exit();
}


$Objlayout = new layout();
global $conf;

$adminEmail = $_SESSION['admin_email'] ?? 'admin@dukahub.com';


$Objlayout->header($conf);
$Objlayout->nav($conf);
?>


<main class="admin-dashboard">
    <section class="admin-header">
        <h1>Welcome, <?php echo htmlspecialchars($adminEmail); ?></h1>
        <p>You are logged in as <strong>Administrator</strong>.</p>
    </section>

    <section class="admin-content">
        <h2>Dashboard Overview</h2>
        <p>Welcome to your admin control panel. Use the links below to manage users, products, and categories.</p>
    </section>

    <nav class="admin-menu">
        <ul>
            <li><a href="view_users.php">👥 View Users</a></li>
            <li><a href="view_products.php">🛒 View Products</a></li>
            <li><a href="view_categories.php">📦 View Categories</a></li>
            <li><a href="admin_orders.php">📄 View Orders</a></li>
            
        </ul>
    </nav>

    
</main>

<?php

$Objlayout->footer($conf);
?>
