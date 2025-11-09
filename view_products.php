<?php
session_start();
require_once 'ClassAutoLoad.php';

// Make sure global variables from ClassAutoLoad.php are accessible
global $SQL, $conf;

// Verify admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: signin.php");
    exit();
}

// Get PDO connection from dbConnection class
$conn = $SQL->getConnection();

$Objlayout->header($conf);
$Objlayout->nav($conf);

// Handle deletion
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        $stmt->execute();
        echo '<div class="alert alert-success">Product deleted successfully.</div>';
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error deleting product: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?php echo htmlspecialchars($conf['site_name']); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css">
</head>
<body>
<div class="container mt-4">
    <h2>🛒 Products</h2>
    <hr>

    <?php
    try {
        // Fetch only name, description, price
        $stmt = $conn->prepare("SELECT id, name, description, price FROM products ORDER BY id DESC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products) {
            echo '<table id="productsTable" class="table table-striped table-bordered">';
            echo '<thead><tr><th>Name</th><th>Description</th><th>Price</th><th>Action</th></tr></thead><tbody>';
            foreach ($products as $product) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($product['name']) . '</td>';
                echo '<td>' . htmlspecialchars($product['description']) . '</td>';
                echo '<td>' . htmlspecialchars($product['price']) . '</td>';
                echo '<td>
                        <a href="?delete_id=' . $product['id'] . '" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm(\'Are you sure you want to delete this product?\');">
                           Delete
                        </a>
                      </td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No products found.</p>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>

<script>
    $(document).ready(function() {
        new DataTable('#productsTable');
    });
</script>
</body>
</html>

<?php
$Objlayout->footer($conf);
?>
