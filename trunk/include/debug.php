<?php
// help.php
	function page_html_header(){
		html_header("nocache");
		return 1;
	}
	
        function page_html_footer(){
		html_footer();
                return 1;
        }
	
	function page_main(){
		topform();

	$txt=show_source(__FILE__);	
        tableit($txt);
        echo '<hr> Debug Script <hr>';
        show_source(__FILE__);
        phpinfo();

		return 1;
	}
	
	function page_init(){
		return 1;
	}

        function page_cleanup(){
                return 1;
        }

?> 
