<?php
require_once 'ClassAutoLoad.php';   

if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        die(" All fields are required!");
    }

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            die(" This email is already registered. Please use another one.");
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) 
                                VALUES (:username, :email, :password)");
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $hashedPassword
        ]);

        // Welcome email content
        $mailContent = [
            'name_from'  => $conf['site_name'],
            'email_from' => $conf['smtp_user'],  
            'name_to'    => $username,
            'email_to'   => $email,
            'subject'    => 'Welcome to ' . $conf['site_name'] . ' - Account Verification!', 
            'body'       => "Hello $username,<br><br>
                             Welcome to <b>{$conf['site_name']}</b>!<br>
                             <p>You are receiving this email because you created an account on {$conf['site_name']}.</p>
                             <p>To activate your account, please 
                             <a href='http://localhost/dukahub/verify.php?email=$email'>Click Here</a>.</p>
                             <p>If you did not request this account, you can ignore this email.</p>
                             <br>
                             Regards,<br>{$conf['site_name']} Team"
        ];

        $ObjSendMail->Send_Mail($conf, $mailContent);

        echo " Registration successful! A welcome email has been sent to $email";

    } catch (PDOException $e) {
        die(" Database error: " . $e->getMessage());
    }
} else {
    die(" Invalid request.");
}