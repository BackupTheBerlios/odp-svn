<?php
// secret.php
	function page_html_header(){
		html_header("nocache");
		return 1;
	}
	
        function page_html_footer(){
		html_footer();
                return 1;
        }
	
	function page_main(){
		echo "you where asked ...";
		return 1;
	}
	
	function page_init(){
		return 1;
	}

        function page_cleanup(){
                return 1;
        }

?> 
