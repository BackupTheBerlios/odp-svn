<?php
function start_session_start(){
	session_start(); 
	if (!isset($_SESSION['count'])) {
    		$_SESSION['count'] = 0;
	} else {
	    $_SESSION['count']++;
	}

 	$_SESSION['reqpage'] = $_SERVER['REQUEST_URI'];
	return 1;
}

function restart_session(){
	session_destroy();
	return 1;
}
?>
