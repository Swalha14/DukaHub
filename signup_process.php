<?php
require_once 'ClassAutoLoad.php';   

if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        die("All fields are required!");
    }

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            die("This email is already registered. Please use another one.");
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Generate a 6-digit 2FA verification code
        $verification_code = random_int(100000, 999999);

        // Insert user (with verification code and status)
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, verification_code, is_verified) 
                                VALUES (:username, :email, :password, :verification_code, 0)");
        $stmt->execute([
            ':username'          => $username,
            ':email'             => $email,
            ':password'          => $hashedPassword,
            ':verification_code' => $verification_code
        ]);

        // Send email with the 2FA code
        $mailContent = [
            'name_from'  => $conf['site_name'],
            'email_from' => $conf['smtp_user'],  
            'name_to'    => $username,
            'email_to'   => $email,
            'subject'    => 'Verify Your Dukahub Account - 2FA Code', 
            'body'       => "
                <h3>Hello $username,</h3>
                <p>Welcome to <b>{$conf['site_name']}</b>!</p>
                <p>Your 6-digit verification code is:</p>
                <h2 style='color:#2c7be5;'>$verification_code</h2>
                <p>Please enter this code on the verification page to activate your account.</p>
                <p>If you did not create this account, you can safely ignore this email.</p>
                <br>
                <p>Regards,<br>{$conf['site_name']} Team</p>"
        ];

        $ObjSendMail->Send_Mail($conf, $mailContent);

        // Redirect to verification page with email in URL
        header("Location: verify_2fa.php?email=" . urlencode($email));
        exit();

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}
?>
