<?php
require_once 'ClassAutoLoad.php';

$email = isset($_GET['email']) ? $_GET['email'] : '';

if (empty($email)) {
    die("No email provided for verification.");
}

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['verification_code']);

    if (empty($code)) {
        $message = 'Please enter the verification code.';
        $messageClass = 'alert-danger';
    } else {
        $stmt = $conn->prepare("SELECT verification_code FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $message = 'Invalid email address.';
            $messageClass = 'alert-danger';
        } elseif ($user['verification_code'] == $code) {
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = :email");
            $stmt->execute([':email' => $email]);

            $message = '✅ Your account has been successfully verified! You can now log in.';
            $messageClass = 'alert-success';
        } else {
            $message = '❌ Incorrect code. Please try again.';
            $messageClass = 'alert-danger';
        }
    }
}

$conf['site_name'] = 'Dukahub | Verify Account';

$Objlayout->header($conf);
$Objlayout->nav($conf);
?>

<!-- Main Content -->
<div class="container d-flex justify-content-center align-items-center text-center" style="min-height: 85vh;">
    <div class="card shadow p-5" style="max-width: 500px; width: 100%; border-radius: 20px;">
        <h1 style="color: #007bff; font-weight: 800; font-size: 32px; margin-bottom: 15px;">
            Welcome to Dukahub
        </h1>

        <p class="text-muted" style="font-size: 16px;">
            We’ve sent a <strong>6-digit verification code</strong> to your email:
        </p>

        <p style="font-size: 17px; font-weight: 600; color: #333; margin-bottom: 10px;">
            <?php echo htmlspecialchars($email); ?>
        </p>

        <p style="font-size: 15px; color: #555; margin-bottom: 20px;">
            Please enter the code below to verify your account.
        </p>

        <?php if (!empty($message)): ?>
    <?php if ($messageClass === 'alert-success'): ?>
        <!-- Blue success banner -->
        <div style="
            background-color: #007bff;
            color: white;
            padding: 18px 22px;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
            font-size: 17px;
        ">
            ✅ Your account has been successfully verified! 
            You can now 
            <a href='signin.php' style='color: #fff; text-decoration: underline; font-weight: 700;'>
                log in
            </a>.
        </div>
    <?php else: ?>
        <!-- Default error alert -->
        <div class="alert <?php echo $messageClass; ?> mt-2 mb-3" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>



        <form method="POST">
            <div class="mb-3">
                <input type="text" name="verification_code" 
                       class="form-control text-center" 
                       placeholder="Enter 6-digit code" 
                       maxlength="6" 
                       style="font-size: 20px; letter-spacing: 4px; padding: 14px; border-radius: 12px;"
                       required>
            </div>
            <button type="submit" class="btn btn-primary w-100" 
                    style="font-weight: 700; padding: 12px; border-radius: 12px;">
                Verify Account
            </button>
        </form>
    </div>
</div>

<?php
$Objlayout->footer($conf);
?>
