<?php

class Forms
{
    public function signup()
    {
        ?>
        
        <form action="signup_process.php" method="post" class="dukahub-form">
            
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <?php
            $this->submit_btn("Sign Up", "signup");
            ?>
            <p>
                <a href="signin.php">Already have an account? Log in</a>
            </p>
        </form>
        <?php
    }

    private function submit_btn($value, $name)
    {
        ?>
        <button type="submit" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
            <?php echo $value; ?>
        </button>
        <?php
    }

    public function signin()
    {
        ?>
        
        <form action="signin_process.php" method="post" class="dukahub-form">
            
            <label for="signin_email">Email:</label>
            <input type="email" id="signin_email" name="email" required>
            
            <label for="signin_password">Password:</label>
            <input type="password" id="signin_password" name="password" required>
            
            <?php
            $this->submit_btn("Sign In", "signin");
            ?>
            <p>
                <a href="signup.php">Don’t have an account? Sign up</a>
            </p>
        </form>
        <?php
    }
}

?>