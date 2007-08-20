<?php
//
function getlang(){
	return $_SERVER['HTTP_ACCEPT_ENCODING'];
}

function splash($txt){
#<body onLoad=document.login.user.focus();>
//<html
?>
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
//html>
}


function login(){
		global $logintxt;
	        global $JSMainMenu;

                $reqpage=$_SESSION['reqpage'];

		html_header();
		splash($reqpage . "<br>" . $logintxt);
//<html
?>

<form name="login" method="post" action="<?=$reqpage?>">

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

<?php
//html>
		html_footer();
	}

function html_header($nocache, $refresh_url, $refresh_pause){
        global $bg1;
        global $JSMainMenu;
        global $JSUserMenu;
        global $tabtag;

//<html
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


<table bgcolor=#000000 <?=$tabtag?>>
        <tr bgcolor=#<?="$bg1" ?>>
                <td align=center width=80>
                <a href='http://www.nedi.ch'><img src='img/n.png' border=0 hspace=10 valign=middle></a>
                </td>
                <td ID=MainMenuID></td>
                <td width=80 ID=UserId></td>
        </tr>
</table>
<SCRIPT LANGUAGE="JavaScript"><!--
<?php
//html>
        include $JSMainMenu;
        include $JSUserMenu;
//<html
?>
cmDraw ('MainMenuID', mainmenu, 'hbr', cmThemeN, 'ThemeN');
cmDraw ('UserId', usermenu, 'hbr', cmThemeN, 'ThemeN');
--></SCRIPT>
<?php
//html>

        return 1;

	}

        function html_footer(){
		global $bg1;
		global $footertxt;
//<html
?>
<p>
<table bgcolor=#000000 cellspacing=1 cellpadding=4 width=100% border=0>
<tr bgcolor=#<?php echo "$bg1" ?>>
	<td align=right><font size=-2><?php echo "$footertxt" ?></font></td>
</tr>
</table>
</body>
</html>
<?php
//html>
        }

        function getpageid(){
		//return "page2.php";
		if (  isset( $_GET['pageid']) ){
                	return $_GET['pageid'];
		}
		return null;
        }
	
?>
