<?php
session_start();
require_once 'ClassAutoLoad.php';


global $conn;


if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['signin'])) {
    header("Location: signin.php");
    exit();
}


$email = trim($_POST['email']);
$password = trim($_POST['password']);

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Please fill in all fields.";
    header("Location: signin.php");
    exit();
}

try {
    //Admin check
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['role'] = 'admin';
        $_SESSION['logged_in'] = true;

        header("Location: admin_dashboard.php");
        exit();
    }

    // Regular user check
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: signin.php");
        exit();
    }

    // Check if user is verified
    if ($user['is_verified'] != 1) {
        header("Location: verify_2fa.php?email=" . urlencode($email));
        exit();
    }

    //start user session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = 'user';
    $_SESSION['logged_in'] = true;

    header("Location: index.php");
    exit();

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    $_SESSION['error'] = "A database error occurred. Please try again later.";
    header("Location: signin.php");
    exit();
}
?>
