<?php
session_start();
require_once 'ClassAutoLoad.php';
global $conn, $Objlayout;

$email = isset($_GET['email']) ? $_GET['email'] : '';
if (empty($email)) die("Invalid request.");

$conf['site_name'] = 'Dukahub | Reset Password';
$Objlayout->header($conf);
$Objlayout->nav($conf);

$message = '';
$messageClass = '';
$showPasswordForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: Verify reset code
    if (isset($_POST['reset_code'])) {
        $code = trim($_POST['reset_code']);

        $stmt = $conn->prepare("SELECT reset_code, reset_expires FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['reset_code'] == $code && $user['reset_expires'] > date("Y-m-d H:i:s")) {
            $showPasswordForm = true;
            $_SESSION['verified_email'] = $email;
        } else {
            $message = "Invalid or expired reset code.";
            $messageClass = "alert-danger";
        }

    // Step 2: Reset password
    } elseif (isset($_POST['new_password'])) {
        $newPass = trim($_POST['new_password']);
        $confirmPass = trim($_POST['confirm_password']);
        $email = $_SESSION['verified_email'] ?? '';

        if ($newPass !== $confirmPass) {
            $message = "Passwords do not match.";
            $messageClass = "alert-danger";
            $showPasswordForm = true;
        } elseif (strlen($newPass) < 6) {
            $message = "Password must be at least 6 characters.";
            $messageClass = "alert-danger";
            $showPasswordForm = true;
        } else {
            $hashed = password_hash($newPass, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = :password, reset_code = NULL, reset_expires = NULL WHERE email = :email");
            $stmt->execute([
                ':password' => $hashed,
                ':email' => $email
            ]);

            unset($_SESSION['verified_email']);
            $message = "Password successfully reset! You can now <a href='signin.php' style='color:#007bff;'>log in</a>.";
            $messageClass = "alert-success";
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center text-center" style="min-height: 80vh;">
    <div class="card shadow p-5" style="max-width: 500px; border-radius: 20px;">
        <h3 class="mb-3" style="color: #007bff;">Reset Password</h3>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $messageClass; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!$showPasswordForm): ?>
            <p class="text-muted mb-3">Enter the 6-digit code sent to your email:</p>
            <form method="POST">
                <input type="text" name="reset_code" maxlength="6" class="form-control text-center mb-3" placeholder="Enter code" required>
                <button type="submit" class="btn btn-primary w-100">Verify Code</button>
            </form>
        <?php else: ?>
            <p class="text-muted mb-3">Enter your new password below:</p>
            <form method="POST">
                <input type="password" name="new_password" class="form-control mb-3" placeholder="New password" required>
                <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm password" required>
                <button type="submit" class="btn btn-success w-100">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php $Objlayout->footer($conf); ?>
