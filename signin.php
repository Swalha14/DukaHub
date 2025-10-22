<?php
session_start();
require_once 'ClassAutoLoad.php';


$Objlayout->header($conf);
$Objlayout->nav($conf);
?>

<?php if (isset($_SESSION['error'])): ?>
    <p class="error"><?= htmlspecialchars($_SESSION['error']); ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php

$Objlayout->form_content($conf, $Objform);


$Objlayout->footer($conf);
?>
