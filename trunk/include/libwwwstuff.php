<?php
	function getlang(){
		return $_SERVER['HTTP_ACCEPT_ENCODING'];
	}

	function splash($txt){
?>
	<html>
<head><title>NeDi - on the way to version 2</title>
<link href="css/style.css" type=text/css rel=stylesheet>
<link rel="shortcut icon" href="img/favicon.ico">
</head>
<body onLoad=document.login.user.focus();>
<p>
<table border=0 cellspacing=1 cellpadding=8 bgcolor=#000000 width=50% align=center>
	<tr>
		<td align=center colspan=3 background=img/blubg.png>
		<a href='http://www.nedi.ch'><img src=img/nedib.png border=0>
		</td>
	</tr>
	<tr bgcolor=#D0D0D0>
		<th align=center colspan=3>
		<img src=img/nedie.jpg border=0>
		<p><hr>
		<?=$txt?>
		</th>
	</tr>
</table>

<?php
}

function form_login(){
?>

<form name="login" method="post" action="<?=$_SESSION['reqpage']?>">

<table border=0 cellspacing=1 cellpadding=8 bgcolor=#000000 width=50% align=center>
	<tr>
		<th background=img/blubg.png>
		User <input type="text" name="user" size="12">
		</th>
		<th background=img/blubg.png>
		Pass <input type="password" name="pass" size="12">
		</th>
		<th background=img/blubg.png>
		<input type="submit" value="Login">
		</th>
	</tr>
</table>
</form>
</body>
<?php
	}

	function html_header($nocache, $refresh_url, $refresh_pause){
?>

<html>
<head>
<title>NeDi </title>
<?=($nocache)?"<META HTTP-EQUIV=\"CACHE-CONTROL\" CONTENT=\"NO-CACHE\">\n":""?>
<?=($refresh_url)?"<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"$refresh_pause;$refresh_url\">\n":""?>

<link href='css/style.css' type=text/css rel=stylesheet>

<link rel='shortcut icon' href='img/favicon.ico'>

<SCRIPT LANGUAGE='JavaScript' SRC='JS/JSCookMenu/JSCookMenu.js'></SCRIPT>
<LINK REL='stylesheet' HREF='JS/JSCookMenu/ThemeN/theme.css' TYPE='text/css'>
<SCRIPT LANGUAGE='JavaScript' SRC='JS/JSCookMenu/ThemeN/theme.js'></SCRIPT>

</head>

<body <?=$btag?>>

<?php	
	}

        function html_footer(){
?>
</body>
</html>
<?php
        }

        function getpageid(){
		//return "page2.php";
		if (  isset( $_GET['pageid']) ){
                	return $_GET['pageid'];
		}
		return null;
        }
	
	function domenu(){
// Clip and paste from nedi ...

global $bg1;

$lang   = 'eng';
$datfmt = "j.M G:i:s";

$bga    = "D0D0D0";
$bgb    = "C0C0C0";
$bia    = "F0F0F0";
$bib    = "E6E6E6";

$tabtag = "cellspacing=1 cellpadding=6 border=0 width=100%";

// JS for domenu();
// should be static, built by admin when necessary via php and required: waste of time making it in run time ...
// also easier to debug :)
?>

<table bgcolor=#000000 <?=$tabtag?>>
	<tr bgcolor=#<?="$bg1" ?>>
		<td align=center width=80>
		<a href='http://www.nedi.ch'><img src='img/n.png' border=0 hspace=10 valign=middle></a>
		<a href='http://developer.berlios.de/users/oxo'>ODP</a>
		</td>
		<td ID=MainMenuID></td>
		<th width=80>
		<?=getuserid()?>
		</th>
	</tr>
</table>

<SCRIPT LANGUAGE="JavaScript"><!--
var mainmenu = [
  	[
		null,'Realtime',null,null,null,
          		['<img src=./img/16/flop.png>','Ping','index.php?pageid=nettools.php&tool=ping',null,null],
	],
	[
		null,'User',null,null,null,
          		['<img src=./img/16/user.png>','Logout','index.php?pageid=logout.php',null,null],
 	],
  	[
		null,'Help',null,null,null,
          	['<img src=./img/16/wglb.png>','Developement','index.php?pageid=help.php',null,null],
          	['<img src=./img/16/wglb.png>','Debug','index.php?pageid=debug.php',null,null],
          	['<img src=./img/16/user.png>','User','index.php?pageid=help.php',null,null],
	],
];
cmDraw ('MainMenuID', mainmenu, 'hbr', cmThemeN, 'ThemeN');
--></SCRIPT>

<?php

		return 1;
	}

?>
