<?php
class layout
{
    public function header($conf)
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo $conf['site_name']; ?></title>
            <link rel="stylesheet" href="CSS/style.css">
            <script src="JS/script.js" defer></script>
        </head>
        <body>
        <?php
    }

    public function nav($conf)
    {
        ?>
        <div class="banner">
            <h1><?php echo $conf['site_name']; ?></h1>
            <nav>
                <ul>
                    <li><a href="./">Home</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                    <li><a href="signin.php">Sign In</a></li>
                </ul>
            </nav>
        </div>
        <?php
    }

    public function banner($conf)
    {
        ?>
        <section class="hero">
            <h2>Welcome to <?php echo $conf['site_name']; ?></h2>
            <p>
                <?php echo $conf['site_name']; ?> is your digital shop hub. 
                Easily connect buyers and sellers, explore community shops, 
                and manage your store with simplicity and speed.
            </p>
        </section>
        <?php
    }

    public function form_content($conf, $Objform)
    {
        ?>
        <section class="content">
            <?php
            if (basename($_SERVER['PHP_SELF']) == 'signup.php') {
                $Objform->signup();
            } elseif (basename($_SERVER['PHP_SELF']) == 'signin.php') {
                $Objform->signin();
            } else {
                echo "<h2>Explore " . $conf['site_name'] . "</h2><p>Sign up or log in to get started.</p>";
            }
            ?>
        </section>
        <?php
    }

    public function footer($conf)
    {
        ?>
        <footer>
            <p>&copy; <?php echo date("Y"); ?> <?php echo $conf['site_name']; ?> - All Rights Reserved</p>
        </footer>
        </body>
        </html>
        <?php
    }
}
?>
