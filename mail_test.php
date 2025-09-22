
<?php
require_once 'ClassAutoLoad.php'; // autoloader should load $conf and SendMail

$mailContent = [
    'name_from'  => 'Dukahub',
    'email_from' => 'no-reply@dukahub.com',
    'name_to'    => 'Test User',
    'email_to'   => 'receiver@example.com',
    'subject'    => 'Welcome to Dukahub',
    'body'       => '<h3>Welcome to Dukahub!</h3><p>Your digital shop hub is ready 🚀.</p>'
];

// Send mail
$ObjSendMail->Send_Mail($conf, $mailContent);
