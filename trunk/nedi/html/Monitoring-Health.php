<?
/*
#============================================================================
# Program: Monitoring-Health.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 08/06/05	initial version.
# 10/03/06	new SQL query support
# 17/04/07	extended monitoring
# 12/07/07	new location and function scheme
# 04/09/07	implemented CSS scheme
*/

error_reporting(E_ALL ^ E_NOTICE);

$refresh= 60;

include_once ("inc/header.php");
include_once ('inc/libdev.php');
include_once ('inc/libmon.php');

$_GET = sanitize($_GET);
$reg = isset($_GET['reg']) ? $_GET['reg'] : "";
$cty = isset($_GET['cty']) ? $_GET['cty'] : "";
$bld = isset($_GET['bld']) ? $_GET['bld'] : "";

?><h1>Monitoring Health</h1><?
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('monitoring');
$res	= @DbQuery($query,$link);
if($res){
	$nmon= 0;
	$mal = 0;
	$lck = 0;
	while( ($m = @DbFetchRow($res)) ){
		$deval[$m[0]] = $m[1];
		if($m[1]){$mal++;}
		if($m[5] > $lck){$lck = $m[5];}
		$nmon++;
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
}
?>
<table class="content fixed"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/neth.png"" title="Health at a glance.">
</a></th>
<th valign=top>
<h3>Uptime Polling</h3><p>
<img src="img/32/dev.png"" title="Polling <?=$nmon?> devices"><?
if($mal == 0){
	echo "<img src=\"img/32/bchk.png\" title=\"Last check ".date($datfmt,$lck)."\">";
}else{
	if($mal == 1){
		echo "<img src=\"img/32/bomb.png\" title=\"1 active incident\">";
		echo "<embed src=inc/alarm1.mp3 volume=100 hidden=true>\n";
	}elseif($mal < 10){
		echo "<img src=\"img/32/impt.png\" title=\"$mal active incidents\">";
		echo "<embed src=inc/alarm2.mp3 volume=100 hidden=true>\n";
	}else{
		echo "<img src=\"img/32/bstp.png\" title=\"$mal active incidents!\">";
		echo "<embed src=inc/alarm3.mp3 volume=100 hidden=true>\n";
	}
	
?>
<p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th><img src="img/16/dev.png"><br>Device</th><th><img src="img/16/clock.png"><br>Downtime</th>
<?
	$row = 0;
	foreach(array_keys($deval) as $d){
		if($deval[$d]){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			list($statbg,$stat) = StatusBg(1,1,$deval[$d],$bi);
			$ud	= rawurlencode($d);
			echo "<tr class=\"$bg\"><th>\n";
			echo "<a href=Devices-Status.php?dev=$ud&shg=on&shp=on>$d</a></th><td class=\"$statbg\">$stat</td></tr>\n";
		}
	}
	echo "</table>\n";	
}
?>
</td><th valign=top>
<h3>Interface Traffic</h3><p>
<?
StatusIf('it');
StatusIf('ot');
?>
</th><th valign=top>
<h3>Interface Errors</h3><p>
<?
StatusIf('ie');
StatusIf('oe');
?>
</th><th valign=top>
<h3>System</h3><p>

<?
StatusSys('cpu');
StatusSys('mem');
StatusSys('tmp');
?>
</th></tr></table>
<p>

<table class="full"><tr><td  width=20% class="helper">

<h2>Message Statistics</h2>
<?
$query	= GenQuery('messages','g','level','level desc');
$res	= @DbQuery($query,$link);
if($res){
	$nlev = @DbNumRows($res);
	if($nlev){
?>
<p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=40><img src="img/16/info.png"><br>Level</th>
<th><img src="img/16/say.png"><br>Messages</th>
<?
		$row = 0;
		while( ($msg = @DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$mbar = Bar($msg[1],0,1);
			echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
			echo "<a href=Monitoring-Messages.php?lvl=$msg[0]><img src=\"img/16/" . $mico[$msg[0]] . ".png\" title=" . $mlvl[$msg[0]] . "></a></th><td>$mbar $msg[1]</td></tr>\n";
		}
		echo "</table>\n";
	}else{
		echo '<p><h5>No Messages</h5>';	
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
}
?>

</td><td width=80% class="helper">

<h2>Recent Vital Messages</h2>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=40><img src="img/16/info.png"><br>Level</th>
<th width=100><img src="img/16/clock.png"><br>Time</th>
<th><img src="img/16/dev.png"><br>Source</th>
<th><img src="img/16/find.png"><br>Info</th>
</tr>
<?
$query	= GenQuery('messages','s','*','id desc',$lim,array('level'),array('>'),array('100') );
$res	= @DbQuery($query,$link);
if($res){
	$row  = 0;
	while( ($m = @DbFetchRow($res)) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$hint = "";
		$time = date($datfmt,$m[2]);
		$fd   = str_replace(" ","%20",date("m/d/Y H:i:s",$m[2]));
		$usrc = rawurlencode($m[3]);
		echo "<tr class=\"$bg\"><th class=\"$bi\"><a href=Monitoring-Messages.php?ina=level&opa==&sta=$m[1]>\n";
		echo "<img src=\"img/16/" . $mico[$m[1]] . ".png\" title=\"" . $mlvl[$m[1]] . "\"></a></th>\n";
		echo "<td><a href=Monitoring-Messages.php?ina=time&opa==&sta=$fd>$time</a></td><th>\n";
		echo "<a href=Monitoring-Messages.php?ina=source&opa==&sta=$usrc>$m[3]</a></th><td>$m[4]</td></tr>\n";
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
}

?>
</table>

</td></tr></table>

<?
TopoTable($reg,$cty,$bld);
if (!$cty){
	TopoCities();
}elseif (!$bld){
	TopoBuilds($reg,$cty);
}else{
	TopoFloors($reg,$cty,$bld);
}
include_once ("inc/footer.php");

?>
