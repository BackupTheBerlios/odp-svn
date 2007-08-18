<?php
// nettools.php
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
		// include class
		require ("Net/Ping.php");

		// create object
		$ping = Net_Ping::factory();

		// ping host and display response
		echo "<hr>";
		echo "<pre>";
		if(!PEAR::isError($ping)) 
		{
		   	$response = $ping->ping('w3c.org');
			print_r($response);
			print_r($ping);
		}
		echo "</pre>";

// include class file
include("Net/DNS.php");

// create object
$ndr = new Net_DNS_Resolver();

// uncomment this for debug output
$ndr->debug = 1;

// query for IP address
$answer = $ndr->search("cnet.com", "A");

// print output
print_r($answer);

		echo "<hr>";
		show_source(__FILE__);
		echo "<hr>";
		return 1;
	}
	
	function page_init(){
		require 'config/nettools.php';
		return 1;
	}

        function page_cleanup(){
                return 1;
        }

?> 
