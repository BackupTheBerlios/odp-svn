<?php
// help.php
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
		echo "There should be code here for the page requester";
		echo "<p>";
		$base='/home/groups/nedi/htdocs/odp/';
		echo "<hr>";
		echo " Call this help!!";
		echo "<hr>";
		show_source(__FILE__);
		echo "<hr>";
		show_source($base . 'index.php');
		echo "<hr>";
		$base= $base . 'include/';
                show_source( $base . 'aaa.php');
                echo "<hr>";
                show_source( $base . 'session.php');
                echo "<hr>";
                show_source( $base . 'wwwstuff.php');
                echo "<hr>";

		return 1;
	}
	
	function page_init(){
		require "config/help.php";
		return 1;
	}

        function page_cleanup(){
                return 1;
        }

?> 
