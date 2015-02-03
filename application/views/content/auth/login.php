<?php // Login/Authentication View  $messages
    $messages = isset( $messages ) ? $messages : null;
?>


        <!-- ======================================== LOGIN ======================================= -->
        <div class="row flipper">
            <div class="front" id="login">
                <div class="logo-login">
                    <div class="logo-image"></div>
                </div>
                <div class="login-form">
                    <?php printMessages( $messages ); ?>
                    <?php echo form_open('auth/login') . "\n"; ?>
                        <?php echo form_input([ 'type' => 'email', 'name' =>'email', 'placeholder' => 'Email', 'required' => '' ]) . "\n"; ?>
                        <?php echo form_password('password', '', ' placeholder="Password" required=""') . "\n"; ?>
                        <button type="submit" class="btn btn-login">Login</button>
                    <?php echo form_close() . "\n"; ?>
                    <div class="login-links">
                        <a role="button" class="flip-click">Forgot password ?</a>
                    </div>
                </div>
            </div>
            <div class="back">
                <div class="logo-login">
                    <div class="logo-image"></div>
                </div>
                <div class="login-form">
                    <?php echo form_open('auth/reset') . "\n"; ?>
                        <p>Please provide your email address.</p>
                        <?php echo form_input(['type' => 'email', 'name' =>'email', 'placeholder' => 'Email', 'required' => '' ]) . "\n"; ?>
                        <button type="submit" class="btn btn-login btn-reset">Reset Password</button>
                    <?php echo form_close(); ?>
                    <div class="login-links">
                        <a role="button" class="flip-click">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>

        <footer class="container">
                <p id="footer-text"><small>Copyright &copy; 2015 <a href="http://www.avaza.co/">AVAZA</a> Language Services Corporation</small></p>
        </footer>
