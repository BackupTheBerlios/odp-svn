<?php
$a=file('/etc/passwd');
foreach ($a as $line)
{
	echo $line;
}
?>
