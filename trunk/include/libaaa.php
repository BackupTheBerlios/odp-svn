<?php
//
        function getuserid(){
                if ( authenticated() ){
			return $_SESSION['authentication'];
		}else{
			return '';
		}
        }
//
	function authenticate(){
//
// watch out: limit nr of tries or else be locked out by PAM ...

		if (  isset($_SERVER['PHP_AUTH_USER']) ){
	        	$_SESSION['authentication'] = $_SERVER['PHP_AUTH_USER'];
			return 1;
		}else{
// need sanatize POST
			if( isset( $_POST['user'])  ){
				$_SESSION['authentication'] = $_POST['user'];
				return 1;
			}else{
				return 0;
			};
			return 0;
		}
                return 0;
	}
//
	function authenticated(){
		if ( isset( $_SESSION['authentication'] ) ){
			return 1;
		}else{
			return 0;
		}
	}
//
	function autherized(){
		return 1;
	}
?>
