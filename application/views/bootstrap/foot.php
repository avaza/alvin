<?php // Basic Boilerplate Footer for Alvin Views ?>


    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="/assets/javascripts/vendors/jquery-1.11.1.min.js"><\/script>')</script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-placeholder/2.0.8/jquery.placeholder.min.js"></script>
    <script src="/assets/javascripts/all.js"></script>
    <?php
        if( isset( $javaScripts ) && ! empty( $javaScripts )):
            foreach( $javaScripts as $script ):
                echo "<script src=\"/assets/javascripts/{$script}\"></script>\n";
            endforeach;
        endif;
    ?>
    </body>
</html>