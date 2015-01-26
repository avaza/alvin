<?php //LOGIN VIEW ?>


        <!-- ======================================== LOGIN ======================================= -->
        <div class="row" id="flip-auth">
            <div class="col-sm-6 col-md-4 col-sm-offset-3 col-md-offset-4">
                <div class="flip-container">
                    <div class="flipper">
                        <div class="front" id="login">
                            <!-- ===== LOGO ===== -->
                            <div class="logo-login">
                                <div class="logo-image"></div>
                            </div>
                            <!-- ===== FORM ===== -->
                            <div class="login-form">
                                <!-- ===== FORM ALERTS ===== -->
                                <?php

                                if($this->session->flashdata('message'))
                                {
                                    echo "<div class=\"alert alert-error\">
                                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
                                            <h4>There is an issue with your request</h4>
                                            {$this->session->flashdata('message')}
                                        </div>\n";
                                }
                                else
                                {
                                    echo "<!-- NONE -->\n";
                                }

                                ?>
                                <!-- ===== FORM FIELDS ===== -->
                                <form action="/auth/authenticate" method="post">
                                     <input type="text" placeholder="Email" required/>
                                     <input type="password"  placeholder="Password" required/>
                                     <button type="submit" class="btn btn-login">Login</button>
                                </form>
                                <!-- ===== FORM HELP ===== -->
                                <div class="login-links">
                                    <a role="button" class="flip-click">Forgot password ?</a>
                                </div>
                            </div>
                        </div>
                        <div class="back">
                            <!-- ===== LOGO ===== -->
                            <div class="logo-login">
                                <div class="logo-image"></div>
                            </div>
                            <!-- ===== FORM ===== -->
                            <div class="login-form">
                                <!-- ===== FORM ALERTS ===== -->
                                <?php if($this->session->flashdata('message'))
                                {
                                    echo "<div class=\"alert alert-error hide\">
                                        <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
                                        <h4>There is an issue with your request</h4>
                                        {$this->session->flashdata('message')}
                                        </div>\n";
                                }?>
                                <!-- ===== FORM FIELDS ===== -->
                                <form action="/auth/reset" method="post">
                                    <p>Provide your email address, and a reset link will be sent to you.</p>
                                    <input type="email" placeholder="Email"/>
                                    <button type="submit" class="btn btn-login btn-reset">Reset password</button>
                                </form>
                                <!-- ===== FORM HELP ===== -->
                                <div class="login-links">
                                    <a role="button" class="flip-click">Back to Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
