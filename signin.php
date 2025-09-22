<?php
// Include the ClassAutoLoad
require_once 'ClassAutoLoad.php';

// Build the page
$Objlayout->header($conf);
$Objlayout->nav($conf);
$Objlayout->form_content($conf, $Objform); // On signin, calls $Objform->signin()
$Objlayout->footer($conf);