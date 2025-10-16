<?php

require_once 'ClassAutoLoad.php';


$Objlayout->header($conf);
$Objlayout->nav($conf);
$Objlayout->form_content($conf, $Objform); // On signin, calls $Objform->signin()
$Objlayout->footer($conf);