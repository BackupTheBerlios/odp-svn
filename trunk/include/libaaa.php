<?php
//
        function getuserid(){
                if ( isset($_SESSION['authentication']) ){
			return $_SESSION['authentication'];
		}else{
			return '';
		}
		return '';
        }
//
	function authenticate(){
                if ( isset($_SESSION['authentication']) ){
                        return 1;
                }else{
                        if ( isset($_SERVER['PHP_AUTH_USER']) ){
                                $_SESSION['authentication'] = $_SERVER['PHP_AUTH_USER'];
                                return 1;
                        }else{
// need sanatize POST
                                if( isset( $_POST['user'])  ){
                                        $_SESSION['authentication'] = $_POST['user'];
                                        return 1;
                                }else{
                                        return 0;
                                }
                                return 0;
                        }
                        return 0;
                }
                return 0;
	}
//
	function authenticated(){
                if ( authenticate() ){
                        return 1;
                }else{
                        return 0;
                }
	}
//
	function autherized(){
                if ( authenticated() ){
                        return 1;
                }else{
                        return 0;
                }


	}
?>
