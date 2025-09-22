<?php
// Load Composer dependencies if needed (PHPMailer, etc.)
// require 'Plugins/PHPMailer/vendor/autoload.php';

require_once 'conf.php';

// Directories where classes are stored
$directories = ['Forms', 'Layout', 'Global'];

// Autoload classes
spl_autoload_register(function ($className) use ($directories) {
    foreach ($directories as $directory) {
        $filePath = _DIR_ . '/' . $directory . '/' . $className . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
            return;
        }
    }
});

// Create instances
$Objform   = new Forms();
$Objlayout = new layout();
$ObjSendMail= new SendMail();
