<?php //TESTING VIEW ?>


        <!-- ======================================== MOCK ======================================= -->

        <section class="flip-form">
            <div id="deck">
                <figure class="front" id="login">
                    <!-- ===== LOGO ===== -->
                    <div class="login-logo">
                        <div class="logo-image"></div>
                    </div>
                    <!-- ===== FORM ===== -->
                    <div class="login-form">
                        <!-- ===== FORM ALERTS ===== -->
                        <div class="alert alert-error hide">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>There is an issue with your request</h4>
                            Your Error Message goes here
                        </div>
                        <!-- ===== FORM FIELDS ===== -->
                        <form action="#" method="get"  >
                             <input type="text" placeholder="Email" required/>
                             <input type="password"  placeholder="Password" required/>
                             <button type="submit" class="btn btn-login">Login</button>
                        </form>
                        <!-- ===== FORM HELP ===== -->
                        <div class="login-links">
                            <a class="flip-click" href="#reset">Forgot password?</a>
                        </div>
                    </div>
                </figure>
                <figure class="back" id="reset">
                    <!-- ===== LOGO ===== -->
                    <div class="login-logo">
                        <div class="logo-image"></div>
                    </div>
                    <div class="login-form">
                        <!-- ===== FORM ALERTS ===== -->
                        <div class="alert alert-error hide">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>There is an issue with your request</h4>
                            Your Error Message goes here
                        </div>
                        <!-- ===== FORM FIELDS ===== -->
                        <form action="#" method="get">
                            <p>Reset Password.</p>
                            <input type="email" placeholder="Email"/>
                            <button type="submit" class="btn btn-login btn-reset">Reset password</button>
                        </form>
                        <!-- ===== FORM HELP ===== -->
                        <div class="login-links">
                            <a class="flip-click" href="#login"><strong>Back to Login</strong></a>
                        </div>
                    </div>

                </figure>
            </div>
        </section>
