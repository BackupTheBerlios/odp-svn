<?
/*
#============================================================================
# Program: Reports-Nodes.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 21/04/05	initial version.
# 27/04/05	added update stats.
# 04/05/05	added ambiguous IPs
# 20/05/05	added unused vlans
# 06/03/06	added nomads and reorganized everything
# 20/03/06	new SQL query support
# 04/09/07	implemented CSS scheme
*/

error_reporting(E_ALL ^ E_NOTICE);

include_once ("inc/header.php");
include_once ('inc/libnod.php');

$_GET = sanitize($_GET);
$rep = isset($_GET['rep']) ? $_GET['rep'] : array();
$lim = isset($_GET['lim']) ? $_GET['lim'] : 10;
$ord = isset($_GET['ord']) ? "checked" : "";
?>
<h1>Node Reports</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/dcub.png" title="Node based statistics">
</a></th>
<th>Select Report(s)</th>
<th>
<select multiple name="rep[]" size=4>
<option value="sum" <? if(in_array("sum",$rep)){echo "selected";} ?> >Summary
<option value="ips" <? if(in_array("ips",$rep)){echo "selected";} ?> >IP Addresses
<option value="ifs" <? if(in_array("ifs",$rep)){echo "selected";} ?> >Interfaces
<option value="vln" <? if(in_array("vln",$rep)){echo "selected";} ?> >Unpopulated Vlans
<option value="nom" <? if(in_array("nom",$rep)){echo "selected";} ?> >Nomads
<option value="ust" <? if(in_array("ust",$rep)){echo "selected";} ?> >Update Stats 
</select>

