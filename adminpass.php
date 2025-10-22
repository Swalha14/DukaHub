<?php
require_once 'ClassAutoLoad.php';

$email = 'admin@dukahub.com';
$newPlain = 'Admin@2025.dukahub';  
$hash = password_hash($newPlain, PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE admin SET password = :password WHERE email = :email");
$stmt->execute([':password' => $hash, ':email' => $email]);

echo "Admin password updated.\n";
?>
