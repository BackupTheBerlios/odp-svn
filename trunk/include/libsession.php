<?php
// libsession.php
// - usually first
//
function start_session_start(){
	session_start(); 
	if (!isset($_SESSION['count'])) {
    		$_SESSION['count'] = 0;
	} else {
	    $_SESSION['count']++;
	}

 	$_SESSION['SCRIPT_URI'] = $_SERVER['SCRIPT_URI'];
	return 1;
}

function restart_session(){
	session_destroy();
	return 1;
}
?>