</th>
<th>Limit
<select size=1 name="lim">
<? selectbox("limit",$lim);?>
</select>
</th>
<th>
<input type="checkbox" name="ord" <?=$ord?> > alternative
</th>
</select></th>
<th width=80><input type="submit" name="gen" value="Show"></th>
</tr></table></form><p>
<?
if($rep){
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('nodes');
$res	= @DbQuery($query,$link);
	if($res){
		$tnod   = 0;
		$lip    = 0;
		$nswift = 0;
		$nodns  = 0;
		$nodif	= array();
		$nonf	= array();
		$nodip['0'] = 0;
		while( ($n = @DbFetchRow($res)) ){
			if(!$n[0]){$nodns++;}
			if($n[14]){$lip++;}
			if($n[4] == $n[5]){$nswift++;}
			if(!$n[9]){$nodif["$n[6];;$n[7]"]++;}
			$nodip[$n[1]]++;
			$oui[$n[3]]++;
			$nodup[$n[4]]['fs']++;
			$nodup[$n[5]]['ls']++;
			$ifvl["$n[6];;$n[7]"] = $n[8];
			$uvlid[$n[6]][$n[8]]++;
			$nodup[$n[10]]['iu']++;
			$ival[$n[9]]++;
			$ifchg[$n[11]]++;
			$nodup[$n[12]]['au']++;
			$ipchg[$n[13]]++;
			$iplost[$n[14]]++;
			if($n[13] and $n[11]){
				$nonf[$n[2]] = $n[11] * $n[13];
				$nona[$n[2]] = $n[0];
				$noou[$n[2]] = $n[3];
				$nodv[$n[2]] = $n[6];
				$noif[$n[2]] = $n[7];
				$nofs[$n[2]] = $n[4];
				$nols[$n[2]] = $n[5];
			}
			$tnod++;
		}
		@DbFreeResult($res);
	}else{
		print @DbError($link);
		die;
	}
}
if ( in_array("sum",$rep) ){
	$nodb = Bar($nodns);
	$noib = Bar($nodip['0']);
	$lipb = Bar($lip);
	$swib = Bar($nswift);
	$totb = Bar($tnod);

?>
<table class="full fixed"><tr><td class="helper">

<h2>Node Summary</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=25%><img src="img/32/db.png" title="Lost IP nodes used to have an address. Swift nodes were discovered only once."><br>Item</th>
<th><img src="img/32/form.png"><br>Value</th>
<tr><th class="<?=$modgroup[$self]?>2">Non DNS Nodes</th><td class="txta"><?=$nodb?><a href=Nodes-List.php?ina=name&opa=regexp&sta=%5E%24> <?=$nodns?></a></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Non IP Nodes</th><td class="txtb"><?=$noib?><a href=Nodes-List.php?ina=ip&opa==&sta=0> <?=$nodip['0']?></a></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Lost IPs</th><td class="txta"><?=$lipb?><a href=Nodes-List.php?ina=iplost&opa=%3E&sta=0> <?=$lip?></a></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Swift Nodes</th><td class="txtb"><?=$swib?><a href=Nodes-List.php?ina=firstseen&cop==&inb=lastseen> <?=$nswift?></a></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Total Nodes</th><td class="txta"><?=$totb?> <?=$tnod?></td></tr>
</table>

</td><td class="helper">

<h2>OUI Chart</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=3 width=25%><img src="img/32/cnic.png"><br>NIC Vendor</th>
<th><img src="img/32/cubs.png"><br>Nodes</th>
<?
	if($ord){
		asort($oui);
	}else{
		arsort($oui);
	}
	$row = 0;
	foreach ($oui as $o => $nn){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$obar = Bar($nn);
		$img  = Nimg($o);
		$uo = rawurlencode($o);
		echo "<tr class=\"$bg\"><th>\n";
		echo "$row</th><th class=\"$bi\"><img src=\"img/oui/$img.png\"></th>\n";
		echo "<td><a href=http://www.google.com/search?q=$uo&btnI=1>$o</a></td><td>$obar <a href=Nodes-List.php?ina=oui&opa==&sta=$uo>$nn</a></td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> OUI vendors</td></tr>
</table>

</td></tr></table>

<?
}
if ( in_array("ips",$rep) ){
?>
<table class="full fixed"><tr><td class="helper">

<h2>IP Changes</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=25%><img src="img/32/brld.png" title="# of times a node was seen with a different IP where > 1000000 means IP was lost."><br>IP Changes</th>
<th><img src="img/32/cubs.png"><br>Nodes</th>
<?
	if($ord){
		krsort($ipchg);
	}else{
		ksort($ipchg);
	}
	$row = 0;
	foreach ($ipchg as $c => $nc){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$cbar = Bar($nc);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$c times</th><td>$cbar <a href=Nodes-List.php?ina=ipchanges&opa==&sta=$c>$nc</a></td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Values for changed IPs</td></tr>
</table>

</td><td class="helper">

<h2>Lost IPs</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=25%><img src="img/32/bdwn.png" title="# of times a node lost its IP address."><br>IP Lost</th>
<th><img src="img/32/cubs.png"><br>Nodes</th>
<?
	if($ord){
		krsort($iplost);
	}else{
		ksort($iplost);
	}
	$row = 0;
	foreach ($iplost as $c => $nc){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$cbar = Bar($nc);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$c times</th><td>$cbar <a href=Nodes-List.php?ina=iplost&opa==&sta=$c>$nc</a></td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Values for lost IPs</td></tr>
</table>

</td></tr><tr><td class="helper">

<h2>Ambiguous IP Addresses</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=20%><img src="img/32/brgt.png"><br>IP Address</th>
<th><img src="img/32/cubs.png"><br>Nodes</th>
<?

	if($ord){
		asort($nodip);
	}else{
		arsort($nodip);
	}
	$row = 0;
	foreach ($nodip as $ai => $nm){
		if ($ai and $nm > 1){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ip = long2ip($ai);
			$mbar = Bar($nm,5);
			echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
			echo "$row<td>$ip</td>\n";
			echo "<td>$mbar <a href=Nodes-List.php?ina=ip&&opa==&sta=$ip&ord=lastseen>$nm</a></td></tr>\n";
			if($row == $lim){break;}
		}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Ambiguous IPs</td></tr>
</table>

</td></tr></table>

<?
}
if ( in_array("ifs",$rep) ){
?>
<table class="full fixed"><tr><td class="helper">

<h2>Multiple MAC Addresses</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=20%><img src="img/32/dev.png"><br>Device</th>
<th width=20%><img src="img/32/dumy.png"><br>Interface</th>
<th><img src="img/32/stat.png"><br>Vlan</th>
<th width=50%><img src="img/32/cubs.png"><br>Nodes</th>
<?

	if($ord){
		asort($nodif);
	}else{
		arsort($nodif);
	}
	$row = 0;
	foreach ($nodif as $di => $nm){
		if ($nm > 1){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$d = explode(';;', $di);
			$mbar = Bar($nm,8);
			$ud = rawurlencode($d[0]);
			echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
			echo "$row</th><td><a href=Devices-Status.php?dev=$ud>$d[0]</a></td>\n";
			echo "<td>$d[1]</td><td align=center><a href=Devices-Vlans.php?ina=vlanid&opa==&sta=$ifvl[$di]&draw=1>$ifvl[$di]</a></td>\n";
			echo "<td>$mbar <a href=Nodes-List.php?ina=device&&opa==&sta=$d[0]&cop=AND&inb=ifname&opb==&stb=$d[1]>$nm</a></td></tr>\n";
			if($row == $lim){break;}
		}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Interfaces with multiple macs</td></tr>
</table>

</td><td class="helper">

<h2>Interface Changes</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=25%><img src="img/32/dumy.png" title="# of times a node was discovered on a different IF."><br>IF Changes</th>
<th><img src="img/32/cubs.png"><br>Nodes</th>
<?
	if($ord){
		krsort($ifchg);
	}else{
		ksort($ifchg);
	}
	$row = 0;
	foreach ($ifchg as $c => $nc){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$cbar = Bar($nc);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$c times</th><td>$cbar <a href=Nodes-List.php?ina=ifchanges&opa==&sta=$c>$nc</a></td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Values for changed IFs</td></tr>
</table>

</td></tr><tr><td class="helper">


<h2>Metric Distribution</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=25%><img src="img/32/casp.png" title="0 Access, 10 IP-Phone, 30 Router, 50 Uplink, 100 Channel (lower = more accurate)"><br>Metric</th>
<th><img src="img/32/cubs.png"><br>Nodes</th>
<?
	ksort($ival);
	$row = 0;
	foreach ($ival as $v => $nn){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$vbar = Bar($nn);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$v</th><td>$vbar <a href=Nodes-List.php?ina=ifmetric&opa==&sta=$v>$nn</a></td></tr>\n";
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Metrics found on <?=$tnod?> nodes</td></tr>
</table>

</td></tr></table>

<?
}
if ( in_array("vln",$rep) ){
?>
<h2>Unpopulated Vlans</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=80><img src="img/32/stat.png"><br>Vlan Id</th>
<th><img src="img/32/dev.png"><br>Devices</th>
<?
	$query	= GenQuery('vlans');
	$res	= @DbQuery($query,$link);
	
	if($res){
		$nvl = 0;
		$nunvl = 0;
		$uvlandev = array();
		while( ($vl = @DbFetchRow($res)) ){
			if(! $uvlid[$vl[0]][$vl[1]] and ! preg_match("/$ignoredvlans/",$vl[1]) ){
				$ud = rawurlencode($vl[0]);
				$uvlandev[$vl[1]] .= "<a href=Devices-Status.php?dev=$ud>$vl[0]</a> ($vl[2]) ";
				$nunvl++;
			}
			$nvl++;
		}
		@DbFreeResult($res);
	}else{
		print @DbError($link);
		die;
	}
	if($ord){
		krsort($uvlandev);
	}else{
		ksort($uvlandev);
	}
	$row = 0;
	foreach ($uvlandev as $vl => $dvs){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$ubar = Bar($up,50);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "<a href=Devices-Vlans.php?ina=vlanid&opa==&sta=$vl><b>$vl</b></th>\n";
		echo "<td>$dvs</td></tr>\n";
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$nunvl?> of <?=$nvl?> device vlans unpopulated </td></tr>
</table>
	<?
}
if ( in_array("nom",$rep) ){
?>

<h2>Nomads</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=3><img src="img/32/ngrn.png" title="Nodes according to Nomad-factor (ipchanges * ifchanges)"><br>Node</th>
<th colspan=2><img src="img/32/dev.png"><br>Device - IF</th>
<th><img src="img/32/clock.png"><br>First Seen</th>
<th><img src="img/32/clock.png"><br>Last Seen</th>
<th><img src="img/32/fitr.png"><br>Nomad Factor</th>
<?
	if($ord){
		asort($nonf);
	}else{
		arsort($nonf);
	}
	$row = 0;
	foreach ($nonf as $m => $nf){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$nbar = Bar($nf,100);
		$img  = Nimg($noou[$m]);
		echo "<tr class=\"$bg\"><th>\n";
		echo "$row</th><th class=\"$bi\"><a href=Nodes-Status.php?mac=$m><img src=\"img/oui/$img.png\"></a></th><td>$nona[$m]</td>\n";
		echo "<td> $nodv[$m]</td><td>$noif[$m]\n";
		list($fc,$lc)	= Agecol($nofs[$m],$nols[$m],$row%2);
		$fs = date("j.M G:i:s",$nofs[$m]);
		echo "<td bgcolor=#$fc>$fs</td>";
		$ls = date("j.M G:i:s",$nols[$m]);
		echo "<td bgcolor=#$lc>$ls</td>";
		echo "<td>$nbar $nf</td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> nomads out of <?=$tnod?> nodes</td></tr>
</table>
	<?
}
if ( in_array("ust",$rep) ){
?>
<h2>Nodes Update Statistic</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=33%><img src="img/32/clock.png"><br>Timestamp</th>
<th><img src="img/32/eyes.png"><br>Events</th>
<?

	if($ord){
		ksort ($nodup);
	}else{
		krsort ($nodup);
	}
	$row = 0;
	foreach ( array_keys($nodup) as $d ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$nodup[$d]['fs'] = isset($nodup[$d]['fs']) ? $nodup[$d]['fs'] : 0;
		$nodup[$d]['ls'] = isset($nodup[$d]['ls']) ? $nodup[$d]['ls'] : 0;
		$nodup[$d]['iu'] = isset($nodup[$d]['iu']) ? $nodup[$d]['iu'] : 0;
		$nodup[$d]['au'] = isset($nodup[$d]['au']) ? $nodup[$d]['au'] : 0;
		$fbar = Bar($nodup[$d]['fs'],100000);
		$lbar = Bar($nodup[$d]['ls'],1);
		$ibar = Bar($nodup[$d]['iu'],0);
		$abar = Bar($nodup[$d]['au'],0);
		$fd   = rawurlencode(date("m/d/Y H:i:s",$d));
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo date("j.M G:i:s",$d)."</th>\n";
		echo "<td>$fbar <a href=Nodes-List.php?ina=firstseen&opa==&sta=".$fd.">".$nodup[$d]['fs']."</a> first seen<br>\n";
		echo "$lbar <a href=Nodes-List.php?ina=lastseen&opa==&sta=".$fd.">".$nodup[$d]['ls']."</a> last seen <br>\n";
		echo "$abar <a href=Nodes-List.php?ina=ipupdate&opa==&sta=".$fd.">".$nodup[$d]['au']."</a> IP Updates<br>\n";
		echo "$ibar <a href=Nodes-List.php?ina=ifupdate&opa==&sta=".$fd.">".$nodup[$d]['iu']."</a> IF Updates</td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> updates from <?=$tnod?> nodes</td></tr>
</table>
	<?
}

include_once ("inc/footer.php");
?>
