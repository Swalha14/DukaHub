<?php

require_once 'ClassAutoLoad.php';


$Objlayout->header($conf);
$Objlayout->nav($conf);
$Objlayout->form_content($conf, $Objform); // On signup, calls $Objform->signup()
$Objlayout->footer($conf);