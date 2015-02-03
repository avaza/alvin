<?php // foot ARC View : ACE admin GUI footer ?>


        <!-- ======================================== FOOT ======================================= -->

                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.page-content -->
                </div>
            </div><!-- /.main-content -->

            <div class="footer">
                <div class="footer-inner">
                    <!-- #section:basics/footer -->
                    <div class="footer-content">
                        <span class="bigger-120">
                            <span class="blue bolder">AVAZA Language Services Corporation</span>
                            ARC System &copy; 2015
                        </span>
                        &nbsp; &nbsp;
                        <span class="action-buttons">
                            <a href="#"><i class="ace-icon fa fa-twitter-square light-blue bigger-150"></i></a>
                            <a href="#"><i class="ace-icon fa fa-facebook-square text-primary bigger-150"></i></a>
                            <a href="#"><i class="ace-icon fa fa-rss-square orange bigger-150"></i></a>
                        </span>
                    </div>
                </div>
            </div>
            <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse"><i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i></a>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <!--[if !IE]> -->
            <script>window.jQuery || document.write('<script src="/assets/javascripts/vendors/jquery-1.11.1.min.js"><\/script>')</script>
        <!-- <![endif]-->

        <!--[if IE]>
            <script type="text/javascript">
                window.jQuery || document.write("<script src='/assets/javascripts/gui/jquery1x.js'>"+"<"+"/script>");
            </script>
        <![endif]-->

        <script type="text/javascript">
            if('ontouchstart' in document.documentElement) document.write("<script src='/assets/javascripts/gui/jquery.mobile.custom.js'>"+"<"+"/script>");
        </script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>

        <!-- page specific plugin scripts -->

        <!-- ace scripts -->
        <script src="/assets/javascripts/gui/ace/elements.scroller.js"></script>
        <script src="/assets/javascripts/gui/ace/elements.colorpicker.js"></script>
        <script src="/assets/javascripts/gui/ace/elements.fileinput.js"></script>
        <script src="/assets/javascripts/gui/ace/elements.typeahead.js"></script>
        <script src="/assets/javascripts/gui/ace/elements.wysiwyg.js"></script>
        <script src="/assets/javascripts/gui/ace/elements.spinner.js"></script>
        <script src="/assets/javascripts/gui/ace/elements.treeview.js"></script>
        <script src="/assets/javascripts/gui/ace/elements.wizard.js"></script>
        <script src="/assets/javascripts/gui/ace/elements.aside.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.ajax-content.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.touch-drag.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.sidebar.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.sidebar-scroll-1.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.submenu-hover.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.widget-box.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.settings.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.settings-rtl.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.settings-skin.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.widget-on-reload.js"></script>
        <script src="/assets/javascripts/gui/ace/ace.searchbox-autocomplete.js"></script>
        <?php
            if( isset( $javaScripts ) && ! empty( $javaScripts )):
                foreach( $javaScripts as $script ):
                    echo "<script src=\"/assets/javascripts/{$script}\"></script>\n";
                endforeach;
            endif;
        ?>

        <!-- inline scripts related to this page -->

    </body>
</html>