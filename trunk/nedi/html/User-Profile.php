<?

/*
#============================================================================
# Program: User-Profile.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 08/03/05	initial version.
# 10/03/06	new SQL query support
# 17/07/07	improved announcements
# 30/08/07	implemented CSS scheme
*/

$msgfile= 'log/msg.txt';

include_once ("inc/header.php");

$name = $_SESSION['user'];
$_POST = sanitize($_POST);
$msg = isset( $_POST['msg']) ? $_POST['msg'] : "";

$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if(isset($_GET['up']) ){
	if($_GET['pass']){
		if($_GET['pass'] == $_GET['vpas']){
			$pass = md5( $_GET['pass'] );
			$query	= GenQuery('user','u','name',$name,'',array('password'),array('='),array($pass) );
			if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>Password $upokmsg</h3>";}
		}else{
			echo "$n1rmsg";
		}
	}
	echo "<h3>";
	$query	= GenQuery('user','u','name',$name,'',array('email'),array('='),array($_GET['email']) );
	if( !@DbQuery($query,$link) ){echo DbError($link);}else{echo "Email, ";}
	$query	= GenQuery('user','u','name',$name,'',array('phone'),array('='),array($_GET['phone']) );
	if( !@DbQuery($query,$link) ){echo DbError($link);}else{echo "Phone, ";}
	$query	= GenQuery('user','u','name',$name,'',array('comment'),array('='),array($_GET['comment']) );
	if( !@DbQuery($query,$link) ){echo DbError($link);}else{echo "Comment, ";}
	$_SESSION['lang'] = $_GET['lang'];
	$query	= GenQuery('user','u','name',$name,'',array('language'),array('='),array($_GET['lang']) );
	if( !@DbQuery($query,$link) ){echo DbError($link);}else{echo "Feedback language, ";}
	$_SESSION['theme'] = $_GET['teme'];
	$query	= GenQuery('user','u','name',$name,'',array('theme'),array('='),array($_GET['teme']) );
	if( !@DbQuery($query,$link) ){echo DbError($link);}else{echo "Theme ";}
	echo " $upokmsg</h3>";
}
$query	= GenQuery('user','s','*','','',array('name'),array('='),array($name) );
$res	= @DbQuery($query,$link);
$uok	= @DbNumRows($res);
if ($uok == 1) {
	$u = @DbFetchRow($res);
}else{
	echo "<h4>user $name doesn't exist! ($uok)</h4>";
	die;
}
?>
<h1>User Profile</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>" name="pro">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>><img src=<?=Smilie($name)?> title="Set your personal information"></a>
<br><?=$name?></th>
<th valign=top align=right>
Password <input type="password" name="pass" size="12"><p>
Verify <input type="password" name="vpas" size="12">
</th>

<th valign=top>Language<p>
<SELECT name="lang" size=2>
<OPTION VALUE="eng" <?=($u[13] == 'eng')?"selected":""?> >English
<OPTION VALUE="ger" <?=($u[13] == 'ger')?"selected":""?> >Deutsch
</SELECT>
</th>
<th valign=top>Theme<p>
<SELECT name="teme" size=2>
<?
if ($dh = opendir("inc/")) {
	while (($f = readdir($dh)) !== false) {
		if( $p = strpos($f, ".css") ){
			$t = substr($f,0,$p);
			echo "<OPTION VALUE=\"$t\" ".(($u[14] == $t)?"selected":"").">$t\n";
		}
	}
	closedir($dh);
}
?>
</SELECT>
</th>
<th valign=top align=right>
Email <input type="text" name="email" size="32" value="<?=$u[8]?>" >
Phone <input type="text" name="phone" size="12" value="<?=$u[9]?>" >
<p>
Comment <input type="text" name="comment" size="50" value="<?=$u[12]?>" >
</th>

</th>
<th width=80><input type="submit" name="up" value="Update"></th>
</tr></table></form>

<h2>Groups</h2>
<table class="content">
<tr class="<?=$modgroup[$self]?>2">
<th>Admin</th><th>Network</th><th>Helpdesk</th><th>Monitoring</th><th>Manager</th><th>Other</th>
<th>Created on</th>
<tr class="txta">
<th><?=($u[2])?"<img src=\"img/32/cfg.png\">":"<img src=\"img/16/bcls.png\">"?></th>
<th><?=($u[3])?"<img src=\"img/32/net.png\">":"<img src=\"img/16/bcls.png\">"?></th>
<th><?=($u[4])?"<img src=\"img/32/ring.png\">":"<img src=\"img/16/bcls.png\">"?></th>
<th><?=($u[5])?"<img src=\"img/32/sys.png\">":"<img src=\"img/16/bcls.png\">"?></th>
<th><?=($u[6])?"<img src=\"img/32/umgr.png\">":"<img src=\"img/16/bcls.png\">"?></th>
<th><?=($u[7])?"<img src=\"img/32/glob.png\">":"<img src=\"img/16/bcls.png\">"?></th>
<th><?=date("j. M Y",$u[10])?></th>
</tr></table><p>

<?
if($u[2]){
	if(isset($_POST['cam']) ){
		unlink($msgfile);
	}elseif(isset($_POST['sam']) ){
		$fh = fopen($msgfile, 'w') or die("Cannot write $msgfile!");
		fwrite($fh, "$msg");
		fclose($fh);
	}
	if(isset($_GET['eam']) ){
?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" name="ano">
<table class="content">
<tr class="warn">
<th width="80">
<input type="button" value="Bold" OnClick='document.ano.msg.value = document.ano.msg.value + "<b></b>"';>
<p>
<input type="button" value="Italic" OnClick='document.ano.msg.value = document.ano.msg.value + "<i></i>"';>
<p>
<input type="button" value="Pre" OnClick='document.ano.msg.value = document.ano.msg.value + "<pre></pre>"';>
<p>
<input type="button" value="Break" OnClick='document.ano.msg.value = document.ano.msg.value + "<br>\n"';>
<p>
<input type="button" value="Title" OnClick='document.ano.msg.value = document.ano.msg.value + "<h2></h2>\n"';>
<p>
<input type="button" value="List" OnClick='document.ano.msg.value = document.ano.msg.value + "<ul>\n<li>\n<li>\n</ul>\n"';>
</th><th>
<textarea rows="16" name="msg" cols="100">
<?
	if (file_exists($msgfile)) {
		readfile($msgfile);
	};
?>
</textarea>
</th>
<th width="80">
<input type="submit" name="cam" value="Clear">
<p>
<input type="submit" name="sam" value="Save">
</th></table>
<?
	}else{
	?>
	<div align="center"><a href="?eam=1"><img src="img/16/tab.png" title="Edit Admin Message"></a></div>
	<?
	}
}
if (file_exists('log/msg.txt')) {
	echo "<h2>Admin Message</h2><div class=\"textpad warn\">\n";
	include_once ($msgfile);
	echo "</div>";
}
include_once ("inc/footer.php");
?>
