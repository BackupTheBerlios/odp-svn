<?php
	echo '<hr>';
	$Script_Filename=$_SERVER['SCRIPT_FILENAME'];
	echo 'Script filename: ' . $Script_Filename . '<p>';
	echo '<hr>';
	show_source("$Script_Filename");
	echo '<hr> Debug Script <hr>';
	show_source(__FILE__);
	phpinfo();
?>

