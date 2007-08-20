<?php
//
        function getuserid(){
                if ( isset($_SESSION['authentication']) ){
			return $_SESSION['authentication'];
		}
		return '';
        }
//
	function authenticated(){
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
	function autherized($userid,$pageauth){
		if ( !isset($pageauth) ){
			return 1;
		}

                if ( authenticated() ){
                        return 1;
                }
                return 0;
	}
?>
