<?php
session_start();
require_once 'ClassAutoLoad.php';
global $conn, $Objlayout, $ObjSendMail;

$conf['site_name'] = 'Dukahub | Forgot Password';
$Objlayout->header($conf);
$Objlayout->nav($conf);

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Please enter your email address.";
        $messageClass = "alert-danger";
    } else {
        $stmt = $conn->prepare("SELECT username FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate a 6-digit reset code and expiration time
            $resetCode = random_int(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $stmt = $conn->prepare("UPDATE users SET reset_code = :code, reset_expires = :expires WHERE email = :email");
            $stmt->execute([
                ':code' => $resetCode,
                ':expires' => $expires,
                ':email' => $email
            ]);

            // Prepare email content using your existing Send_Mail
            $mailContent = [
                'name_from'  => $conf['site_name'],
                'email_from' => $conf['smtp_user'],  
                'name_to'    => $user['username'],
                'email_to'   => $email,
                'subject'    => 'DukaHub Password Reset Code', 
                'body'       => "
                    <h3>Hello {$user['username']},</h3>
                    <p>You requested to reset your Dukahub password.</p>
                    <p>Your 6-digit password reset code is:</p>
                    <h2 style='color:#2c7be5;'>$resetCode</h2>
                    <p>Enter this code on the reset page to set a new password.</p>
                    <p>This code expires in 1 hour.</p>
                    <p>If you did not request this, ignore this email.</p>
                    <br>
                    <p>Regards,<br>{$conf['site_name']} Team</p>"
            ];

            $ObjSendMail->Send_Mail($conf, $mailContent);

            $_SESSION['success'] = "A 6-digit reset code has been sent to your email.";
            header("Location: reset_code.php?email=" . urlencode($email));
            exit();

        } else {
            $message = "No account found with that email.";
            $messageClass = "alert-danger";
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center text-center" style="min-height: 80vh;">
    <div class="card shadow p-5" style="max-width: 500px; border-radius: 20px;">
        <h3 class="mb-3" style="color: #007bff;">Forgot Password</h3>
        <p class="text-muted mb-4">Enter your registered email to receive a reset code.</p>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $messageClass; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" class="form-control mb-3" placeholder="Enter your email" required>
            <button type="submit" class="btn btn-primary w-100">Send Reset Code</button>
        </form>
    </div>
</div>

<?php $Objlayout->footer($conf); ?>
