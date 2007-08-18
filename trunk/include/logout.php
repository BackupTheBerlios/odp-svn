<?php
// logout.php
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
		echo "Logout " . getuserid();
		echo " <br> need some way to get out of this page......";
		echo "<p> Session Destroy";
		echo "<hr>";
		show_source(__FILE__);
		echo "<hr>";
		return 1;
	}
	
	function page_init(){
		return 1;
	}

        function page_cleanup(){
		session_destroy();
                return 1;
        }

?> 
