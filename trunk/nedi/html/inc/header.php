<?php
//===============================
// NeDi header.
//===============================

// Some defaults 

$datfmt = "j.M G:i:s";

ini_set("memory_limit","16M");							# Added 8.1.2007 due to reporting problems on large networks

session_start(); 

$self = preg_replace("/.*\/(.+).php/","$1",$_SERVER['PHP_SELF']);
require_once ('libmisc.php');
if(isset ($_SESSION['group']) ){
	ReadConf($_SESSION['group']);
}else{
	echo "<script>document.location.href='index.php';</script>\n";
	die;
}
require_once ("lang-$_SESSION[lang].php");
require_once ("lib" . strtolower($backend) . ".php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<title>NeDi <?=$self?></title>
<?=(isset($nocache))?"<meta http-equiv=\"cache-control\" content=\"no-cache\">\n":""?>
<?=(isset($refresh))?"<meta http-equiv=\"refresh\" content=\"$refresh;$_SERVER[PHP_SELF]\">\n":""?>
<?=(isset($calendar))?"<script language=\"JavaScript\" src=\"inc/cal.js\"></script>\n":""?>

<link href="inc/<?=$_SESSION[theme]?>.css" type="text/css" rel="stylesheet">
<link rel="shortcut icon" href="img/favicon.ico">
<script language="JavaScript" src="inc/JSCookMenu.js"></script>
<link rel="stylesheet" href="inc/ThemeN/theme.css" TYPE="text/css">
<script language="JavaScript" src="inc/ThemeN/theme.js"></script>
</head>

<body>
<table id="header">
<tr class="<?=$modgroup[$self]?>1">
<th width=80><a href="http://www.nedi.ch"><img src="img/n.png"></a></th>
<td ID="MainMenuID"></td><th width=80><?=$_SESSION['user']?></th></tr></table>

<script language="JavaScript"><!--
var mainmenu = [
<?
	foreach (array_keys($mod) as $m) {
		echo "	[null,'$m',null,null,null,\n";
		foreach ($mod[$m] as $s => $i) {
			echo "		['<img src=./img/16/$i.png>','$s','$m-$s.php',null,null],\n";
		}
		echo "	],\n";
	}
?>
];
cmDraw ('MainMenuID', mainmenu, 'hbr', cmThemeN, 'ThemeN');
--></SCRIPT>
<p>
<?
if( strpos($_SESSION['group'],$modgroup[$self]) === false){
	echo $nokmsg;
	die;
}
?>
