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
		echo "<p>";
		echo " Call this help!!";
		echo "<hr>";
		show_source(__FILE__);
		echo "<hr>";
		return 1;
	}
	
	function page_init(){
		return 1;
	}

        function page_cleanup(){
                return 1;
        }

?> 
