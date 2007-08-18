<?php
// page2.php
	function page_domenu(){
		domenu();
		return 1;
	}
	
	function page_html_header(){
		html_header("nocache");
		return 1;
	}
	
        function page_html_footer(){
		html_footer();
                return 1;
        }
	
	function page_main(){
		echo "<hr>";
		echo "Page #2 served to " . getuserid();
		echo "<p> Session Destroy";
		echo "<hr>";
		session_destroy();
		show_source(__FILE__);
		return 1;
	}
	
	function page_init(){
		return 1;
	}

        function page_cleanup(){
                return 1;
        }

?> 
