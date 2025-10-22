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
                <?php echo $conf['site_name']; ?> is a digital shop platform. 
                Easily connect with the store, explore what we have to offer, 
                and buy what you need.Happy shopping!
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
            } /*else {
                echo "<h2>Explore " . $conf['site_name'] ;  //"</h2><p>Sign up or log in to get started.</p>";
            }*/
            ?>
        </section>
        <?php
    }

    public function categories_section($conn)
{
    try {
        $stmt = $conn->prepare("SELECT * FROM categories");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <section class="categories">
            <h2>Shop by Category</h2>
            <div class="category-grid">
                <?php foreach ($categories as $row): ?>
                    <a href="products.php?category_id=<?php echo htmlspecialchars($row['id']); ?>" class="category-card">
                        <img src="Images/Categories/<?php echo htmlspecialchars($row['image']); ?>" 
                             alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    } catch (PDOException $e) {
        echo "<p>Error loading categories: " . $e->getMessage() . "</p>";
    }
}


    public function footer($conf)
    {
        ?>
        <footer>
           <h5>Contact Us</h5>
        <p>Have questions or need help? Email us at 
            <a href="mailto:admin@dukahub.com">admin@dukahub.com</a>
        </p>

        <hr style="width: 60%; margin: 1rem auto; border: 0; border-top: 1px solid #fff;">


            <p>&copy; <?php echo date("Y"); ?> <?php echo $conf['site_name']; ?>. All rights reserved.</p>
        </footer>
        </body>
        </html>
        <?php
    }
}
?>
