<?
/*
#============================================================================
# Program: User-Accounts.php
# Programmer: Remo Rickli
#
# DATE     COMMENT
# -------- ------------------------------------------------------------------
# 10/03/05	initial version
# 10/02/06	pw reset added
# 19/01/07	new Sorting approach
# 30/08/07	implemented CSS scheme
*/

include_once ("inc/header.php");

$_GET = sanitize($_GET);
$ord = isset( $_GET['ord']) ? $_GET['ord'] : "";
$grp = isset( $_GET['grp']) ? $_GET['grp'] : "";

$cols = array(	"name"=>"Name",
		"email"=>"Email",
		"phone"=>"Phone",
		"time"=>"Creation",
		"lastseen"=>"Lastseen",
		"comment"=>"Comment",
		"language"=>"Language",
		);

?>
<h1>User Accounts</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content" ><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>><img src="img/32/user.png"></a></th>
<th>Filter Group
<SELECT size=1 name="grp" onchange="this.form.submit();">
<OPTION VALUE="">None
<OPTION VALUE="adm" <?=($grp == "adm")?"selected":""?> >Admin
<OPTION VALUE="net" <?=($grp == "net")?"selected":""?> >Network
<OPTION VALUE="dsk" <?=($grp == "dsk")?"selected":""?> >Helpdesk
<OPTION VALUE="mon" <?=($grp == "mon")?"selected":""?> >Monitor
<OPTION VALUE="mgr" <?=($grp == "mgr")?"selected":""?> >Manager
<OPTION VALUE="oth" <?=($grp == "oth")?"selected":""?> >Other
</SELECT>
</th>
<th>User
<input type="text" name="usr" size="12">
<input type="submit" name="create" value="Create">
</th>
</table></form>
<p>
<?
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if (isset($_GET['usr']) and isset($_GET['create']) ){
	$now = time();
	$pass = md5( $_GET['usr'] );
	$query	= GenQuery('user','i','','','',array('name','password','time'),'',array($_GET['usr'],$pass,$now) );
	if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>User $_GET[usr] $upokmsg</h3>";}
}elseif(isset($_GET['del']) ){
	$query	= GenQuery('user','d','','','',array('name'),array('='),array($_GET['del']) );
	if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>User $_GET[del] $delokmsg</h3>";}
}elseif(isset($_GET['psw']) ){
	$pass = md5( $_GET['psw'] );
	$query	= GenQuery('user','u','name',$_GET['psw'],'',array('password'),'',array($pass) );
	if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3> $_GET[psw]'s password $upokmsg</h3>";}
}elseif( isset($_GET['grm']) ){
	$query	= GenQuery('user','u','name',$_GET['grm'],'',array($_GET['mgp']),'',array('0') );
	if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3> $_GET[grm] $_GET[mgp] $delokmsg</h3>";}
}elseif(isset($_GET['gad']) ){
	$query	= GenQuery('user','u','name',$_GET['gad'],'',array($_GET['mgp']),'',array('1') );
	if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3> $_GET[gad] $_GET[mgp] $upokmsg</h3>";}
}
?>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<?
ColHead('name');
ColHead('email');
ColHead('phone');
ColHead('comment');
ColHead('language');
ColHead('time');
ColHead('lastseen');
echo "<th>Groups</th><th>Action</th></tr>\n";

if ($grp){
	$query	= GenQuery('user','s','*',$ord,'',array($grp),array('='),array('1') );
}else{
	$query	= GenQuery('user','s','*',$ord );
}
$res	= @DbQuery($query,$link);
if($res){
	$row = 0;
	while( ($u = @DbFetchRow($res)) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		list($cc,$lc) = Agecol($u[10],$u[11],$row % 2);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "<img src=".Smilie($u[0])." title=\"Hello I'm $u[0]\"'><br>$u[0]</th>\n";
		echo "<td>$u[8]</td><td align=center>$u[9]</td><td>$u[12]</td><td align=center>$u[13]</td>\n";
		echo "<td bgcolor=#$cc>".date("j. M Y",$u[10])."</td>\n";
		echo "<td bgcolor=#$lc>".date("j. M (G:i)",$u[11])."</td><th>\n";
		if($u[2]){echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&grm=$u[0]&mgp=adm><img src=\"img/16/cfg.png\" title=-admin></a>";}
		else{echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&gad=$u[0]&mgp=adm><img src=\"img/16/bcls.png\"  title=+admin></a>";}
		if($u[3]){echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&grm=$u[0]&mgp=net><img src=\"img/16/net.png\" title=-net></a>";}
		else{echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&gad=$u[0]&mgp=net><img src=\"img/16/bcls.png\" title=+net></a>";}
		if($u[4]){echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&grm=$u[0]&mgp=dsk><img src=\"img/16/ring.png\" title=-helpdesk></a>";}
		else{echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&gad=$u[0]&mgp=dsk><img src=\"img/16/bcls.png\" title=+helpdesk></a>";}
		if($u[5]){echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&grm=$u[0]&mgp=mon><img src=\"img/16/sys.png\" title=-monitor></a>";}
		else{echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&gad=$u[0]&mgp=mon><img src=\"img/16/bcls.png\" title=+monitor></a>";}
		if($u[6]){echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&grm=$u[0]&mgp=mgr><img src=\"img/16/umgr.png\" title=-manager></a>";}
		else{echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&gad=$u[0]&mgp=mgr><img src=\"img/16/bcls.png\" title=+manager></a>";}
		if($u[7]){echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&grm=$u[0]&mgp=oth><img src=\"img/16/3d.png\" title=-other></a>";}
		else{echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&gad=$u[0]&mgp=oth><img src=\"img/16/bcls.png\" title=+other></a>";}
		echo "</th>\n";
		echo "<th><a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&del=$u[0]><img src=\"img/16/bcnl.png\" title='delete user' onclick=\"return confirm('Delete User $u[0]?')\"></a>\n";
		echo "<a href=$_SERVER[PHP_SELF]?grp=$grp&ord=$ord&psw=$u[0]><img src=\"img/16/key.png\" title='reset password' onclick=\"return confirm('Reset password for $u[0]?')\"></a></th></tr>\n";
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
}
	?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Users (<?=$query?>)</td></tr>
</table>
	<?

include_once ("inc/footer.php");
?>
