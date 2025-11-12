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

// Get PDO connection
$conn = $SQL->getConnection();

$Objlayout->header($conf);
$Objlayout->nav($conf);

// Handle deletion
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        $conn->beginTransaction();

        // Delete all orders belonging to the user first
        $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        $stmt->execute();

        // Now delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        $stmt->execute();

        $conn->commit();

        echo '<div class="alert alert-success text-center mt-3">
                ✅ User and related orders deleted successfully. Refreshing...
              </div>';
        echo '<meta http-equiv="refresh" content="2;url=view_users.php">';
    } catch (PDOException $e) {
        $conn->rollBack();
        echo '<div class="alert alert-danger text-center mt-3">
                ❌ Error deleting user: ' . htmlspecialchars($e->getMessage()) . '
              </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users - <?php echo htmlspecialchars($conf['site_name']); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css">
</head>
<body>
<div class="container mt-4">
    <h2>👥 Registered Users</h2>
    <hr>

    <?php
    try {
        // Fetch users
        $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($users) {
            echo '<table id="usersTable" class="table table-striped table-bordered align-middle">';
            echo '<thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                  </thead><tbody>';

            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                echo '<td>
                        <a href="?delete_id=' . $user['id'] . '" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm(\'Are you sure you want to delete this user and all their orders?\');">
                           Delete
                        </a>
                      </td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p class="text-muted">No registered users found.</p>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger mt-3">
                Database Error: ' . htmlspecialchars($e->getMessage()) . '
              </div>';
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
        new DataTable('#usersTable');
    });
</script>
</body>
</html>

<?php
$Objlayout->footer($conf);
?>
