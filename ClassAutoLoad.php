<?php
// Load Composer dependencies if needed (PHPMailer, etc.)
 require 'Plugins/PHPMailer/vendor/autoload.php';

require_once 'conf.php';

// Directories where classes are stored
$directories = ['Forms', 'Layout', 'Global'];

// Autoload classes
spl_autoload_register(function ($className) use ($directories) {
    foreach ($directories as $directory) {
        $filePath = __DIR__ . '/' . $directory . '/' . $className . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
            return;
        }
    }
});

/*Create a database connection
$SQL = new dbConnection($conf['db_type'], $conf['db_host'], $conf['db_name'], $conf['db_user'], $conf['db_pass'], $conf['db_port']);
*/

// Create instances
$Objform   = new Forms();
$Objlayout = new layout();
$ObjSendMail= new SendMail();
