<?php
// logout.php
	function page_domenu(){
		return 1;
	}
	
	function page_html_header(){
		return 1;
	}
	
        function page_html_footer(){
                return 1;
        }
	
	function page_main(){
		session_destroy();
		echo "<script>document.location.href='index.php';</script>\n";
		return 1;
	}
	
	function page_init(){
	}

        function page_cleanup(){
                return 1;
        }

?> 
