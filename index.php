<?php

require_once 'ClassAutoLoad.php';


$Objlayout->header($conf);
$Objlayout->nav($conf);
$Objlayout->banner($conf);
$Objlayout->form_content($conf, $Objform); // On index, shows welcome text
$Objlayout->categories_section($conn);
$Objlayout->footer($conf);
