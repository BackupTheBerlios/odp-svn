<?
/*
#============================================================================
# Program: Nodes-Status.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 14/04/05	initial version.
#  9/05/05	improved probing & cosmetic changes.
# 17/03/06	new SQL query support
# 09/01/07	Minor improvements.
# 02/28/07	Added IP & IF tracking tables.
# 04/09/07	implemented CSS scheme
*/

include_once ("inc/header.php");
include_once ('inc/libnod.php');

$_GET = sanitize($_GET);
$mac = isset($_GET['mac']) ? $_GET['mac'] : "";
$wol = isset($_GET['wol']) ? $_GET['wol'] : "";
$del = isset($_GET['del']) ? $_GET['del'] : "";
?>
<h1>Node Status</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>><img src="img/32/ngrn.png" title="DB info and a little portscan (22,23,80, 137 & 443) for nodes"></a></th>
<th>MAC Address <input type="text" name="mac" value="<?=$mac?>" size="15"></th>
<th width=80><input type="submit" value="Show"></th>
</tr></table></form><p>
<?

if ($mac){

	$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
	$query	= GenQuery('nodes','s','*','','',array('mac'),array('='),array($mac));
	$res	= @DbQuery($query,$link);
	$nnod	= @DbNumRows($res);
	if ($nnod != 1) {
		echo "<h4>$mac $n1rmsg</h4>";
		@DbFreeResult($res);
		die;
	}else{
		$n		= @DbFetchRow($res);
		@DbFreeResult($res);

		$name		= preg_replace("/^(.*?)\.(.*)/","$1", $n[0]);
		$ip		= long2ip($n[1]);
		$au		= date($datfmt,$n[12]);
		list($a1c,$a2c) = Agecol($n[12],$n[12],1);
		$img		= Nimg("$n[2];$n[3]");
		$fs		= date($datfmt,$n[4]);
		$ls		= date($datfmt,$n[5]);
		$ud 		= rawurlencode($n[6]);
		$ui 		= rawurlencode($n[7]);
		list($fc,$lc)	= Agecol($n[4],$n[5],0);

		if($n[7]){
			$query	= GenQuery('interfaces','s','*','','',array('device','ifname'),array('=','='),array($n[6],$n[7]),array('AND') );
			$res	= @DbQuery($query,$link);
			$nif	= @DbNumRows($res);
			if ($nif != 1) {
				echo "<h4>$query $n1rmsg</h4>";
			}else{
				$if	= @DbFetchRow($res);
				if ($if[8] == "2"){
					$ifimg	= "<img src=\"img/bulbr.png\" title=\"Disabled!\">";
				}else{
					$ifimg = "<img src=\"img/bulbg.png\" title=\"Enabled\">";
				}
			}
			@DbFreeResult($res);
			$iu		= date($datfmt,$n[10]);
			list($i1c,$i2c) = Agecol($n[10],$n[10],1);
		}
		$vl[2] = "-";
		if($n[8]){
			$query	= GenQuery('vlans','s','*','','',array('device','vlanid'),array('=','='),array($n[6],$n[8]),array('AND') );
			$res	= @DbQuery($query,$link);
			$nvl	= @DbNumRows($res);
			if ($nvl == 1) {
				$vl	= @DbFetchRow($res);
			}
			@DbFreeResult($res);
		}
	}
?>

<table class="full fixed"><tr><td class="helper">

<h2>Database Info</h2><p>
<table class="content"><tr>
<th class="imga" width=80><a href=?mac=<?=$n[2]?> ><img src="img/oui/<?=$img?>.png" title="<?=$n[3]?>" ></a><br><?=$name?></th>
<th class="<?=$modgroup[$self]?>2">

<div  style="float:left">

<a href="Devices-Status.php?dev=<?=$ud?>"><img src="img/16/hwif.png" title="Status of <?=$n[6]?>"></a>
<?
if(preg_match("/dsk/",$_SESSION['group']) ){
	echo "<a href=Nodes-Stolen.php?na=$n[0]&ip=$n[1]&stl=$n[2]&dev=$n[6]&ifn=$n[7]><img src=\"img/16/fiqu.png\" title=\"Mark as stolen!\"></a>";
	echo "<a href=$_SERVER[PHP_SELF]?wol=$n[2]><img src=\"img/16/powr.png\" title=\"Send Wake on Lan packet\"></a>";
}
?>

</div><div  style="float:right">

<?
if(preg_match("/adm/",$_SESSION['group']) ){
	echo "<a href=$_SERVER[PHP_SELF]?del=$n[2]><img src=\"img/16/bcnl.png\" onclick=\"return confirm('Delete node $n[2]?')\" title=\"Delete this node!\"></a>";
}
?>

</div>

</th></tr>
<tr><th class="<?=$modgroup[$self]?>2">MAC Address</th>	<td class="txta"><b><?=rtrim(chunk_split($n[2],2,"-"),"-")?></b> or <b><?=rtrim(chunk_split($n[2],4,"."),".")?></b></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">NIC Vendor</th>	<td class="txtb"><a href=http://www.google.com/search?q=<?=urlencode($n[3])?>&btnI=1><?=$n[3]?></a></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">IP Address</th>	<td class="txta"><?=$ip?> (<?=($n[1])?gethostbyaddr($ip):"";?>)</td></tr>
<tr><th class="<?=$modgroup[$self]?>2">IP Update</th>	<td bgcolor=#<?=$a1c?>><?=$au?> (<?=$n[13]?> Changes / <?=$n[14]?> Lost)</td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Device</th>	<td class="txta"><?=$n[6]?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Interface</th>	<td class="txtb"><?=$ifimg?> <?=$n[7]?> (<?=ZFix($if[9])?>-<?=$if[10]?>) <i><?=$if[7]?> <?=$if[20]?></i></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Vlan</th>	<td class="txta"><?=$n[8]?> <?=$vl[2]?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Traffic</th>	<td class="txtb">Bytes: <?=$if[12]?> Errors: <?=$if[13]?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">IF Update</th>	<td bgcolor=#<?=$i1c?>><?=$iu?> (Changes <?=$n[11]?> / Metric <?=$n[9]?>)</td></tr>
<tr><th class="<?=$modgroup[$self]?>2">First Seen</th>	<td bgcolor=#<?=$fc?>><?=$fs?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Last Seen</th>	<td bgcolor=#<?=$lc?>><?=$ls?></td></tr>

</table>

</td><td class="helper">

<?
flush();
if($n[1]){
?>
<h2>Realtime Info</h2><p>
<table class="content"><tr>
<th class="<?=$modgroup[$self]?>2" width=80><img src="img/32/nwin.png"><br>Netbios</th><td class="txta"><?=NbtStat($ip)?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2"><a href=http://<?=$ip?> target=window><img src="img/32/glob.png"></a><br>HTTP</th><td class="txtb"><?=CheckTCP($ip,'80'," \r\n\r\n")?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2"><a href=https://<?=$ip?> target=window><img src="img/32/glok.png"></a><br>HTTPS</th><td class="txta"><?=CheckTCP($ip,'443','')?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2"><a href=ssh://<?=$ip?>><img src="img/32/lokc.png"></a><br>SSH</th><td class="txtb"><?=CheckTCP($ip,'22','')?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2"><a href=telnet://<?=$ip?>><img src="img/32/loko.png"></a><br>Telnet</th><td class="txta"><?=CheckTCP($ip,'23','\n')?></td></tr>
</table>
<?
}else{
	echo "<h4>No IP!</h4>";
}
?>

</td></tr><tr><td class="helper" align="center">


<h2>Current Interface Traffic (<?=$rrdstep?>s average)</h2>
<a href=Devices-Graph.php?dv=<?=$ud?>&if%5B%5D=<?=$ui?>><img src=inc/drawrrd.php?dv=<?=$ud?>&if%5B%5D=<?=$ui?>&s=m&t=trf></a>

</td><td class="helper" align="center">

<h2>Current Interface Errors (<?=$rrdstep?>s average)</h2>
<img src=inc/drawrrd.php?dv=<?=$ud?>&if%5B%5D=<?=$ui?>&s=m&t=err>

</td></tr><tr><td class="helper">

<h2>IP Tracking</h2>

<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2><img src="img/32/clock.png"><br>Updated</th>
<th><img src="img/32/say.png"><br>Name</th>
<th><img src="img/32/net.png"><br>IP Address</th>
<?

$query	= GenQuery('nodiplog','s','*','ipupdate','',array('mac'),array('='),array($n[2]) );
$res	= @DbQuery($query,$link);
if($res){
	$row = 0;
	while( $l = @DbFetchRow($res) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$lip = long2ip($l[3]);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$row</th><td>". date($datfmt,$l[1]) ."</td><td>$l[2]</td><td><a href=Nodes-List.php?ina=ip&opa==&sta=$lip>$lip</a></td></tr>\n";
	}
	@DbFreeResult($res);
	}else{
		print @DbError($link);
	}
	?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> IP changes (<?=$query?>)</td></tr>
</table>

</td><td class="helper">

<h2>IF Tracking</h2>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2><img src="img/32/clock.png"><br>Updated</th>
<th><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/dumy.png"><br>IF</th>
<th><img src="img/32/stat.png"><br>Vlan</th>
<th><img src="img/32/casp.png"><br>Metric</th>
<?

$query	= GenQuery('nodiflog','s','*','ifupdate','',array('mac'),array('='),array($n[2]) );
$res	= @DbQuery($query,$link);
if($res){
	$row = 0;
	while( $l = @DbFetchRow($res) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$utd = rawurlencode($l[2]);
		$uti = rawurlencode($l[3]);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$row</th><td>". date($datfmt,$l[1]) ."</td>\n";
		echo "<td><a href=Devices-Status.php?dev=$utd&shp=on>$l[2]</a></td><td>";
		echo "<a href=Nodes-List.php?ina=device&opa==&sta=$utd&cop=AND&inb=ifname&opb==&stb=$uti>$l[3]</td><td>$l[4]</td><td>$l[5]</td></tr>\n";
	}
	@DbFreeResult($res);
	}else{
		print @DbError($link);
	}
	?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> IF changes (<?=$query?>)</td></tr>
</table>

</td></tr></table>

<?

include_once ("inc/footer.php");

}elseif ($wol){
	if(preg_match("/dsk/",$_SESSION['group']) ){
		$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
		$query	= GenQuery('nodes','s','*','','',array('mac'),array('='),array($wol));
		$res	= @DbQuery($query,$link);
		$nnod	= @DbNumRows($res);
		if ($nnod != 1) {
			echo "<h4>$wol $n1rmsg</h4>";
			@DbFreeResult($res);
			die;
		}else{
			$n		= @DbFetchRow($res);
			@DbFreeResult($res);
			$ip		= long2ip($n[1]);
		}
		wake($ip,$wol, 9);
	}else{
		echo $nokmsg;
	}
?>
<h5>Magic Packet sent to <?=$ip?></h5>
<script language="JavaScript"><!--
setTimeout("history.go(-1)",10000);
//--></script>
<?

}elseif ($del){
	if(preg_match("/adm/",$_SESSION['group']) ){
		$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
		$query	= GenQuery('nodes','d','','','',array('mac'),array('='),array($del) );
		if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>Node $del $delokmsg</h3>";}
		$query	= GenQuery('nodiplog','d','','','',array('mac'),array('='),array($del) );
		if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>Node IP log $del $delokmsg</h3>";}
		$query	= GenQuery('nodiflog','d','','','',array('mac'),array('='),array($del) );
		if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>Node IF log $del $delokmsg</h3>";}
?>
<script language="JavaScript"><!--
setTimeout("history.go(-2)",2000);
//--></script>
<?
	}else{
		echo $nokmsg;
	}
}

?>
